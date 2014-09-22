<?php
/**
 * Created by PhpStorm.
 * User: panchangyun
 * Date: 14-9-19
 * Time: 下午1:49
 */

function eval_code($code){
    if (!strstr($code, 'return') && !strstr($code, "\n")){
        $code = 'return ' . $code;
    }
    $filename = "eval-code.php";
    $file = fopen($filename, "wt");
    fwrite($file, '<?PHP ' . $code);
    fclose($file);
    return require($filename);
}