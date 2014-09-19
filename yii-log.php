<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?php
/**
 * Created by PhpStorm.
 * User: panchangyun
 * Date: 14-9-18
 * Time: 上午10:37
 */
if (!isset($fileName)) {
    $fileName = 'd:\yii-array.log';
}
/*
 *
array (
  'time' => '2014-09-18 10:36:36',
  'level' => 'trace',
  'category' => 'system.db.CDbCommand',
  'msgHead' => 'Executing SQL',
  'msgBody' => ' INSERT INTO method_log_02	(log_pos,content,extend_id,type,msg,add_time)	VALUES(\'StatementMod::getFmisCharts::529\', "{uid:7329,token:ST-4935246-LGBrCMbt0FOtfue5T3gl-cas,nickname:\\u6f58\\u660c\\u8d5f,r:0.27386932098307,isExcel:0,agencyId:4333,agencyName:,period:,startDate:,start:0,limit:10,sortname:,sortorder:}", 0, \'2\', "获取财务报表：0", \'2014-09-18 10:36:36\');',
  'stackTrace' => ...
 * */
require("/plib/eval_code.php");

class ArrayLog {
    public function __construct($fileName){
        $this->file = fopen($fileName, "rt");
    }
    public function next(){
        $line = fgets($this->file);
        $line = rtrim($line, ',');
        return eval($line);
    }
}

function readSqlLog($fileName='d:\yii-sql.log'){
    $content = file_get_contents($fileName);
    $code = 'return array(' . $content . ');';
    //echo $code;
    return eval_code($code);
}

//$log = readSqlLog($fileName);
//var_dump($log);
//$log = array_reverse($log);

?>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <script type='text/javascript' src='js/jquery.js'></script>
    <script type='text/javascript' src='js/jquery.autocomplete.js'></script>
    <script type="text/javascript" src="js/jquery-ui/jquery-ui.js" >    </script>
    <script type="text/javascript">
        function toggle_stack_trace(){
            $('.stackTrace').toggle();
        }
    </script>
    <title></title>
    <style type="text/css" >
        table {
            margin: 0;
            border: 0;
            padding: 0;
        }
        tr {
            margin: 0;
            padding: 0;
        }
        thead {
            margin: 0;
            border: 0;
            padding: 0;
            font-weight: bold;
        }
        tbody {
            margin: 0;
            border: 0;
            padding: 0;
        }
        thead {
            margin: 0;
            border: 0;
            padding: 0;
        }
        td {
            padding: 0;
            margin: 0;
            clear: both;
            color: rgb(51, 51, 51);
            font-size: 13px;
            line-height: 18px;
            position: relative;
            border-bottom-width: 1px;
            border-bottom-color: rgba(82, 168, 236, 0.6);
            border-bottom-style: solid;
            vertical-align: top;
        }
        .level {
            display: none;
        }
        .category {
            display: none;
        }
        .msgHead {
            font-weight: bold;
            min-width: 5em;
        }
        .msgBody {
            min-width: 60vw;
            max-width: 70vw;
            white-space: pre-wrap;
        }
        .stackTrace{
            display: none;
            white-space: pre-wrap;
            max-height: 33vh;
            min-width: 80vw;
            max-width: 100vw;
            overflow-y: scroll;
            overflow-x: hidden;
        }
        .tools{
            position: fixed;
            left: 40vw;
            top: 1vh;
            z-index: 10;
        }
    </style>
</head>
<body>
<div class="tools">
    <form method="POST" action="<?= $_SERVER['REQUEST_URI'] ?>" >
        <input type="hidden" name="CLEAR" id="CLEAR" value="YES" />
        <input type="submit" value="Clear">
    </form>
    <form method="GET" action="<?= $_SERVER['REQUEST_URI'] ?>" style="position: relative; top: -1.5em; left: 5em" >
        <input type="submit" value="Refresh">
        <label onclick="toggle_stack_trace()">Toggle Stack Trace</label>
    </form>

</div>
<?php
if ($_POST['CLEAR'] == 'YES') {
    file_put_contents($fileName, "");
    file_put_contents($fileName . '.bak', var_export($log, true));
    echo "Clear finished! (Note: old log file is backup to .bak)";
    $log = array();
}
?>
<?php foreach ($log as $logLine): ?>
    <table >
        <tbody>
        <tr>
            <?php foreach ($logLine as $key => $logValue): ?>
                <td><pre class="<?= $key ?>"><?= $logValue ?></pre></td>
            <?php endforeach; ?>
        </tr>
        </tbody>
    </table>
<?php endforeach; ?>
</body>
</html>