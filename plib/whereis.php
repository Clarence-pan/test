<?php
/**
 * Created by PhpStorm.
 * User: panchangyun
 * Date: 14-9-20
 * Time: 下午1:52
 */
//var_dump($GLOBALS);
$path = explode(';', $_SERVER['Path']);
$exe = $_SERVER['argv'][1];
$exts = explode(';', $_SERVER['PATHEXT']);
$ext[] = '';
foreach ($path as $dir) {
    foreach ($exts as $ext){
        $file = $dir . '\\' . $exe . $ext;
        //echo $file;
        if (file_exists($file)){
            echo $file . "\n";
        }
    }
}

