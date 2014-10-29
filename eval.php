
<html lang="zh_CN">
<head>
    <title>EVAL</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <script type="text/javascript" >
        function eval_form_submit(){
            eval_form.submit();
        }
    </script>
</head>
<body>
    <form target="_self" method="post" id="eval_form" >
        <textarea id="e" name="e" cols="80" rows="40" placeholder="Input what to eval..." onchange="javascript:eval_form_submit();" ><?= $_REQUEST['e'] ?></textarea>
        <br/>
        <input type="submit" value="                                                 EVAL IT                                                        " />
    </form>
    <?php if ($_REQUEST['e'] || strstr($_SERVER['QUERY_STRING'], 'global')):   ?>
        <div id="result">
            <?php
            function my_eval($code){
                if (!strstr($code, 'return') && !strstr($code, "\n")){
                    $code = 'return ' . $code;
                }
                $filename = "eval-code.php";
                $file = fopen($filename, "wt");
                fwrite($file, '<?PHP ' . $code);
                fclose($file);
                return require($filename);
            }

            ?>
            <?php
                if ($_REQUEST['e']){
                    $result = my_eval($_REQUEST['e'] . ';' );
                }else{
                    $result = my_eval('dump($GLOBALS);');
                }
            ?>
            <h3>Eval Result:</h3>
            <?php   dump($result);           ?>
        </div>
    <?php endif; ?>
</body>
</html>
