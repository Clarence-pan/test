<?php
/**
 * Created by PhpStorm.
 * User: panchangyun
 * Date: 14-9-22
 * Time: 上午10:39
 */

class MultiCurl {
    private $urls = null;
    private $defaultFormat = 'base64json';  // or raw
    private $multiCurl = null;
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
        $this->addUrls($urls);
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
        $this->urls[] = array('url' => $url,
                               'param' => $param,
                               'method' => $method,
                               'format' => $format,
                                );
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
        switch ($format){
            case 'base64':
                return base64_encode($param);
            case 'base64json':
                $json = json_encode($param);
                return base64_encode($json);
            case 'query':
                $queryString = http_build_query($param);
                return $queryString;
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
            case 'raw':
            default:
                return $param;
        }
    }

    /**
     * @param bool $waitTillEnd 是否需要等到请求结束
     * @return self
     */
    public function exec($waitTillEnd=false, $returnResult=false){
        Yii::log("exec: $waitTillEnd, $returnResult");
        // init multi-curl and curl and add into multi-curl
        $this->multiCurl = curl_multi_init();
        for ($i = 0, $cnt = count($this->urls); $i < $cnt; $i++) {
            $url = &$this->urls[$i];
            $ch = curl_init();
            $url['curl'] = $ch;
            curl_setopt($ch, CURLOPT_URL, $url['url']);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $queryString = null;
            if (!empty($url['param'])){
                $queryString = $this->format($url['param'], $url['format']);
            }
            if ($url['method'] == 'POST'){
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $queryString);
            } else { // GET
                if ($queryString != null){
                    curl_setopt($ch, CURLOPT_URL, $url['url'] . '?' . $queryString);
                    Yii::log("Made query ULR for GET: " . $url['url'] . '?' . $queryString);
                }
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // 将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
            curl_multi_add_handle($this->multiCurl, $ch);
        }

        // run
        $this->running = false;
        do {
            $this->mrc = curl_multi_exec($this->multiCurl, $this->running);
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
     * @return self
     */
    public function wait(){
        Yii::log("wait: running: {$this->running}, mrc: {$this->mrc}");
        while ($this->running && $this->mrc == CURLM_OK) {
            if (curl_multi_select($this->multiCurl) != -1){
                do {
                    $this->mrc = curl_multi_exec($this->multiCurl, $this->running);
                    Yii::log("wait: {$this->mrc} = curl_multi_exec({$this->multiCurl}, {$this->running})  while (mrc == {CURLM_CALL_MULTI_PERFORM}))");
                }while($this->mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }
        return $this;
    }

    /**
     * get results of urls
     * @return array ( array( 'url' => "http://..."
     *                         'resultRaw' => '...raw result...'
     *                         'result' => stdClass(...))
     */
    public function getResults(){
        foreach ($this->urls as &$url) {
            $contentRaw = curl_multi_getcontent($url['curl']);
            $content = $this->antiFormat($contentRaw, $url['format']);
            $url['resultRaw'] =  $contentRaw;
            $url['result'] = $content;

        }
        return $this->urls;
    }

    /**
     * cleanup
     */
    public function cleanup(){
        foreach ($this->urls as &$url){
            $ch = $url['curl'];
            if ($ch != null){
                curl_close($ch);
                if ($this->multiCurl != null){
                    curl_multi_remove_handle($this->multiCurl, $ch);
                }
            }
        }
        if ($this->multiCurl != null){
            curl_multi_close($this->multiCurl);
        }
    }

    public function __destruct(){
        $this->cleanup();
    }
}

