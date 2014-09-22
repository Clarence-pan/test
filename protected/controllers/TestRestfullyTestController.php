<?php
/**
 * Created by PhpStorm.
 * User: panchangyun
 * Date: 14-9-22
 * Time: 上午9:16
 */
require('/plib/test.php');
// http://test/TestRestfullyTest/run
//
class TestRestfullyTestController extends RestfullyTest {
    public function testCheckArrayStructure_simpleKeyValue_OK(){
        $base = array("a" => 1);
        $tests = array(
            array('a'=>1),
            array('a'=>2),
            array('a'=>''),
        );
        $this->assert($this->checkArrayStructure($base, $base, false));
        foreach ($tests as $test){
            $this->assert($this->checkArrayStructure($base, $test, false));
        }
    }
    public function testCheckArrayStructure_simpleKeyValue_FAIL(){
        $base = array("a" => 1);
        $tests = array(
            array(),
            array('b'=>1),
            array('a'=>null),
            null,
        );
        $this->assert($this->checkArrayStructure($base, $base, false));
        foreach ($tests as $test){
            $this->assert(!$this->checkArrayStructure($base, $test, false));
        }
    }
    public function testCheckArrayStructure_justNumbers_OK(){
        $base = array(1,2,3);
        $tests = array(
          array(),
          array(1,2),
          array(1,23,4,5,6),
        );
        $this->assert($this->checkArrayStructure($base, $base, false));
        foreach ($tests as $test){
            $this->assert($this->checkArrayStructure($base, $test, false));
        }
    }
    public function testCheckArrayStructure_justNumbers_Fail(){
        $base = array(1,2,3);
        $tests = array(
            array('a' => 1),
            array('b' => 2),
        );
        $this->assert($this->checkArrayStructure($base, $base, false));
        foreach ($tests as $test){
            $this->assert(!$this->checkArrayStructure($base, $test, false));
        }
    }
    public function testCheckArrayStructure_justNumbers_Fail2(){
        $base = array(1,2,3);
        $tests = array(
            null,
        );
        $this->assert($this->checkArrayStructure($base, $base, false));
        foreach ($tests as $test){
            $this->assert(!$this->checkArrayStructure($base, $test, false));
        }
    }
    public function testCheckArrayStructure_mix_OK(){
        $base = array('a'=> 1,
                       'b'=> array(1,2,3));
        $tests = array(
            array('a' => 1, 'b' => array(1,2,3,4)),
            array('a' => 1, 'b' => array(1,2)),
            array('a' => 1, 'b' => array()),
        );
        $this->assert($this->checkArrayStructure($base, $base, false));
        foreach ($tests as $test){
            $this->assert($this->checkArrayStructure($base, $test, false));
        }
    }
    public function testCheckArrayStructure_mix_OK2(){
        $base = array('a'=> 1,
            'b'=> array(1,2,3));
        $tests = array(
            array('a' => 1, 'b' => array(), 'c' => array()),
        );
        $this->assert($this->checkArrayStructure($base, $base, false));
        foreach ($tests as $test){
            $this->assert($this->checkArrayStructure($base, $test, false));
        }
    }
    public function testCheckArrayStructure_mix_FAIL(){
        $base = array('a'=> 1,
            'b'=> array(1,2,3));
        $tests = array(
            array('a' => 1,), // 缺少key b
            array('a' => 1, 'b' => array('c'=>1)), // key b的数据的结构不对
            array('a' => 1,  'c' => array()), //缺少key b
            array(), // 缺少a和b
            array('b' => array(1,2,3)), // 缺少a
        );
        $this->assert($this->checkArrayStructure($base, $base, false));
        foreach ($tests as $test){
            $this->assert(!$this->checkArrayStructure($base, $test, false));
        }
    }
} 