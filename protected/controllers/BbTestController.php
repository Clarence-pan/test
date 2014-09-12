<?php

/**
 * Created by PhpStorm.
 * User: panchangyun
 * Date: 14-9-9
 * Time: 下午4:24
 */
class BbTestController extends CController
{
    public function makeDb(CDbConnection $db)
    {
        $bb_account_sql = $db->createCommand('INSERT INTO bb_account
                                        ( account_name, vendor_id )
                                        VALUES
                                        ( :account_name, :vendor_id )');
        $cps_order_sql = $db->createCommand('INSERT INTO cps_order
                                        ( vendor_id, order_id, place_order_time, sign_contract_time)
                                        VALUES
                                        ( :vendor_id, :order_id, :place_order_time, :sign_contract_time)
                                        ');
        $cps_purchase_order_sql = $db->createCommand('INSERT INTO cps_purchase_order
                                        ( order_id, expense, vendor_id, expense_ratio, purchase_order_id)
                                        VALUES
                                        ( :order_id, :expense, :vendor_id, :expense_ratio, :purchase_order_id)');
        $order_id = 10000;
        for ($i = 500; $i < 600; $i++) {
            $bb_account_sql->execute(array(':account_name' => 'vendor' . $i,
                ':vendor_id' => $i));
            for ($j = 0; $j < 10; $j++) {
                $order_id++;
                $cps_order_sql->execute(array(':vendor_id' => $i,
                    ':order_id' => $order_id,
                    ':place_order_time' => date("Y-m-D H:i:s"), // '0000-00-00 00:00:00' COMMENT '下单时间',
                    ':sign_contract_time' => date('Y-m-d'))); //'0000-00-00' COMMENT '签约时间',
                for ($k = 0; $k < 20; $k++) {
                    $cps_purchase_order_sql->execute(array(':order_id' => $order_id,
                        ':expense' => ($k * 88),
                        ':vendor_id' => $i,
                        ':purchase_order_id' => ($order_id * 20 + $k),
                        ':expense_ratio' => (0.03 + $k / 2000)));
                }
            }
        }

    }

    public function actionGenDb()
    {
        $this->makeDb(Yii::app()->bbdb);
    }
} 