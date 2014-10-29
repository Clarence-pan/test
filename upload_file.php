
<script type="text/javascript">
    function close_this(){
        window.close();
    }
</script>
<a href="javacript:close_this()">close window</a>

<?php
/**
 * Created by PhpStorm.
 * User: panchangyun
 * Date: 14-10-9
 * Time: 下午1:15
 */

require("/plib/dump/dump.php");

dump($GLOBALS);
$file = array_shift($_FILES);
$r = move_uploaded_file($file['tmp_name'], 'd:\\tmp_upload.tmp');
dump($r);

?>