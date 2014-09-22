<?php
/**
 * Created by PhpStorm.
 * User: panchangyun
 * Date: 14-9-22
 * Time: 上午10:39
 */

class MultiCurl {
    private $urls = null;
    private $defaultFormat = 'base64';  // or raw
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
        $this->urls = $urls;
    }

    /**
     * @param $url   string: "http://test.org/..."
     *                array like: array( 'url' => 'http://test.org/...",
     *                                      "param" => array( 'key' => 'value'),
     *                                      'method' => 'POST/GET...')
     * @param array $param 传递给URL的参数，格式: array( 'key' => 'value')
     * @param string $method HTTP请求的类型, GET, POST, DELETE, PUT...
     * @param string $format  参数格式化类型，raw表示拼接后的原始参数，base64表示用base64进行加密后再请求，
     *                                         null表示使用setDefaultFormat设置的默认值，初始化为base64
     */
    public function addUrl($url, array $param=null, $method="GET", $format=null){
        $this->urls[] = array('url' => $url,
                               'param' => $param,
                               'method' => $method,
                               'format' => ($format === null ? $this->getDefaultFormat() : $format),
                                );
    }

    /**
     * @param array $urls  like array( array( 'url' => 'http://test.org/...",
     *                                      "param" => array( 'key' => 'value'),
     *                                      'method' => 'POST/GET...',
     *                                      'format' => 'raw/base64'
     *                                       ),
     *                             ... )
     * @see addUrl
     */
    public function addUrls(array $urls){
        if ($this->urls === null){
            $this->urls = $urls;
            return;
        }
        foreach ($urls as &$url) {
            $this->urls[] = $url;
        }
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
     * @param $text
     * @return string
     */
    public function format($text){
        switch ($this->getDefaultFormat()){
            case 'base64':
                return base64_encode($text);
            case 'raw':
            default:
                return $text;
        }
    }
} 