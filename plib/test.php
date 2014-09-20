<?php

namespace plib;
use \Exception;
use \stdClass;
/**
 * Created by PhpStorm.
 * User: panchangyun
 * Date: 14-9-20
 * Time: 下午3:04
 */

// start test:
// 1: Test::runTests()
class Test {
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
        trace("Begin running testcase '$case'...");
        try{
            $this->setResult($case, Test::FAILED);
            call_user_func_array(array($this, 'test' . $case), array());
            $this->setResult($case, Test::PASSED);
        } catch (TestFailedException $e){
            $this->setResult($case, Test::FAILED, $e);
        } catch (Exception $e){
            $this->setResult($case, Test::ERROR, $e);
        }
        trace("End running testcase '$case' => RESULT: " . $this->getResult($case)->status);
    }
    public function reportResult(){
        $statistic = array();
        foreach ($this->_result as $result) {
            $statistic[$result->status]++;
        }
        output("Result: ");
        foreach ($statistic as $status => $count) {
            output("$status: $count ");
        }
        output("\n");
    }
    public function setResult($case, $status, $detail=null){
        $this->_result[$case] = $result = new stdClass();
        $result->name = $case;
        $result->status = $status;
        $detail->detail = $detail;
    }
    public function getResult($case){
        return $this->_result[$case];
    }

    public function failed(){
        throw new TestFailedException();
    }

    public function assert($condition){
        if (!$condition){
            $this->failed();
        }
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

function trace($msg){
    output("TRACE: " . strval($msg) . "\n");
}
function log($msg){
    output("LOG: " . strval($msg) . "\n");
}
function output($msg){
    echo "<pre>" . str_replace("\n", "<br/>", $msg) . "</pre>";
}