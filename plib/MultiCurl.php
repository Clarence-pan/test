<?php
/**
 * Created by PhpStorm.
 * User: panchangyun
 * Date: 14-9-22
 * Time: 上午10:39
 */

class MultiCurl {
    private $urls = null;  // array of SingleUrl
    private $defaultFormat = 'base64json';  // or raw
    private $multiCurlHandle = null;
    /**
     * @param array $urls  like array( array( 'url' => 'http://test.org/...",
     *                                      "param" => array( 'key' => 'value'),
     *                                      'method' => 'POST/GET...',
     *                                      'format' => 'raw/base64'
     *                                       ),
     *                             ... )
     * @see addUrl
     */
    public function __construct(array $urls = null){
        if ($urls != null){
            $this->addUrls($urls);
        }
        $this->multiCurlHandle = curl_multi_init();
    }

    /**
     * @param $url   string: "http://test.org/..."
     *                array like: array( 'url' => 'http://test.org/...",
     *                                      "param" => array( 'key' => 'value'),
     *                                      'method' => 'POST/GET...')
     * @param array $param 传递给URL的参数，格式: array( 'key' => 'value')
     * @param string $method HTTP请求的类型, GET, POST, DELETE, PUT...
     * @param string $format  参数格式化类型，raw表示拼接后的原始参数，base64表示用base64进行加密后再请求，
     *                                         null表示使用setDefaultFormat设置的默认值，初始化为base64json
     * @return self
     */
    public function addUrl($url, array $param=null, $method="GET", $format=null){
        $singleCurl = new SingleUrl($url, $param, $method, $format);
        $this->urls[] = $singleCurl;
        $singleCurl->addToMultiCurl($this);
        return $this;
    }

    /**
     * @param array $urls  like array( array( 'url' => 'http://test.org/...",
     *                                      "param" => array( 'key' => 'value'),
     *                                      'method' => 'POST/GET...',
     *                                      'format' => 'raw/base64'
     *                                       ),
     *                             ... )
     * @see addUrl
     * @return self
     */
    public function addUrls(array $urls){
        foreach ($urls as $url) {
            $this->addUrl($url['url'], $url['param'], isset($url['method']) ? $url['method'] : "GET", $url['format']);
        }
        return $this;
    }

    /**
     * @see curl_multi_setopt
     * @param $option
     * @param $value
     * @return bool
     */
    public function setMultiCurlOpt($option, $value){
        return curl_multi_setopt($this->multiCurlHandle, $option, $value);
    }


    /**
     * @param bool $waitTillEnd 是否需要等到请求结束
     * @return self
     */
    public function exec($waitTillEnd=false, $returnResult=false){
        Yii::log("exec: $waitTillEnd, $returnResult");

        // run
        $this->running = false;

        /**
         * curl_multi_perform(3) is asynchronous. It will only execute as little as possible and then return back control
         * to your program. It is designed to never block. If it returns CURLM_CALL_MULTI_PERFORM you better call it again
         * soon, as that is a signal that it still has local data to send or remote data to receive."
         */
        do {
            $this->mrc = curl_multi_exec($this->multiCurlHandle, $this->running);
        } while($this->mrc == CURLM_CALL_MULTI_PERFORM);

        if ($waitTillEnd){
            $this->wait();
        }

        if ($returnResult){
            return $this->getResults();
        }

        return $this;
    }

