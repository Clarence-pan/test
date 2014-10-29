<?php
/**
 * Created by PhpStorm.
 * User: panchangyun
 * Date: 14-9-30
 * Time: 上午10:38
 */


class TestMessageQueueController extends CController {
    public function actionEnque($qId, $data){
        $q = msg_get_queue($qId);
        $r = msg_send($q, 1, $data);
        echo "Result: " . $r;
    }
    public function actionDequeue($qId, $data){
        $q = msg_get_queue($qId);
        $r = msg_receive($q, 1, $msgType, 10000, $msg);
        echo "Result: ".$r ."<br/>";
        echo " Got msg: " . $msg;
    }
} 