<?PHP if(!$_REQUEST["autoAppend"]){ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?PHP } ?>
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

if ($_REQUEST['clear']) {
    file_put_contents($fileName, "");
    file_put_contents($fileName . '.bak', var_export($log, true));
    echo "Clear finished! (Note: old log file is backup to .bak)";
}
class ArrayLog {
    public $fileSize;
    public $filemtime;
    public $fileName;
    public function __construct($fileName){
        $this->file = fopen($fileName, "rb");
        $this->fileName = $fileName;
        $this->fileSize = filesize($fileName);
        $this->filemtime = filemtime($fileName);
    }
    public function seek($offset){
        fseek($this->file, $offset);
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

function output_logs($log, $id=100000){
    for ($logline = $log->next(); $logline; $logline = $log->next(), $id++): /* id="<?=$id?>" */ ?>
        <ul  class="log">
            <li class="line"><?= $id + 1 ?>:</li>
            <?php foreach ($logline as $key => $logValue): ?>
                <li class="<?= $key ?>"><?= $logValue ?></li>
            <?php endforeach; ?>
        </ul>
    <?PHP if ($id % 200 == 0): ?>
        <script type="application/javascript">
            scrollToBottom();
        </script>
    <?PHP endif;
    endfor;
    return $id;
}

?>
<?PHP if(!$_REQUEST["autoAppend"]){ ?>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <!--
    <script type='text/javascript' src='js/jquery.js'></script>
    <script type='text/javascript' src='js/jquery.autocomplete.js'></script>
    <script type="text/javascript" src="js/jquery-ui/jquery-ui.js" >    </script>
    -->
    <script type="text/javascript">
        function getGlobal(key){
            var global = (function(){return this;})();
            if (!key){
                return global;
            }
            return global[key];
        }
        function setGlobal(key, value){
            var global = (function(){return this;})();
            global[key] = value;
        }
        function toggle_stack_trace(){
            $('.stackTrace').toggle();
        }
        function buildQuery(key, value){
            var query = getCurrentParams();
            if (typeof(key) == 'object'){
                for (var k in key){
                    query[k] = key[k];
                }
            } else {
                query[key] = value;
            }
            query = buildQueryString(query);
            return query;
        }
        function refresh(key, value){
//            $href = window.location.href;
//            if ($href[$href.length-1] != '?'){
//                $href = $href + "?";
//            }
//            $href = $href + $param;
//            window.open($href, "_self");
            var query = buildQuery(key, value);
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
        function autoAppend(){
            var url = buildQuery({"seek": getGlobal('fileSize'),
                                   "autoAppend": 1,
                                   "id": getGlobal('itemId')});
            ajaxGetContent(url, true, function(content){
                var div = document.createElement('div');
                div.innerHTML = content;
                document.body.appendChild(div);
                scrollToBottom();
                var trick = '<!-- MUST RUN:';
                var i = content.indexOf(trick);
                if (i > 0){
                    eval(content.substr(i + trick.length));
                }
            });
            if (window.stopAutoAppend){
                return;
            }
            setTimeout("autoAppend()", 1000);
        }
        function ajaxGetContent(url, async, resultCallbackFunc){
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function(){
                if (xhttp.readyState == 4 && xhttp.status == 200){
                    resultCallbackFunc(xhttp.responseText);
                }
            };
            xhttp.open("GET", url, async);
            xhttp.send();
        }
        function scrollToBottom(){
//            var bottom = document.getElementById('bottom');
//            if (!bottom){
//                bottom = document.createElement("div");
//                bottom.id = 'bottom';
//            }
//            document.body.appendChild(bottom);
//            location.replace("#bottom");
            window.scroll(0, 9999999999);
        }
        function scrollToTop(){
            window.scrollTo(0, 0);
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
        li.level {
            display: none !important;
        }
        li.category {
            display: none !important;
        }
        li.msgHead {
            font-weight: bold;
            min-width: 5em;
        }
        li.msgBody {
            display: block;
            position: relative;
            left: 11em;
            white-space: pre-wrap;
            color: black;
        }
        li.line {

        }
        li.stackTrace{
            <?PHP if (!$_REQUEST['displayStackTrace']): ?>
            display: none !important;
            <?PHP else: ?>
            display: block !important;
            position: relative !important;
            left: 11em !important;
            <?PHP endif; ?>
            white-space: pre-wrap;
        }
        .tools{
            position: fixed;
            left: 20vw;
            top: 1vh;
            z-index: 10;
        }
    </style>
</head>
<body>
<div class="tools">
    <form method="GET" action="<?= $_SERVER['REQUEST_URI'] ?>" >
        <input type="submit" value="Refresh">
        <input type="button" onclick="refresh('clear', 1)" Value="Clear" />
        <input type="button" onclick="refresh('displayStackTrace',1)" Value="Display Stack Trace" />
        <input type="button" onclick="refresh('seek', getGlobal('fileSize'))" value="See new"/>
        <input type="button" onclick="window.stopAutoAppend = false; autoAppend();" value="Auto append" />
        <input type="button" onclick="window.stopAutoAppend = true" value="Stop auto append" />
        <input type="button" onclick="scrollToTop()" value="Top" />
        <input type="button" onclick="scrollToBottom()" value="Bottom" />
    </form>
</div>
<?php }?>
<script type="application/javascript">
    console.log("Last modified time: <?= date('Y-m-d H:i:s', $log->filemtime) ?>, file size: <?= $log->fileSize ?>bytes");
</script>

<?PHP
if ($_REQUEST['seek']){
    $log->seek(intval($_REQUEST['seek']));
}
$id =  $_REQUEST["id"];
$id = $id ? $id : 0;
$id = output_logs($log, $id);
?>

<script type="application/javascript" >
    setGlobal("itemId",  <?= $id ?>);
    setGlobal("fileSize", <?= $log->fileSize ?>);
    scrollToBottom();
</script>
<!-- MUST RUN: // The above maybe cannot run, so eval the below:
    setGlobal("itemId",  <?= $id ?>);
    setGlobal("fileSize", <?= $log->fileSize ?>);
//-->