    /**
     * wait until end of the request
     * @param $dealResultCallback callable - 当收到数据后处理的函数
     * @return self
     */
    public function wait($dealResultCallback=null){
        Yii::log("wait: running: {$this->running}, mrc: {$this->mrc}" . __FUNCTION__);
        while ($this->running && $this->mrc == CURLM_OK) {
            if ($dealResultCallback){
                $this->tryGetResult($dealResultCallback);
            }
            if (curl_multi_select($this->multiCurlHandle) != -1){
                do {
                    $this->mrc = curl_multi_exec($this->multiCurlHandle, $this->running);
                    Yii::log("wait: {$this->mrc} = curl_multi_exec({$this->multiCurlHandle}, {$this->running})  while (mrc == {CURLM_CALL_MULTI_PERFORM}))" . __FUNCTION__);
                }while($this->mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }
        if ($dealResultCallback){
            $this->tryGetResult($dealResultCallback);
        }
        return $this;
    }

    /**
     * 尝试获取multi curl的结果，如果获取到，则调用callback来处理结果
     * @param $dealResultCallback
     */
    private function tryGetResult($dealResultCallback){
        while ($done = curl_multi_info_read($this->multiCurlHandle)){
            $curlHandle = $done['handle'];
            $singleCurl = $this->findUrlByCurlHandle($curlHandle);
            $singleCurl->fetchContentFromMultiCurl();
            call_user_func_array($dealResultCallback, array($singleCurl));
        }
    }

    /**
     * 根据CURL的handle查找对应的url信息
     * @param $curlHandle
     * @return mixed
     */
    public function &findUrlByCurlHandle($curlHandle){
        foreach ($this->urls as &$singleCurl) {
            if ($singleCurl->getHandle() == $curlHandle){
                return $singleCurl;
            }
        }
    }


    /**
     * get results of urls
     * @return array ( array( 'url' => "http://..."
     *                         'resultRaw' => '...raw result...'
     *                         'result' => stdClass(...))
     */
    public function getResults(){
        foreach ($this->urls as &$singleCurl) {
            $singleCurl->fetchContentFromMultiCurl();
        }
        return $this->urls;
    }

    /**
     * cleanup
     */
    public function cleanup(){
        foreach ($this->urls as &$singleCurl){
            $singleCurl->cleanup();
        }
        if ($this->multiCurlHandle != null){
            curl_multi_close($this->multiCurlHandle);
            unset($this->multiCurlHandle);
        }
    }

    public function getHandle(){
        return $this->multiCurlHandle;
    }

    public function __destruct(){
        $this->cleanup();
    }
}

class SingleUrl
{
    /**
     * @see MultiCurl::addUrl
     * @param $url
     * @param $param
     * @param $method
     * @param $format
     * @param $initNow   -- 立即初始化创建CURL句柄，如果传false，则应主动调用@see SingleUrl::init()进行初始化
     */
    public function __construct($url, $param, $method, $format, $initNow=true){
        $this->url = $url;
        $this->param = $param;
        $this->method = $method;
        $this->format = $format;
        if ($initNow){
            $this->init();
        }
    }

    /**
     * 初始化,创建CURL句柄
     * @throws InvalidArgumentException
     */
    public function init(){
        $ch = curl_init();
        $this->curlHandle = $ch;
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $queryString = null;
        if (!empty($this->param)){
            $queryString = $this->format($this->param, $this->format);
        }
        if ($queryString != null && strtoupper($this->method) != 'POST'){
            $newUrl = $this->url;
            // 如果原始url中本来就有querystring则追加，否则要加?再追加
            if (strstr('?', $this->url)){
                $newUrl .= '&' . $queryString;
            } else {
                $newUrl .= '?' . $queryString;
            }
            curl_setopt($ch, CURLOPT_URL, $newUrl);
            Yii::log("Made query ULR: " .$newUrl);
        }
        switch (strtoupper($this->method))
        {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $queryString);
                curl_setopt($ch, CURLOPT_URL, $this->url);
                break;
            case 'PUT': // 发送文件，必须同时设置inFile和inFileSize
                curl_setopt($ch, CURLOPT_PUT, true);
                curl_setopt($ch, CURLOPT_INFILE, $this->inFile);
                curl_setopt($ch, CURLOPT_INFILESIZE, $this->inFileSize);
                break;
            case 'GET':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                break;
            case 'HEAD':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'HEAD');
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case 'TRACE';
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'TRACE');
                break;
            case 'CONNECT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'CONNECT');
                break;
            case 'OPTIONS':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'OPTIONS');
                break;
            default:
                throw new InvalidArgumentException("Method " . $this->method .' of URL ' . $this->url .' is not supported!');
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // 将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
    }


    /**
     * @return string
     */
    public function getDefaultFormat(){
        return $this->defaultFormat;
    }

    /**
     * @param $format
     */
    public function setDefaultFormat($format){
        $this->defaultFormat = $format;
    }

    /**
     * 格式化
     * @param $param
     * @param $format  - null -> use default format, @see getDefaultFormat()
     * @return string
     */
    public function format($param, $format){
        $format = ($format === null ? $this->getDefaultFormat() : $format);
        if (!is_string($param)){
            $param = http_build_query($param);
        }
        switch ($format){
            case 'base64':
                return base64_encode($param);
            case 'base64json':
                $json = json_encode($param);
                return base64_encode($json);
            case 'json':
                return json_encode($param);
            case 'raw':
            default:
                return $param;
        }
    }

    /**
     * @param $param
     * @param $format - null -> use default format, @see getDefaultFormat()
     * @return string
     */
    public function antiFormat($param, $format){
        $format = ($format === null ? $this->getDefaultFormat() : $format);
        switch ($format){
            case 'base64':
                return base64_decode($param);
            case 'base64json':
                $json = base64_decode($param);
                return json_decode($json);
            case 'json':
                return json_decode($param);
            case 'raw':
            default:
                return $param;
        }
    }

    /**
     * 从Multi-CURL中获取结果
     */
    public function fetchContentFromMultiCurl(){
        $contentRaw = curl_multi_getcontent($this->curlHandle);
        $content = $this->antiFormat($contentRaw, $this->format);
        $this->resultRaw =  $contentRaw;
        $this->result = $content;
        $this->resultInfo = curl_getinfo($this->curlHandle);
        $this->resultError = curl_error($this->curlHandle);
        return $this;
    }

    public function cleanup(){
        $ch = $this->curlHandle;
        if ($ch){
            curl_close($ch);
        }
        unset($this->curlHandle);

        $this->removeFromMultiCurl();
    }


    /**
     * @param mixed $curlHandle
     */
    private function setCurlHandle($curlHandle)
    {
        $this->curlHandle = $curlHandle;
    }

    /**
     * @return mixed
     */
    public function getHandle()
    {
        return $this->curlHandle;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * @return mixed
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $param
     */
    public function setParam($param)
    {
        $this->param = $param;
    }

    /**
     * @return mixed
     */
    public function getParam()
    {
        return $this->param;
    }

    /**
     * @param mixed $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        if (empty($this->result)){
            $this->fetchResult();
        }
        return $this->result;
    }

    /**
     * @param mixed $resultError
     */
    private function setResultError($resultError)
    {
        $this->resultError = $resultError;
    }

    /**
     * @return mixed
     */
    public function getResultError()
    {
        return $this->resultError;
    }

    /**
     * @param mixed $resultInfo
     */
    private function setResultInfo($resultInfo)
    {
        $this->resultInfo = $resultInfo;
    }

    /**
     * @return mixed
     */
    public function getResultInfo()
    {
        return $this->resultInfo;
    }

    /**
     * @param mixed $resultRaw
     */
    private function setResultRaw($resultRaw)
    {
        $this->resultRaw = $resultRaw;
    }

    /**
     * @return mixed
     */
    public function getResultRaw()
    {
        return $this->resultRaw;
    }

    /**
     * @param mixed $inFile
     */
    public function setInFile($inFile)
    {
        $this->inFile = $inFile;
    }

    /**
     * @return mixed
     */
    public function getInFile()
    {
        return $this->inFile;
    }

    /**
     * @param mixed $inFileSize
     */
    public function setInFileSize($inFileSize)
    {
        $this->inFileSize = $inFileSize;
    }

    /**
     * @return mixed
     */
    public function getInFileSize()
    {
        return $this->inFileSize;
    }

    /**
     * 添加到MultiCurl中去
     * @param MultiCurl $multiCurl
     * @return int
     */
    public function addToMultiCurl(MultiCurl $multiCurl){
        $this->multiCurlAddedTo = $multiCurl;
        return curl_multi_add_handle($multiCurl->getHandle(), $this->getHandle());
    }

    /**
     * 从MultiCurl中去除
     * @return int
     */
    public function removeFromMultiCurl(){
        if (!$this->multiCurlAddedTo){
            return 0;
        }
        $ret = curl_multi_remove_handle($this->multiCurlAddedTo->getHandle(), $this->getHandle());
        unset($this->multiCurlAddedTo);
        return $ret;
    }

    private $url;
    private $curlHandle;
    private $param;
    private $method;
    private $format;
    private $resultRaw;
    private $result;
    private $resultError;
    private $resultInfo;
    private $defaultFormat;
    private $inFile;
    private $inFileSize;
    private $multiCurlAddedTo;
}

