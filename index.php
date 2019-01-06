<?php
/**
 * Created by PhpStorm.
 * User: haibin
 * Date: 2017/2/28
 * Time: 16:21
 */
require_once 'common.php';
$fol = '';

if (!empty($_REQUEST['f'])) {
    // $fol = base64_decode($_REQUEST['f']) . '/';
    $fol = $_REQUEST['f'];
}
$fs = explode('/',$fol);
$fol .= '/';

$localPath = PHOTOPATH . $fol;

$dp = dir($localPath);
$dirs = [];
$files = [];
while ($file = $dp->read()) {
    if (is_dir($localPath . $file) && $file != '.' && $file != '..' && $file != 'tmp') {
        $dirs[] = $file;
    }
    if (is_file($localPath . $file) && isImg($localPath . $file)) {
        $files[] = $file;
    }
}
sort($dirs);
sort($files);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>stu</title>
    <link href="//apps.bdimg.com/libs/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">
    <style type="text/css">
    .main { padding: 10px; }
    .picimg { display: none; position: absolute; cursor: zoom-in; }
    .menu { display: none; position: fixed; width: 100%; text-align: center; bottom: 10%; }
    .menu div { border: 1px solid #ededed; padding: 0; display: inline-block; }
    .menu .btn { display: inline-block; width: 100px; height: 60px; line-height: 40px; font-size: 22px; background: none; border: none; color: #fff; cursor: pointer; }
    .menu .btn:hover, .menu .btn.act { color: #e85f6b; }
    </style>
</head>
<body>

<div id="main" class="container-fluid">
    <br />
    <!--面包屑-->
    <ol class="breadcrumb">
        <li><a href="<?PHP echo BASEURL; ?>">主页</a></li>
        <?PHP $lpath = '/';
        for ($i = 0; $i < count($fs); $i++) {
            $f = $fs[$i];
            if (!$f) {continue;}
            $lpath .= $f . '/';
            if ($i == count($fs) - 1) { ?>
                <li><?PHP echo $f ?></li>
            <?PHP } else { ?>
                <li><a href="<?PHP echo BASEURL; ?>index.php?f=<?PHP echo urlencode($lpath); ?>" title="<?PHP echo $lpath ?>"><?PHP echo $f ?></a></li>
            <?PHP }
        } ?>
    </ol>
    <div class="row">
        <!--文件夹-->
        <div class="<?PHP echo count($dirs) == 0?'hide':''; echo (count($dirs) > 0 && count($files) == 0)? 'col-md-12':'col-sm-3 col-md-3 col-lg-2' ?>">
            <div class="list-group">
                <?PHP foreach ($dirs as $f) { ?>
                    <a class="list-group-item" href="<?PHP echo BASEURL; ?>index.php?f=<?PHP echo urlencode($fol . $f); ?>" title="<?PHP echo $f ?>">
                        <span><?PHP echo $f ?></span>
                    </a>
                <?PHP } ?>
            </div>
        </div>
        <!-- 图片 -->
        <div class="<?PHP echo count($files) == 0?'hide':''; echo (count($dirs) == 0 && count($files) > 0)? 'col-md-12':'col-sm-9 col-md-9 col-lg-10' ?>">
            <div class="row">
                <?PHP for ($i = 0; $i < count($files); $i++) { ?>
                    <div class="col-xs-12 col-sm-4 col-md-3 col-lg-2">
                        <a class="thumbnail" data-index="<?PHP echo $i; ?>" onclick="showpic(<?PHP echo $i; ?>);">
                            <img src="<?PHP echo BASEURL; ?>tmb.php?f=<?PHP echo urlencode($fol . $files[$i]); ?>"/>
                        </a>
                    </div>
                <?PHP } ?>
            </div>
        </div>
    </div>
</div>

<div>
    <img class="picimg" id="picimg" onload="resizeImg()" src="" style="max-width: 100%; max-height: 100%;" zoom="0"/>
    <div class="menu" id="menu">
        <div>
            <a class="btn" onclick="prev();">上一张</a>
            <a class="btn" onclick="changeZoom(0);" class="act">全显</a>
            <a class="btn" onclick="changeZoom(1);">满铺</a>
            <a class="btn" onclick="changeZoom(2);">原始</a>
            <a class="btn" onclick="next();">下一张</a>
            <a class="btn" id="stu" href="javascript:;" target="_blank">识图</a>
            <a class="btn" onclick="hidepic();">关闭</a>
        </div>
    </div>
</div>

<script type="text/javascript" src="//apps.bdimg.com/libs/jquery/1.10.2/jquery.min.js"></script>
<script type="text/javascript" src="//apps.bdimg.com/libs/bootstrap/3.3.4/js/bootstrap.min.js"></script>
<script type="text/javascript">
    var $imgs = [<?PHP foreach ($files as $f) {echo '"' . $fol . $f . '",' . PHP_EOL;}?>];
    var x = 0;
    var y = 0;
    var l = 0;
    var t = 0;
    var $dis = false;
    var $otop = 0;
    var $oleft = 0;
    var $keyisdown = false;
    $(function () {
        $(window).resize(function () {resizeImg();});
        $(".folder").attr('src', 'static/images/folder1.png');
        $("#picimg")
            .click(function () {
                return false;
                var $img = $("#picimg");
                var $cltype = $img.attr('data-click');
                if ($cltype == 'next') {next();}
                else if ($cltype == 'big') {
                    if ($img.attr('zoom') == 0) changeZoom(1);
                    else if ($img.attr('zoom') == 1) changeZoom(2);
                    else if ($img.attr('zoom') == 2) changeZoom(0);

                }
                else if ($cltype == 'prev') {prev();}
            })
            .mousemove(function (e) {
                var $img = $("#picimg");
                /* 点击上一张下一张
                if (e.offsetX < $img.width() / 3) {
                    $img.css('cursor', 'url(static/images/cur/pic_prev.cur), auto');
                    $img.attr('data-click','prev');
                }
                else if (e.offsetX < $img.width() / 3 * 2) {
                    if ($img.attr('zoom') == 0) {
                        $img.css('cursor', 'url(static/images/cur/big.cur), auto');
                    }
                    else {
                        $img.css('cursor', 'url(static/images/cur/small.cur), auto');
                    }
                    $img.attr('data-click', 'big');
                } else if (e.offsetX < $img.width()) {
                    $img.css('cursor', 'url(static/images/cur/pic_next.cur), auto');
                    $img.attr('data-click','next');
                }
                */
                /* 移动图片需要的 */
                if ($keyisdown && $img.attr('zoom') > 0) {
                    var new_x = e.clientX;
                    var new_y = e.clientY;
                    var new_t = t - (new_y - y);
                    var new_l = l - (new_x - x);
                    $('body').scrollTop(new_t).scrollLeft(new_l);
                }
                return false;
            })
            .dblclick(function (e) {
                return false;
                var $img = $("#picimg");
                var old_x = e.originalEvent.layerX;
                var old_y = e.originalEvent.layerY;
                var old_w = $img.width();
                var old_h = $img.height();
                if ($img.attr('zoom') == 0) changeZoom(1);
                else if ($img.attr('zoom') == 1) changeZoom(2);
                else if ($img.attr('zoom') == 2) changeZoom(0);
                var new_w = $img.width();
                var new_h = $img.height();

                var new_l = old_x / old_w * new_w - document.body.clientWidth / 2;
                var new_t = old_y / old_h * new_h - document.body.clientHeight / 2;
                $('body').scrollLeft(new_l).scrollTop(new_t);
            })
            /* 移动图片需要的 */
            .mousedown(function (e) {
                $keyisdown = true;
                x = e.clientX;
                y = e.clientY;
                l = $(document).scrollLeft();
                t = $(document).scrollTop();
            })
            .mouseup(function () {
                $keyisdown = false;
            });

        $(document).keydown(function (e) {
            if($dis){
                if (e.keyCode == 27) {hidepic();}
                else if (e.keyCode == 37 || e.keyCode == 38) {prev();}
                else if (e.keyCode == 39 || e.keyCode == 40) {next();}
            }
        });


    });
    function showpic(index) {
        var doc = $(document);
        $otop = doc.scrollTop();
        $oleft = doc.scrollLeft();

        $dis = true;
        $("#main").hide();
        $("#menu").show();
        $('body').attr('style', 'background-color:#000;');
        $("#picimg").attr('style', 'display:block;width: 45px; height: 45px;left:45%;top:45%;').attr('src','static/images/loader4.gif');
        setTimeout(function(){$("#picimg").attr('data-index', index).attr('src', $imgs[index]);},100);
        $("#stu").attr('href','http://image.baidu.com/n/pc_search?queryImageUrl=' + encodeURIComponent('https://suncoder.vicp.net' + $imgs[index]));
    }
    function hidepic() {
        $dis = false;
        $("#main").show();
        $("#menu").hide();
        $("#picimg").attr('style', 'display:none;max-width: 100%; max-height: 100%;cursor: url(static/images/cur/small.cur), auto;');

        $('body').scrollTop($otop).scrollLeft($oleft).attr('style', 'background-color:#fff;');;
    }
    function changeZoom(zoom) {
        var $img = $("#picimg");
        $img.attr('zoom', zoom);
        $("#menu").find('.act').removeClass('act');
        $("#menu button").eq(zoom + 1).addClass('act');
        resizeImg();
    }
    function resizeImg() {
        if (!$dis) {return;}
        var $img = $("#picimg");
        var $doc = $(document);
        if ($img.attr('zoom') == 0) {
            $img.attr('style', 'display:block; cursor: url(static/images/cur/big.cur), auto; max-width: 100%; max-height: 100%;');
        }
        else if ($img.attr('zoom') == 1) {
            if ($img.width() > $img.height())// 宽图
                $img.attr('style', 'display:block; cursor: url(static/images/cur/small.cur), auto; width: 100%;');
            else
                $img.attr('style', 'display:block; cursor: url(static/images/cur/small.cur), auto; width: 100%;');
        }
        else if ($img.attr('zoom') == 2) {
            $img.attr('style', 'display:block; cursor: url(static/images/cur/small.cur), auto;');
        }
        if ($doc.width() > $img.width()) {$img.css('left', ($doc.width() - $img.width()) / 2);}
        if ($doc.height() > $img.height()) {$img.css('top', ($doc.height() - $img.height()) / 2);}
        $('body').scrollLeft($doc.width() / 2).scrollTop($doc.height() / 2);

    }
    function next() {
        var $img = $("#picimg");
        var $index = $img.attr('data-index');
        if ($index < $imgs.length - 1) {$index++; } else {$index = 0;}
        showpic($index);
    }
    function prev() {
        var $img = $("#picimg");
        var $index = $img.attr('data-index');
        if ($index > 0) {$index--; } else {$index = $imgs.length - 1;}
        showpic($index);
    }
</script>
</body>
</html>