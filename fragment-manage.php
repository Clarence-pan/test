<?php
/**
 * Created by PhpStorm.
 * User: panchangyun
 * Date: 14-10-29
 * Time: 下午1:14
 */

function get_file_path(){
    return $_REQUEST['originalFilePath'];
}
define('FRAGMENT_BEGIN', '// !BEGIN:');
define('FRAGMENT_END', '// !END:');
define('FRAGMENT_PLACE_HOLDER', '// !PLACE_HOLDER: ');
define('LINE_BREAK', "\r\n");
function pre_process_line($line){
    return str_replace("\n", "", str_replace("\r", "", $line));
}

$actions = array(
    'view' =>
        function () {
            echo "<h>".get_file_path()."</h>";
            echo "<pre>";
            echo htmlspecialchars(file_get_contents(get_file_path()));
            echo "<pre>";
        },
    'break' =>
        function(){
            $dir = dirname(get_file_path());
            $filename = basename(get_file_path());
            $dir .= DIRECTORY_SEPARATOR.'fragment'.DIRECTORY_SEPARATOR.basename(get_file_path(), ".js");
            `md $dir`;

            $oldFile = file(get_file_path());
            $newFile = '';
            $fragments = array();
            $findName = function($line, $mark){
                if (strstr($line, $mark)){
                    $name = str_replace($mark, "", $line);
                    $name = trim($name);
                    if ($name){
                        return $name;
                    }
                }
                return false;
            };
            for ($i = 0, $n = count($oldFile); $i < $n; $i++){
                $line = pre_process_line($oldFile[$i]);

                $name = $findName($line, FRAGMENT_BEGIN);
                if ($name){
                    echo "Info: find match begin of ".$name."<BR/>";
                    $j = $i;
                    $fragmentContent = '';
                    for ($j++; $j < $n; $j++){
                        $line = pre_process_line($oldFile[$j]);
//                        if (strstr($line, $name)){
//                            echo $line."<br/>";
//                        }
                        $matchEnd = $findName($line, FRAGMENT_END);
                        if ($matchEnd && $matchEnd == $name){
                            break;
                        }else{
                            $matchEnd = false;
                            $fragmentContent .= $line . LINE_BREAK;
                        }
                    }
                    if ($matchEnd){
                        $fragments[$name] = $fragmentContent;
                        $i = $j;
                        $newFile .= FRAGMENT_PLACE_HOLDER.$name.LINE_BREAK;
                        echo "Info: find match end of ".$name."<BR/>";
                        continue;
                    }else{
                        echo "Error: cannot find match end of ".$name."<BR/>";
                    }

                }
                $newFile .= $line . LINE_BREAK;
            }

            foreach ($fragments as $name => $content) {
                file_put_contents($dir.DIRECTORY_SEPARATOR.$name.'.js', $content);
            }

            file_put_contents($dir.DIRECTORY_SEPARATOR.$filename, $newFile);

            run_action('view');
        },
    'join' =>
        function(){
            $dir = dirname(get_file_path());
            $filename = basename(get_file_path());
            $dir .= DIRECTORY_SEPARATOR.'fragment'.DIRECTORY_SEPARATOR.basename(get_file_path(), ".js");
            $hdir = dir($dir);
            $fragments = array();
            while($file = $hdir->read()){
                $content = file_get_contents($dir.DIRECTORY_SEPARATOR.$file);
                if ($content){
                    $fragments[$file] = $content;
                }
            }
            
            $newFile = $fragments[$filename];
            echo "before join file length: ".strlen($newFile)."<br/>";
            foreach ($fragments as $key => $value) {
                if ($key == $filename){
                    continue;
                }
//                echo FRAGMENT_PLACE_HOLDER.$key.LINE_BREAK.'<br/>'.
//                    "<pre>".
//                    FRAGMENT_BEGIN.$key.LINE_BREAK.
//                    $value.LINE_BREAK.
//                    FRAGMENT_END.$key.LINE_BREAK.'</pre>';
                $key = basename($key, '.js');

                $newFile = str_replace(FRAGMENT_PLACE_HOLDER.$key.LINE_BREAK,
                                    FRAGMENT_BEGIN.$key.LINE_BREAK.
                                    $value.
                                    FRAGMENT_END.$key.LINE_BREAK,
                                    $newFile);
            }
            echo "after join file length: ".strlen($newFile)."<br/>";

            $oldFile = file_get_contents(get_file_path());
            file_put_contents(get_file_path().'.bak', $oldFile);
            file_put_contents(get_file_path(), $newFile);
            run_action('view');
        },
    'default' =>
        function () {
            run_action('view');
        }
);

function run_action($action){
    global $actions;
    if ($actions[$action]){
        return $actions[$action]();
    } else {
        return $actions['default']();
    }
}


?>
<!DOCTYPE html>
<html>
<head>
    <script type="text/javascript" src="js/jquery.js" ></script>
    <script type="text/javascript">
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
            $('form').append($('<input type="hidden"/>').attr('name', key).attr('value', value)).submit();
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
            window.scroll(0, 9999999999);
        }
        function scrollToTop(){
            window.scrollTo(0, 0);
        }
    </script>
    <title>HOSTS</title>
</head>
<body>
<form target="_self" action="" id="fm" >
    <input type="text" name="originalFilePath" value="<?=$_REQUEST["originalFilePath"] ?>" placeholder="path/to/your/file" />
    <input type="button" value="view" onclick="refresh('action', 'view')" />
    <input type="button" value="break into fragments" onclick="refresh('action', 'break')" />
    <input type="button" value="join fragments" onclick="refresh('action', 'join')" />
</form>
<?= run_action($_REQUEST['action']); ?>
</body>
</html>
