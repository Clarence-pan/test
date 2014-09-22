<?php
/**
 * Created by PhpStorm.
 * User: panchangyun
 * Date: 14-9-20
 * Time: 上午9:43
 */

class AsyncRunner extends Thread{
    public function __construct($func, $params){
        $this->func = $func;
        $this->params = $params;
    }
    public function run(){
        $this->result = call_user_func_array($this->func, $this->params);
    }
    public function getResult(){
        if (!$this->joined) {
            $this->join();
            $this->joined = true;
        }
        return $this->result;
    }
}
class TestAsyncController extends CController{

    public function actionLongTask($interval, $times, $name){
        //dump(Thread::getCurrentThread());
        for ($i = 0; $i < $times; $i++){
            Yii::log("$name : $i / $times ...." . Thread::getCurrentThreadId(), __FUNCTION__ );
            usleep($interval);
        }
        //echo $name;
        Yii::log($name . 'done.');
        return $name . ' done.';
    }
    public function actionMultiCurl(){


        // var_dump(2);
//    	$urls = array("http://bbtest.test.tuniu.org/bb/public/product/Testphp3",
//						"http://bbtest.test.tuniu.org/bb/public/product/Testphp4",
//						"http://bbtest.test.tuniu.org/bb/public/product/Testphp5");
//    	$mh = curl_multi_init();
//        $ch = array();
//        $chunck = 10; //并发控制数
//        $all = count($urls);//所有的请求url数组
//        $chunck = $all > $chunck ? $chunck : $all;
//
//	    $options = array(
//	        CURLOPT_HEADER=>FALSE,
//	        CURLOPT_RETURNTRANSFER=>TRUE,
//	        CURLOPT_FOLLOWLOCATION=>TRUE,
//	        CURLOPT_MAXREDIRS=>5,
//	        CURLOPT_USERAGENT=>'Mozilla/5.0 (Windows NT 6.1; rv:6.0) Gecko/20100101 Firefox/6.0'
//	    );
//
//	    for($i = 0 ; $i < $chunck ; $i++){
//	        $ch[$i] = curl_init();
//	        curl_setopt($ch[$i],CURLOPT_URL,$urls[$i]);
//	        curl_setopt_array($ch[$i],$options);
//	        curl_multi_add_handle($mh,$ch[$i]);
//	    }
        // create both cURL resources
        $data = array('data' => 111);
        $ch1 = curl_init();
        $ch2 = curl_init();

        // set URL and other appropriate options
        curl_setopt($ch1, CURLOPT_URL, "http://test.local.me/testAsync/longTask?interval=200000&times=30&name=Jim" . rand(0, 10000000));
        curl_setopt($ch1, CURLOPT_HEADER, 0);
        curl_setopt($ch1, CURLOPT_POST, 1); //设置为POST方式
        curl_setopt($ch1, CURLOPT_POSTFIELDS, base64_encode(json_encode($data)));
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_URL, "http://test.local.me/testAsync/longTask?interval=200000&times=30&name=Tom" . rand(0, 10000000));
        curl_setopt($ch2, CURLOPT_HEADER, 0);
        curl_setopt($ch2, CURLOPT_POST, 1); //设置为POST方式
        curl_setopt($ch2, CURLOPT_POSTFIELDS, base64_encode(json_encode($data)));
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);

        //create the multiple cURL handle
        $mh = curl_multi_init();

        //add the two handles
        curl_multi_add_handle($mh, $ch1);
        curl_multi_add_handle($mh, $ch2);

        $active = null;
        //execute the handles

        do {
            $mrc = curl_multi_exec($mh, $active);
            Yii::log(" $mrc = curl_multi_exec($mh, $active) ");
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        if ("WAIT"){
            while ($active && $mrc == CURLM_OK) {
                Yii::log("while ($active && $mrc == CURLM_OK)");
                if (curl_multi_select($mh) != -1) {
                    do {
                        $mrc = curl_multi_exec($mh, $active);
                        Yii::log(" $mrc = curl_multi_exec($mh, $active) ");
                    } while ($mrc == CURLM_CALL_MULTI_PERFORM);
                }
                break;
            }
        }

        echo curl_multi_getcontent($ch1);
        echo curl_multi_getcontent($ch2);

        //close the handles
        curl_multi_remove_handle($mh, $ch1);
        curl_multi_remove_handle($mh, $ch2);
        curl_multi_close($mh);
        echo "end...";

    }

    public function getUrls($urls){
        $count = count($urls);

        $curls = array();
        $master = curl_multi_init();

        for($i = 0; $i < $count; $i++){
            $curls[$i] = curl_init($urls[$i]);
            curl_setopt($curls[$i], CURLOPT_RETURNTRANSFER, true);
            curl_multi_add_handle($master, $curls[$i]);
        }

        do {
            $r = curl_multi_exec($master, $running);
            Yii::log("$r = curl_multi_exec($master,$running);");
            usleep(500000);
        } while($running > 0);


        for($i = 0; $i < $count; $i++)
        {
            $results[] = curl_multi_getcontent  ( $curls[$i]  );
        }

        return $results;
    }

    public function actionGetUrls(){
        $urls = array();
        for ($i = 0; $i < 3; $i++) {
            $urls[] = 'http://test.local.me/testAsync/longTask?interval=200000&times=10&name=Someone'.$i;
        }

        dump($this->getUrls($urls));
    }

    // 创建线程，注意：传递给线程的字段或参数必须是可以被序列化的，否则服务器会挂
    public function actionThreads($count=3,$interval=200000,$times=10,$name="Jim"){

        $threads = array();
        for ($i = 0; $i < 3; $i++) {
            $threads[$i] = new LongTaskThread($this, $interval, $times, $name . $i);
            $threads[$i]->start();
        }


        Yii::log('all threads started!');
        dump($threads);
        Yii::log("1111111111");
        echo $threads[0]->result;
        Yii::log("2222222222222");
        $results = array();
        Yii::log("33333333333333");
        sleep(5);
        for ($i = 0; $i < count($threads); $i++){
            Yii::log("thread[$i] joining:");
            Yii::log(__FUNCTION__ . ' ' .__LINE__ );

            //$joining = $threads[$i]->join();
            Yii::log(__FUNCTION__ . ' ' .__LINE__ );
            Yii::log("thread[$i] joined: $joining");
            $results[$i] = $threads[$i]->result;
            Yii::log(__FUNCTION__ . ' ' .__LINE__ );
        }

        Yii::log("all threads joined");
        Yii::log("results:");
        foreach ($results as $r) {
            Yii::log($r);
        }
    }

    // 这个测试失败了，因为闭包不可以被序列化，挂了
    public function actionAsyncCallTest(){
        $runner = new AsyncRunner(function ($interval, $times, $name){
            //dump(Thread::getCurrentThread());
            for ($i = 0; $i < $times; $i++){
                echo "$name : $i / $times ...." ;
                Yii::log("$name : $i / $times ...." . Thread::getCurrentThreadId(), __FUNCTION__ );
                usleep($interval);
            }

            Yii::log($name . 'done.');
            return $name . ' done.';
        }, array(100000, 10, "Jim"));
        $runner->start();
        echo $runner->getResult() . '<br/>';
    }
    // 这个测试可以成功，因为字符串可以被序列化
    public function actionAsyncCallTest2(){
        $runner = new AsyncRunner('asyncCallTestFunc', array(100000, 10, "Jim"));
        $runner->start();
        echo $runner->getResult() . '<br/>';
    }
    // 多线程操作
    public function actionAsyncCallTest3(){
        $runners = array();
        for ($i = 0; $i < 3; $i++){
            $runners[$i] = new AsyncRunner('asyncCallTestFunc', array(100000, 10, "Jim" . $i));
            $runners[$i]->start();
        }
        sleep(1); //s
        for ($i = 0; $i < count($runners); $i++){
            echo $runners[$i]->getResult() . '<br/>';
        }
    }

    // 全局操作 -- 在额外的线程中可以访问全局的变量 -- 如果有需要，那些无法序列化的对象可以考虑通过全局变量传递到线程
    public function actionAsyncCallGetGlobal(){
        $r = new AsyncRunner('asyncCallGetGlobal', array());
        $r->start();
        $r->getResult();
    }
}


function asyncCallTestFunc($interval, $times, $name){
    //dump(Thread::getCurrentThread());
    for ($i = 0; $i < $times; $i++){
        echo "$name : $i / $times ....<br/>" ;
        Yii::log("$name : $i / $times ...." . Thread::getCurrentThreadId(), __FUNCTION__ );
        usleep($interval);
    }

    Yii::log($name . 'done.<br/>');
    return $name . ' done.<br/>';
}

function asyncCallGetGlobal(){
    dump($GLOBALS);
}



class LongTaskThread extends Thread{
    public $result = "Noooo";
    public function __construct($controller, $interval, $times, $name){
        //$this->controller = $controller;
        $this->interval = $interval;
        $this->times = $times;
        $this->name = $name;
    }
    public function run(){
        $some = 'other';
        Yii::log("make assignment  " . $some);
        $controller = new TestAsyncController(1);
        $result = $controller->actionLongTask($this->interval, $this->times, $this->name);
        //$result = $this->controller->actionLongTask($this->interval, $this->times, $this->name);
        Yii::log("Got result  " . $result);
        $this->result = $result;
        Yii::log("assigned this->result.");
    }
}

