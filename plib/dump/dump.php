<?PHP

####################just for dump debug######################
require_once(dirname(__FILE__).'/CVarDumper.php');
function dump($var, $title="") {
    if ($title != ""){
        echo "<b>" . $title . ":</b><br/>";
    }
    echo "<pre>";
    CVarDumper::dump($var, 10, true);
    echo "</pre>";
    echo "<br/>";
}

if (false){
    dump($_SERVER, 'server');
    dump($_GET, "get");
    dump($_POST, 'post');
    dump($_COOKIE, "cookie");
    dump($_FILES, "files");
    dump($_ENV, "env");
    dump($GLOBALS, 'globals');
}
#############################################################

function fill_sql($sql, $params){
    foreach ($params as $key => $value){
        $sql = str_replace($key, "'" . $value . "'", $sql);
    }
    return $sql;
}

function debug($action, $cookie=null){
    call_user_func($action, $cookie);
}

//define('DEBUG_IS_OPEN', false);
define('DEBUG_IS_OPEN', true);
