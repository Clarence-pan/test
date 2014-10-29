<?PHP
namespace {
    function dump($var, $echo=true, $title=""){
        return \plib\dump\dump($var, $echo, $title);
    }

    ####################just for dump debug######################
    require_once(dirname(__FILE__).'/CVarDumper.php');
}
namespace plib\dump{

    function dump($var, $echo=true, $title="") {
        $result = "";
        if ($title != ""){
            $result .= "<b>" . $title . ":</b><br/>";
        }
        $result .= "<pre>";
        $result .= CVarDumper::dumpAsString($var, 10, false);
        $result .= "</pre>";
        $result .= "<br/>";
        if ($echo) {
            echo $result;
        }
        return $result;
        //return var_dump($var);
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
}