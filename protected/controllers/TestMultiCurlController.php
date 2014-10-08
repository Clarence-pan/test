<?php
/**
 * Created by PhpStorm.
 * User: panchangyun
 * Date: 14-9-28
 * Time: 下午7:10
 */
require("/plib/MultiCurl.php");

class TestMultiCurlController extends  CController {
    /**
     *  一个很长的任务
     * @param $interval number 循环间隔时间，单位毫秒
     * @param $times number 循环次数
     * @param $name string 名称
     * @return string
     */
    public function actionLongTask($interval, $times, $name){
        //dump(Thread::getCurrentThread());
        for ($i = 0; $i < $times; $i++){
            Yii::log("$name : $i / $times ...." .  __FUNCTION__  .' <br>');
            echo "$name : $i / $times ...." .  __FUNCTION__ .' <br>';
            usleep($interval * 1000);
        }
        echo $name . ' done. <br>';
        Yii::log($name . ' done. <br>');
        return $name . ' done. <br>';
    }

    public function actionTestMultiCurlBasic(){
        $mc = new MultiCurl();
        for ($i = 0; $i < 10; $i++ ){
            $mc->addUrl("http://test/testMultiCurl/longTask?interval=300&times=30&name=Jack" . $i);
        }

        $results = $mc->exec()->wait()->getResults();
        foreach ($results as $result) {
            echo "URL: " . $result->getUrl() .' <br>';
            echo "CONTENT: " . $result->getResult().' <br>';
        }
    }
    public function actionTestMultiCurlBasicMethod($method=null){
        $method = ($method ? $method : 'GET');
        $mc = new MultiCurl();
        for ($i = 0; $i < 10; $i++ ){
            $mc->addUrl("http://test/testMultiCurl/longTask", array(
                'interval' => 300,
                'times' => 30,
                'name' => 'Jack'.$i
            ), $method);
        }

        $results = $mc->exec()->wait()->getResults();
        foreach ($results as $result) {
            echo "URL: " . $result->getUrl() .' <br>';
            echo "CONTENT: " . $result->getResult().' <br>';
        }
    }
    // 并行请求10个网页，并打印出来
    public function actionTestMultiCurlBasicGet(){
        $mc = new MultiCurl();
        for ($i = 0; $i < 10; $i++ ){
            $mc->addUrl("http://test/testMultiCurl/longTask", array(
                            'interval' => 300,
                            'times' => 30,
                            'name' => 'Jack'.$i
                        ), 'GET');
        }

        $results = $mc->exec()->wait()->getResults();
        foreach ($results as $result) {
            echo "URL: " . $result->getUrl() .' <br>';
            echo "CONTENT: " . $result->getResult().' <br>';
        }
    }
    // 并行请求10个网页，并打印出来
    public function actionTestMultiCurlBasicPost(){
        $mc = new MultiCurl();
        for ($i = 0; $i < 10; $i++ ){
            $mc->addUrl("http://test/testMultiCurl/longTask", array(
                            'interval' => 300,
                            'times' => 30,
                            'name' => 'Jack'.$i
                        ), 'POST');
        }

        $results = $mc->exec()->wait()->getResults();
        foreach ($results as $result) {
            echo "URL: " . $result->getUrl() .' <br>';
            echo "CONTENT: " . $result->getResult().' <br>';
        }
    }
    // 并行请求10个网页，并打印出来
    public function actionTestMultiCurlBasicHead(){
        $mc = new MultiCurl();
        for ($i = 0; $i < 10; $i++ ){
            $mc->addUrl("http://test/testMultiCurl/longTask", array(
                'interval' => 300,
                'times' => 30,
                'name' => 'Jack'.$i
            ), 'HEAD');
        }

        $results = $mc->exec()->wait()->getResults();
        foreach ($results as $result) {
            echo "URL: " . $result->getUrl() .' <br>';
            echo "CONTENT: " . $result->getResult().' <br>';
        }
    }

    // 并行请求10个网页，并打印出来
    public function actionTestMultiCurlInstantGet(){
        $mc = new MultiCurl();
        for ($i = 0; $i < 10; $i++ ){
            $mc->addUrl("http://test/testMultiCurl/longTask", array(
                'interval' => 300,
                'times' => 30,
                'name' => 'Jack'.$i
            ), 'GET');
        }

        $mc->exec();
        $mc->wait(function ($singleCurl){
            echo "URL: " . $singleCurl->getUrl() .' <br>';
            echo "CONTENT: " . $singleCurl->getResult().' <br>';
        });

        echo "<hr> after all: <br/>";
        $results = $mc->getResults();
        foreach ($results as $result) {
            echo "URL: " . $result->getUrl() .' <br>';
            echo "CONTENT: " . $result->getResult().' <br>';
        }
    }
} 