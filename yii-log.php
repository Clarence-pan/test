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
error_reporting(E_ERROR);
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

if ($_POST['CLEAR'] == 'YES') {
    file_put_contents($fileName, "");
    file_put_contents($fileName . '.bak', var_export($log, true));
    echo "Clear finished! (Note: old log file is backup to .bak)";
}
class ArrayLog {
    public function __construct($fileName){
        $this->file = fopen($fileName, "rt");
    }
    public function next(){
        $line = fgets($this->file);
        $line = 'return ' . $line . ';';
        //echo $line;
        //var_dump($line);
        $arr =  eval($line);
        foreach ($arr as &$val) {
            $val = base64_decode($val);
        }
        //var_dump($arr);
        if (feof($this->file)){
            return false;
        } else if (empty($arr)) {
            return array('--', '--', $line);
        }

        return $arr;
    }
    public function __destruct(){
        fclose($this->file);
    }
}

function readSqlLog($fileName='d:\yii-sql.log'){
    $content = file_get_contents($fileName);
    $code = 'return array(' . $content . ');';
    //echo $code;
    return eval_code($code);
}

$log = new ArrayLog($fileName);

//$log = readSqlLog($fileName);
//var_dump($log);
//$log = array_reverse($log);

?>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <!--
    <script type='text/javascript' src='js/jquery.js'></script>
    <script type='text/javascript' src='js/jquery.autocomplete.js'></script>
    <script type="text/javascript" src="js/jquery-ui/jquery-ui.js" >    </script>
    -->
    <script type="text/javascript">
        function toggle_stack_trace(){
            $('.stackTrace').toggle();
        }
        function refresh(key, value){
//            $href = window.location.href;
//            if ($href[$href.length-1] != '?'){
//                $href = $href + "?";
//            }
//            $href = $href + $param;
//            window.open($href, "_self");
            var query = getCurrentParams();
            query[key] = value;
            query = buildQueryString(query);
            location.replace(query);
        }
        function getCurrentParams(){
            var params = {};
            var searches = window.location.search.substr(1).split("&")
            for (var s in searches){
                var i = s.indexOf('=');
                if (i>0){
                    params[s.substr(0,i)] = s.substr(i+1);
                }
            }
            return params;
        }
        function buildQueryString(params){
            var query = "?";
            for (var i in params){
                query = query + i + "=" + params[i] + "&";
            }
            return query;
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
        body {
            background-color: rgba(152, 251, 152, 0.24);
            margin-top: 3em;
        }
        ul.log {
            margin: 0;
            padding: 0;
            border: 0;
            border-top-color: rgba(82, 168, 236, 0.6);
            border-top-width: 1px;
            border-top-style: solid;
        }
        li {
            display: inline;
            white-space: pre-wrap;
            font-size: 13px;
            color: darkblue;
        }
        .level {
            display: none !important;
        }
        .category {
            display: none !important;
        }
        .msgHead {
            font-weight: bold;
            min-width: 5em;
        }
        .msgBody {
            min-width: 60vw;
            max-width: 70vw;
            white-space: pre-wrap;
            position: relative;
            left: 1em;
            color: black;
        }
        .line {

        }
        .stackTrace{
            <?PHP if (!$_REQUEST['displayStackTrace']): ?>
            display: none !important;
            <?PHP else: ?>
            display: block !important;
            float: right;
            position: relative;
            left: 50vw;
            <?PHP endif; ?>
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
        <input type="button" onclick="refresh('displayStackTrace',1)" Value="Display Stack Trace" />
        <a href="#bottom" >BOTTOM</a>
    </form>
</div>


<?php for ($logline = $log->next(), $id=100000; $logline; $logline = $log->next(), $id++): ?>
        <ul id="<?=$id?>" class="log">
            <li class="line"><?= $id + 1 - 100000 ?>:</li>
            <?php foreach ($logline as $key => $logValue): ?>
                <li class="<?= $key ?>"><?= $logValue ?></li>
            <?php endforeach; ?>
        </ul>
        <?PHP if ($id % 200 == 0): ?>
        <script type="application/javascript">
            (function(){
                location.replace("#<?= $id ?>" );
            })();
        </script>
        <?PHP endif; ?>
<?php endfor; ?>
<div id="bottom" ></div>
<script type="application/javascript">
    (function(){
        location.replace("#bottom" );
    })();
</script>
</body>
</html>