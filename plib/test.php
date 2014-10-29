<?php


/**
 * Created by PhpStorm.
 * User: panchangyun
 * Date: 14-9-20
 * Time: 下午3:04
 */
require(dirname(__FILE__).DIRECTORY_SEPARATOR.'dump/dump.php');
// start test:
// 1: Test::runTests()
class Test extends CController {
    private $_result = array(); // [ { name=>'something', status=>'error/failed/passed', detail='...'}, ...]
    const ERROR = 'error';
    const FAILED = 'failed';
    const PASSED = 'passed';
    public function actionRunTests(){
        $this->doRunTest();
    }
    public function actionRun($cases=null){
        $this->doRunTest($cases);
    }
    public static function runTests(){
        $instance = new static(time());
        return $instance->doRunTest();
    }

    public function doRunTest($cases=null){
        try{
            $this->prepare();
            if (!$cases){
                $this->runAllTests();
            } else if (is_string($cases)){
                $this->runOneTest($cases);
            } else if (is_array($cases)){
                foreach ($cases as $case) {
                    $this->runOneTest($case);
                }
            }
            $this->reportResult();
            return $this->tearDown();
        } catch (Exception $e) {
            $this->tearDown();
            throw $e;
        }
    }

    public function prepare(){

    }
    public function tearDown(){

    }
    public function runAllTests(){
        $class = get_class($this);
        $methods = get_class_methods($class);
        $prefix = 'test';
        foreach ($methods as $m) {
            $pos = strpos($m, $prefix);
            //echo $m . " $pos <br/>";
            if ($pos === 0){
                $case = substr($m, strlen($prefix));
                $this->runOneTest($case);
            }
        }

    }
    public function runOneTest($case){
        trace("Begin running testcase '$case'..." .
             "<a href='http://bb.me/tests/".$this->getTestSuiteName()."/run?case=$case' target='_blank'>(open)</a>" );
        try{
            $this->setResult($case, Test::FAILED);
            call_user_func_array(array($this, 'test' . $case), array());
            $this->setResult($case, Test::PASSED);
        } catch (TestFailedException $e){
            $this->setResult($case, Test::FAILED, $e->getMessage(). ' : '. $e->getTraceAsString());
        } catch (Exception $e){
            $this->setResult($case, Test::ERROR, $e->getMessage(). ' : '. $e->getTraceAsString());

        }
        trace("End running testcase '$case' => RESULT: " . $this->getResult($case)->status);
        if ($this->needTraceDetail() && $this->getResult($case)->status != self::PASSED){
            trace("Detailed:" . strval($this->getResult($case)->detail));
        }
    }
    public function reportResult(){
        $statistic = array();
        foreach ($this->_result as $result) {
            $statistic[$result->status]++;
        }
        echo '<div style="position: fixed; left: 40vw; top: 0; background-color: bisque; padding: 0.3em;">';
        echo "Result: ";
        foreach ($statistic as $status => $count) {
            echo "$status: $count ";
        }
        echo "\n";
        echo '</div>';
    }
    public function setResult($case, $status, $detail=null){
        $this->_result[$case] = $result = new stdClass();
        $result->name = $case;
        $result->status = $status;
        $result->detail = $detail;
    }
    public function getResult($case){
        return $this->_result[$case];
    }

    public function failed($msg){
        throw new TestFailedException($msg);
    }

    public function assert($condition, $msg=null){
        if (!$condition){
            $this->failed($msg);
        }
    }
    protected function needTraceDetail(){
        return true;
    }
    public function getTestSuiteName(){
        return str_replace('Controller', '', get_class($this));
    }
}

class TestFailedException extends Exception{

}

class ExampleTest extends Test{
    public function testSuccessExample(){
        $this->assert(0 == 0);
    }
    public function testFailedExample(){
        $this->assert(0 != 0);
    }
}

class RestfullyTest extends Test{

    // array的key要唯一，要么是数字要么是字符串
    public function checkArrayStructure($example, $data, $throw=true){
        try{
            foreach ($example as $key => $value) {
                $dataKeyChecked = false;
                if (is_numeric($key)){
                    $this->assert(is_array($data));
                    foreach ($data as $dataKey => $dataVal) {
                        $this->assert(is_numeric($dataKey));
                        $this->checkArrayStructure($value, $dataVal);
                    }
                    break;
                } else {
                    $this->assert(isset($data[$key]));
                    $this->checkArrayStructure($value, $data[$key]);

                    if (!$dataKeyChecked){
                        $dataKeyChecked = true;
                        foreach ($data as $dataKey => $dataVal) {
                            $this->assert(is_string($dataKey));
                        }
                    }
                }
            }
        } catch(TestFailedException $e){
            if (!$throw){
                return false;
            }
            throw new TestFailedException($e->getMessage(), $e->getCode(), $e);
        }
        return true;
    }
}

function trace($msg){
    output("TRACE: " . strval($msg) . "\n");
}
function output($msg){
    echo "<pre>" . str_replace("\n", "<br/>", $msg) . "</pre>";
}

/*
 * @param array
 * @method string GET, POST, PUT, DELETE
 * @format string
 * 		'xml' 				=> 'application/xml',
		'json' 				=> 'application/json',
		'serialize' 		=> 'application/vnd.php.serialized',
		'php' 				=> 'text/plain',
    	'csv'				=> 'text/csv',
		'encrypt'			=> 'text/html' (base64 and json)
        'html'              => 'text/html' (no base64)
 * */
function requestUrl($url, $param=null, $method="GET", $format='encrypt'){
    dump(array('URL' => $url, "PARAM" => $param, "method" => $method, "format" => $format));
    if ($format == 'html'){

    } else {
        $restClient = new \RESTClient();
        $response = $restClient->$method($url, $param, $format);
        unset($restClient);
    }
    dump(array('RESPONSE' => $response));
    return $response;
}
function getUrl($url, $param=null, $format=null){
    return requestUrl($url, $param, "GET", $format);
}
function postUrl($url, $param=null, $format=null){
    return requestUrl($url, $param, "POST", $format);
}
function putUrl($url, $param=null, $format=null){
    return requestUrl($url, $param, "PUT", $format);
}
function deleteUrl($url, $param=null, $format=null){
    return requestUrl($url, $param, "DELETE", $format);
}