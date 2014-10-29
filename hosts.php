<?php
/**
 * Created by PhpStorm.
 * User: panchangyun
 * Date: 14-10-29
 * Time: 下午1:14
 */

function get_hosts_path(){
    return $hostsPath = 'C:\\Windows\\System32\\drivers\\etc\\hosts';
}

function get_tuniu_com(){
    $domains = 'www.tuniu.com deluxe.tuniu.com top.tuniu.com wenquan.tuniu.com menpiao.tuniu.com xm.tuniu.com cz.tuniu.com ty.tuniu.com dl.tuniu.com bbs.tuniu.com zhoubian.tuniu.com nj.tuniu.com miao.tuniu.com huaxue.tuniu.com chujing.tuniu.com jingdian.tuniu.com nj.tuniu.com hz.tuniu.com bj.tuniu.com sh.tuniu.com sz.tuniu.com tj.tuniu.com shz.tuniu.com cd.tuniu.com wh.tuniu.com cq.tuniu.com nb.tuniu.com xa.tuniu.com wx.tuniu.com sy.tuniu.com gz.tuniu.com chujing.tuniu.com hainan.tuniu.com sh.tuniu.com bj.tuniu.com deluxe.tuniu.com top.tuniu.com www.tuniu.com wenquan.tuniu.com menpiao.tuniu.com zhoubian.tuniu.com miao.tuniu.com bbs.tuniu.com huaxue.tuniu.com fujian.tuniu.com yunnan.tuniu.com chujing.tuniu.com youji.tuniu.com hainan.tuniu.com maldives.tuniu.com sh.tuniu.com bj.tuniu.com nj.tuniu.com hz.tuniu.com sz.tuniu.com tj.tuniu.com shz.tuniu.com cd.tuniu.com wh.tuniu.com qd.tuniu.com sy.tuniu.com cq.tuniu.com nb.tuniu.com xa.tuniu.com wz.tuniu.com wx.tuniu.com cs.tuniu.com anhui.tuniu.com market.tuniu.com union.tuniu.com hotel.tuniu.com tour.tuniu.com www.hz.tuniu.com www.bj.tuniu.com www.sh.tuniu.com www.nj.tuniu.com www.sz.tuniu.com wwww.tuniu.com ww.tuniu.com jobs.tuniu.com gz.tuniu.com cz.tuniu.com xiamen.tuniu.com anji.tuniu.com chengdu.tuniu.com dali.tuniu.com dalian.tuniu.com guilin.tuniu.com haerbin.tuniu.com hangzhou.tuniu.com hengdian.tuniu.com huangshan.tuniu.com jiuzhaigou.tuniu.com konglongyuan.tuniu.com kunming.tuniu.com lasa.tuniu.com lijiang.tuniu.com lushan.tuniu.com nanjing.tuniu.com nanning.tuniu.com putuoshan.tuniu.com qiandaohu.tuniu.com qingdao.tuniu.com rizhao.tuniu.com sanqingshan.tuniu.com sanya.tuniu.com shaoxing.tuniu.com suzhou.tuniu.com taishan.tuniu.com wuxi.tuniu.com wuyuan.tuniu.com wuzhen.tuniu.com xm.tuniu.com ty.tuniu.com dl.tuniu.com jingdian.tuniu.com xiaman.tuniu.com xian.tuniu.com yancheng.tuniu.com yandangshan.tuniu.com zhangjiajie.tuniu.com zhouzhuang.tuniu.com tuniu.com zixun.tuniu.com youlun.tuniu.com search.tuniu.com pinpan.tuniu.com luxiantu.tuniu.com blogcn.tuniu.com eol.tuniu.com jschina.tuniu.com yingjiesheng.tuniu.com onlinesh.tuniu.com cctv.tuniu.com yjdy.tuniu.com guonei.tuniu.com';
    return explode(' ', $domains);
}

$actions = array(
    'view' =>
        function () {
            echo "<h>".get_hosts_path()."</h>";
            echo "<pre>";
            echo file_get_contents(get_hosts_path());
            echo "<pre>";
        },
    'add-tuniu.com' =>
        function(){
            $domains = get_tuniu_com();
            $addition =  "# ---- auto generated for tuniu.com begin:----- \n" .
                "127.0.0.1 ".implode("\n127.0.0.1 ", $domains)."\n".
                "# ---- auto generated for tuniu.com end:----- \n";
            $oldFile = file_get_contents(get_hosts_path());
            file_put_contents(get_hosts_path().'.bak', $oldFile);
            $newFile = $addition . $oldFile;
            file_put_contents(get_hosts_path(), $newFile);
            run_action('view');
        },
    'remove-tuniu.com' =>
        function(){
            $oldFile = file_get_contents(get_hosts_path());
            file_put_contents(get_hosts_path().'.bak', $oldFile);
            $lines = explode("\n", $oldFile);
            foreach ($lines as $i => $line) {
                if (strstr($line, '.tuniu.com') or strstr($line, ' tuniu.com')){
                    unset($lines[$i]);
                }
            }
            $newFile = implode("\n", $lines);
            file_put_contents(get_hosts_path(), $newFile);
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
    <title>HOSTS</title>
</head>
<body>
    <form target="_self" action="" id="fm" >
        <input type="button" value="view" onclick="refresh('action', 'view')" />
        <input type="button" value="add tuniu.com" onclick="refresh('action', 'add-tuniu.com')" />
        <input type="button" value="remove tuniu.com" onclick="refresh('action', 'remove-tuniu.com')" />
    </form>
    <?= run_action($_REQUEST['action']); ?>
</body>
</html>
