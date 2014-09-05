<?php
/**
 * Created by PhpStorm.
 * User: panchangyun
 * Date: 14-9-4
 * Time: 上午10:03
 */
function my_autoload_handler($class){
    echo __FUNCTION__ . '( ' . $class . ' )';
}
function println($msg = ''){
    if (func_num_args() > 1):
        foreach (func_get_args() as $a):
            echo $a;
        endforeach;
        echo "<br/>";
    else:
        echo $msg . "<br/>";
    endif;
}
function tick_handler(){
    echo __FUNCTION__ . " is called <br/>";
}
class TestController extends CController {
    public function actionDir(){
        echo `dir`;
    }
    public function actionSet(){
        echo `set`;
    }
    public function actionCd($dir){
        echo `cd $dir`;
    }
    public function actionArray(){
        echo "array:";
        $a = array('a' => 1, 'b' => 2);
        dump($a);
        println(count($a));
        println(sizeof($a));
    }
    public function actionIf(){
        if (1 > 2):
            echo "1 > 2";
        elseif (1 == 1):
            echo "1 == 1";
            echo "...";
        else:
            echo "1";
        endif;
    }
    public function actionTicks(){
        declare(ticks=10){
        register_tick_function('tick_handler');
        for ($i = 0; $i < 10000; $i++){
            println($i);
        }
        }
    }
    public function actionReturn(){
        $path = '/protected/test.php';
        $r = include $path;
        dump($r);
        #$t = require $path;
        #dump($t);
    }
    public function actionGetCwd(){
        println(getcwd());
    }
    public function actionArgs(){
        echo println('num: ', func_num_args());
        println("get: ", func_get_arg(0));
        dump(func_get_args());
    }
    public function actionList(){
        list($a, $b, $c) = array(1,2);
        dump($a);
        dump($b);
        dump($c);
    }
    public function actionPhpInfo(){
        phpinfo();
    }
    public function actionCall($f="help", array $args=array()){
        dump($args);
        $ret = call_user_func_array($f, $args);
        println("RETURN:");
        dump($ret);
    }
    public function actionClosure($name="Jim"){
        $greet = function($name){
            println("Hello ".$name);
        };
        $greet($name);
        dump($greet);
    }
    public function actionAutoload($class="test"){

        try{
            spl_autoload_register('my_autoload_handler');
            dump(spl_autoload_functions());
            dump($class);
            new $class();
        }catch(Exception $e){
            dump($e);
            throw $e;
        }
    }
    public function actionSerialize($obj=null){
        $obj = $obj ? $obj : $this;
        echo serialize($obj);
    }
    public function actionNamespace(){
        echo 'namespace: '. __NAMESPACE__;
    }

    public function actionEval($e=null){
        include "/eval.php";
    }
    public function actionConst(){
        dump(get_defined_constants(true));
    }
    public function actionIncludedFiles(){
        dump(get_included_files());
    }
    public function actionModdate(){
        dump(date('F d Y H:i:s', getlastmod ()));
    }
    public function actionHelp(){
        println("self: ".get_class($this));
        $methods = get_class_methods(get_class($this));
        foreach ($methods as $m):
            $pos = strpos($m, 'action');
            if ($pos !== false):
                println('<a href="/'.substr($m, $pos + strlen('action')).'" >'.substr($m, $pos + strlen('action'))."</a>");
            endif;
        endforeach;
    }

    public function actionView(){
        require("/view.php");
    }
    public function actionStart(){
		require("/start.php");
	}
}
