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
        .menu .btn { display: inline-block; width: 100px; font-size: 22px; background: none; border: none; color: #fff; cursor: pointer; }
        .menu .btn:hover, .menu .btn.act { color: #e85f6b; }
        .circular {
            -webkit-animation: rotate 2s linear infinite;
            animation: rotate 2s linear infinite;
            height: 100%;
            -webkit-transform-origin: center center;
            transform-origin: center center;
            width: 100%;
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            margin: auto;
        }
        .path {
            stroke-dasharray: 1,200;
            stroke-dashoffset: 0;
            -webkit-animation: dash 1.5s ease-in-out infinite,color 6s ease-in-out infinite;
            animation: dash 1.5s ease-in-out infinite,color 6s ease-in-out infinite;
            stroke-linecap: round;
        }
        @keyframes rotate {
            to {-webkit-transform: rotate(1turn);transform: rotate(1turn)}
        }
        @keyframes dash {
            0% {stroke-dasharray: 1,200;stroke-dashoffset: 0}
            50% {stroke-dasharray: 89,200;stroke-dashoffset: -35}
            to {stroke-dasharray: 89,200;stroke-dashoffset: -124}
        }
        @keyframes color {0%,to {    stroke: #d62d20}40% {    stroke: #0057e7}66% {    stroke: #008744}80%,90% {    stroke: #ffa700}}
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
    <!-- <svg viewBox="25 25 50 50" class="circular"><circle cx="50" cy="50" r="20" fill="none" stroke-width="5" stroke-miterlimit="10" class="path"></circle></svg> -->
    <img class="picimg" id="picimg" onload="resizeImg()" src="" style="max-width: 100%; max-height: 100%;" zoom="0"/>
    <div class="menu" id="menu">
        <div>
            <a class="btn" onclick="changeZoom(0);" class="act">全显</a>
            <a class="btn" onclick="changeZoom(1);">满铺</a>
            <a class="btn" onclick="changeZoom(2);">原始</a>
            <a class="btn" onclick="prev();">上一张</a>
            <a class="btn" onclick="next();">下一张</a>
            <a class="btn" id="stu" href="javascript:;" target="_blank">识图</a>
            <a class="btn" onclick="hidepic();">关闭</a>
        </div>
    </div>
</div>

<script type="text/javascript" src="//apps.bdimg.com/libs/jquery/1.10.2/jquery.min.js"></script>
<script type="text/javascript" src="//apps.bdimg.com/libs/bootstrap/3.3.4/js/bootstrap.min.js"></script>
<script type="text/javascript">
    var $imgs = [<?PHP foreach ($files as $f) {echo '"' . BASEURL . 'photos' . $fol . $f . '",' . PHP_EOL;}?>];
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
        $("#picimg").attr({'style':'display:block;width: 45px; height: 45px;left:45%;top:45%;','src':$loaderimg});
        setTimeout(function(){
            $("#picimg").attr({'data-index': index,'src': $imgs[index]});
        },50);
        
        // $("#stu").attr('href','http://image.baidu.com/n/pc_search?queryImageUrl=' + encodeURIComponent(location.origin + $("#picimg").attr('src')));
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
    
    var $loaderimg = 'data:image/gif;base64,R0lGODlhIAAgAOYAAP////f/9//3//f39+/37/fv9+/v7+bv5u/m7+bm5t7m3t7e3tbe1t7W3tbW1s7WztbO1szMzM7FzsXFxb3FvcW9xb29vbW9tb21vbW1ta21ra2t'+
                     'ra2lraWlpZylnKWcpZmZmZmZmZmZmZmZmYyMjISMhISEhHuEe4R7hHt7e3tze3Nzc2tza3Nrc2ZmZmZmZmZmZmZmZmNaY1paWlpSWlJSUlJKUkpKSkJKQkpCSkJCQjpCOkI6Qjo6OjE6MToxOjMzMzE'+
                     'pMSkpKSkhKSEhIRkhGSEZIRkZGRkQGRAQEBAIEAgICAAIAAgACAAAAP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'+
                     'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH/C05FVFNDQVBFMi4wAwEAAAAh+QQJBABPACwOABsABQAFAAAHG'+
                     'oBPCQtPTx9HSSZPRE5ORwY1jT0DC0BEE0+BACH5BAkEAE8ALAIAFgAGAAUAAAcfgE8AICQGT08rS0s3AwBBTk5LCU8mS04zAYIWGwNPgQAh+QQJBABPACwAAA8ABQAFAAAHGoAJLjobTzVOTkgLQohL'+
                     'ERZARCtPTwMJAE+BACH5BAkEAE8ALAAACwAWABUAAAdMgAMmQisDT4eIiYqHJE1OTiCLkokmj04kk5kGM0k1Bpmgn6CjpKWmp6ipqqusra6vsK8ZM5GpEUZOS7WnG0uPLqkGNU1BEaoGGwtPgQAh+QQ'+
                     'JBABPACwAAAcACgAXAAAHSIBPTwsuLAmCiAM3Tk43AIhPBkOMQQOQTyZISCSPkAAQDpeio6SlpqeoqaqrrJcABp2eJkQ0h5AWS4wrlxNJuq4mQDUGowCPgQAh+QQJBABPACwAAAUACAASAAAHQ4BPgg'+
                     'AbIAmCgiZLTj0DiEBOkQ2IJElLN46DGR4GiJ6foKGio6SliAAREwCeJEdJK6tPA0GRRJ1PADORmIgGKSmHgoEAIfkECQQATwAsAAADABoAHAAAB2+AT4KCAAkDg4iJiogDLkQ6C4uSihlLTk4sk5qCE'+
                     '0mXJpubIDouh6GaAKeqq6ytrq+wrg4WqbEgR0k1pq+WlxGxM5dCCbEAJCsPscrLzM3Oz9CIBruvAB5EQhqxBkeXQLWuA0KXPeCuFjo1v8oAtYEAIfkECQQATwAsAAACAA4AHQAAB2eAT4KDABEdCYOJ'+
                     'gxlHTkCIioMzTk5NG5GDHUlLQQuYiyQOn6OkpaMGHA4Aowk6S0IWoyCUTjKjEUNOSR6kESsdq6bCw8TFxsfIycqRAA4JwZgALktEGbdJlDfQiglClC2kEzIpA0+BACH5BAkEAE8ALAAAAQAMABkAAAd'+
                     'kgE+Cg4IABgCEhAk3Qi4DiYIrTk5LGZBPJpNJFpcGLj0kiJcAopeQABEdCaYYRE5Aq5AuTk1OlpAbSU1CsYkAGSgOpsPExcbHyMnIAAmPoyxLQMKQFbVOMpcRSLQpox1ALgaJgQAh+QQJBABPACwAAA'+
                     'EAHAAbAAAHhYBPgoOCABYnE4SKi4yCGUlORhGNlIspTk5NIJWcghFCS0ALnZ0LGQmkigCmBqmVFqA8ra6MM5hOGbSMIJBEo7qqHS6JwMXGx8jJysvFAMwRNS6oyAlAmDPOxw5EmD3ZxgAmREIbygAOC'+
                     'd/MwAARv8kzSUTlyA5LmDvJB0GYK8oRLkwMCAQAIfkECQQATwAsAAAAABEAIAAAB4SAT4KDhAAGAISJhAMpQjIGioodS05OJpGJG5RNJJGGiE8AJkAuA4oDK0I6CYOgihZNlSuYhBNJlSi0rSA9LpC6'+
                     'rcDCw8TFxAksNxmutDqVR6zAR5VLDsIbQUcszLQGC9zG4eLj5OXm508LFqbCGUdLN+y6N5VKE8IklEHRugMsPRmeBAIAIfkECQQATwAsAAAAABAAHQAAB32AT4KDhE8GIBMAhYsDNU5HGYuFFklOTjW'+
                     'KhACZTwlCTkskhQMrPSsDgg4pHpyCIJZLHZKEJLAes4MGM0QyqLiCAAa/w8TFwwm7Jq2SM5ZKFsOOoNC/CzVHKcvG29zd3t/g4AAbIL64AClLS5i/AEGwCcMrljnmswAdIMKLgQAh+QQJBABPACwAAA'+
                     'AAHwAaAAAHpoBPgoOETwAZLhaFi4yNTxZHTkIOjpWMK05OTSCWjgIkMxuCE0RLQgudjSBLTkcRgg8eqKmMLJlNorSWD0JJNwa6nQYPA8HGx4MABsXIjAAkQkATzYsORpk3ANSEC0OZNdrbgxk9NQzih'+
                     'QDh6Ozt7u/w8ccJwIQWJA/IACBFRx3jR5YQoWTMgJBMPZi10OSEk7EBQLCFg+QEyCxjEWrUIGgogix2gQAAIfkECQQATwAsAAAAABQAIAAAB5uAT4KDhIIGLjcPhYuFK0tOPQOMggARHQmCQU5OSQuT'+
                     'TxlHTkCYIElLMwCfM5tNG4IPFqqfHadBCp+MGSQOuZQJs76DAyxCOpjCghabTivJghOPTibPTwAgQC4G1ZTc3t/g4YyVyM8DMkdDFtUZ0jfBvg5CmynVABYzJpLi/P3+//YsKLInY0kSENUWINkUpNq'+
                     'AHk6WOKsW4Ya2QAAh+QQJBABPACwAAAAAEQAfAAAHmYBPgoOEgxNCNQiFgwAGAE8GPU5OLotPCTdCLgAJRpNAliuTSxkAK0lHHZYmk0kWgg4Llk8FLj0kj5YGsoIAuZYTQEQrv7NPN6MOxoMyk0QJy4'+
                     'IJLjql0YzX2drbyw4bBNkeR0k6BtdBk06v0StNTkK8ywAdKxHc9/j5+vvX8yTm11YsWXJjwLUhraBFM7HEyY1sACxsMEgoEAAh+QQJBABPACwAAAAAEgAdAAAHnYBPgoOEgwAmSywAhYQWFosTR05JH'+
                     'YxPACtLiU8eTp4plgNBnklPBjdOPQ6WADSaNYsDEQaEAAm0TwMkJLiMACRDQBmWjAlEnjqLxIMGQJ6wy403MwvRhQDK1trb0QAbJArcLkqpvdFLnk4R2ytHSzjmywAWIPHc9/j5+vu++Bk1KwZsW3DM'+
                     'yYpsyyYkeYaQ2AAXR3pM4AbAgT1CgQAAIfkECQQATwAsAAAAABMAGgAAB52AT4KDhIQDNUsghYQAEy4gAE8mS05EDIuCDkJOSyRPM05OShaYTxmUTi5PE0JLMwOMBpEDN0tEE4IGDpGDAytCOglPAxM'+
                     'LpYIWTaErx4UTSaEozYwgPS4G04W82dzaGy4b29MelEmk3C6hTp7cDkBJPcbdBhbY3ff4+frcAOLZDjeAdPBXCkCNUEeEZQOgA6HCbBF6BFF0DwAsboEAACH5BAkEAE8ALAAAAAAUABgAAAeUgE+Cg4'+
                     'QAAIMLPT0OhI1PBi49JIc3Tk43h46CKZZIE09HlkQDjYaCK5ZJFk8kSUkmjQMrPSsDCTdCKZkMjI0glksdTwAGmo0kwB7GmgYzRDKky47E0tXWABk6LgnWgw9Eli6Z1hRJlpjdwylHQp/pggnR7/P09'+
                     'dXj6QkzOhv41S6WjHBLZ8OSkgXvIgDJ5a+arXmBAAAh+QQJBABPACwAAAAAFAAWAAAHmoBPgoOEAA4OAIIWPSwDhI9PHkREIE8JQU5LJJCDAEJOTklPE0qgN48AGxkAAD2gQE8DN0tHGYUsTk0sTwwy'+
                     'Mw+CAxYLjwOfTkQET6ychTNLSzOJzZADICSO1J3T2o8OPEEe3N0ANqBHCd2dN+fp6oIOPUTi753Z9fj5+uoAKUIu99SBaAJqUz0SoJwYfGfAhZAaBvIBMDBOXSAAIfkECQQATwAsAAAAABUAEwAAB5K'+
                     'AT4KDhE8GIBMATwAbLhaKhYQDNU5HGU8TRE5CEZGLghZJTk41TyajTiSFACQ9KwMJQk5LqhFCS0IOhRlKsyBPDikeigAOHQuRHagmns1PAzJJNQnOhAAJxNjVgwMsQjrU24QWqCvihBNLo8znggAgQC'+
                     '4G7db09s0A8/cTQEcp90NGLdFFr8eoJMjoLZgBpMO9QAAh+QQJBABPACwAAAAAFgASAAAHj4BPgoOETwAZLhaChyQGhY9PFkdOQg5PIElOMwOPAA4TAE8rTk5NIE8/pEeWhR5HSzJPE0RLQgtPKUtOO'+
                     'pyFQqRJCU8PHreGICXCjy66P46Qjwa3AyYsDNCPE0BEK9jYN6RLrN6FMqREyuSECS46GqHqnfHz8Qug9IMgR0eb+E+6pB74A+cEnT9qLiL4exIIACH5BAkEAE8ALAAAAAAXABEAAAeSgE+Cg4SCBi43'+
                     'D4MBCwCFj4IrS049A08LNkIpjo8TMiaWQU5OSQtPLqNJFo8LQ05LKU8gSUszjiujRxGdSaM1jg8WnIc6IJyEADNLRBmQgseCGzUkAAATCtDOghG9SyDa2hujTizgzgY3SUIO5s4DDwbt8vNPABYkpvS'+
                     'DJkdOQPH6nhAZtyogvyVAEASsdy9fwEAAIfkECQQATwAsAAAAABgAEAAAB5WAT4KDhIMTQjUIhYuEAAYATwY9Tk4uggAbKxaQiwk3Qi4ACUaUQIIbSU5HE4wrlEsZACtJRx2CKpROIIwmlEkWgg4Lgw'+
                     '9CS0AJgwATE44uPSScnRYGyiZJSSoA24zdTwNAlEfV3t0ANJQ1A+XeBikpyezy840bLhvS9IIeS06/+oRc5CIBcJADIEl6DCsoyAA1hoICAQAh+QQJBABPACwAAAAAGQAPAAAHj4BPgoOEgwAmSywAh'+
                     'YyFFhaLE0dOSR2GC4uNACtLiU8fTqEpggY3RDUHjQNBoUlPpU49DoIgS6EkhZkznTWLAxEGgxu2S5aCAylCJgADJCTBmiY4y4MeocWNmoUkoU4g2eCDBjVLNdDh4QmZ6LkZOi7n7IUPRKEu6/KDFEmh'+
                     'N/j5TwCkOCJkAsBGCQYcLBQIACH5BAkEAE8ALAAAAAAaAA4AAAeSgE+Cg4SFG0Q1BoWLhAATLiAATwlCTksrjJkOlUskTxFHTk45gxEkEZKMGUuiLk8ALktEFoIORE5GtIMDGxkAAzeyE4MOCYMgTaI'+
                     'pg7BOTSu/EwuZT5tLRsOCBpVOQgPUhA4gEY01S0suqeDUAyQg3+uMAyDv8dQAMq3q9oTbot78FgFgkSQJi30BBQHgsAFhwkAAIfkECQQATwAsAAAAABwADgAAB46AT4KDhAAAhAk1MwmEjY6CBi49JI'+
                     'dPNU5OM4+bgimYSBOCS5hJnIWVK6QWgitJSSmEBw6VgwMrPSsDCTdCKZUAFqGDEz1HLgOEIJhLHU8ABKYAl05JwoIkyx6mgwCpTkQLhAYzRDLI24K2NRmDCbMABuiOtBNCxrTyj9NNEfmcnt/C+Xukb'+
                     'tVATvgODgoEACH5BAkEAE8ALAAAAAAdAA0AAAeWgE+Cg4QADg4AgwAZIAaEj5CDHkREIIMgSU4zA5GdTwBCTk5JnABAokcOnoobGQAAPaJAnE8pS046tJ4ALE5NLE8MMjMPiiAlCauCA6FORASficoO'+
                     'KSDSggAzS0sz18oJp00kjwMgJLrKTxOiuJ8kQCne6cs3S0cbTxm3S5bz5BkRBHVgN86fJwM1ktxIZnBVAnkNBwUCACH5BAkEAE8ALAAAAAAfAAwAAAeXgE+Cg4RPAB4rCYQBCwCFj5CCIEtONwOCCzZ'+
                     'CKY6Rj51PPU5OSQ+CLqNJFp6EACQ9K5ckSU09l08ro0cRrIMZSk5LIIIbIAeDBi46IKCsHaNOJqzNvQMySTWKvb0HIBsAAAnU2w81NQ6CAzXBKduQA0CjN44LR6M649sGQ6O2TwM2liQxka8XABBHjn'+
                     'RA5iFDQXcJDAgKBAAh+QQJBABPACwCAAAAHgAPAAAHloBPgoOEAClAHoSKi4yEHUtORA6ECREAjYUOE5dPLk5OSx2DE0BJMgOYgh5HSzKXE0JLOQaCADOfSxapT0KfSQmCCRa0tSaQQpOpLpA/BZgDJ'+
                     'jUWnIsGC0/QLAy7T9SLpEQr3ONPN7jJ5Jgyn0TA6ZgJLjoa3u/dBtT19gY1Qi7E9haV+OSEREBGJAiCOLhogAshKQAEAgAh+QQJBABPACwCAAAAHgAUAAAHmoBPgoOETwAmSywAhYyNjRNHTkkdjpWC'+
                     'EzImA4IgTp4phQYJlk8LQ05LoE8GN049DoQWPUIki44TSZ41tgMRBoQArU5EC5UAM0tEGZbHnkGjhRs1tQATCraVCzM6y4URuUsgpIzYhBueTizj66xJQrDr4wMPv/H29/j5+vv8/ZUgRGrA8+cglxM'+
                     'g/gQV9IQw4RMSRHQMTDhgUSAAIfkECQQATwAsAgAAABwAGwAAB52AT4KDhIMbRDUGhYuLABMLgwlCTk0rjJdPAzNLRBuCD0dOTjmYjBFIojcATwAunBali5JOS5aDDgmLES4mA4OOE6sRMr2xs062AC'+
                     'ZJSSursbdLojqCA0CiR4rQgzNJnYIANKI1vtvgj4QGKSm55u7v8PHy8/T19vf4+fr7/P2MCSlQaKM3oMa4Z/MMhHICpNw8ACqYmUD4cEKwJ4EAACH5BAkEAE8ALAMAAAAcACAAAAemgE+Cg4SEAyQgA'+
                     'IWLhREzLAmEM05LKYyXBkBOTjWKTwZJm0eXjAlDm0GeADVOTS6kg6omSUYbhAUgIAOwAylCJrtPC5Gwlx6bS7bFxSSbTiDLjBMbBJ81SzUG0YUgSEwzuwAJntuCADabSg7ljACzTjra7IsAHikK8/n6'+
                     '+/z9/v8AAwocSLCgwYMILyVYAPDDkSQm/hERJY8fKyc9gvFbAITIhGiBAAAh+QQJBABPACwCAAAAHgAbAAAHrIBPgoOCAA4OAISFiYqNjR5ERCCKDjMzDo6ZTwBCTk5JA4MDQJ49jJqEAD2eQKGCA0e'+
                     'eQ66ZAxsZjA4yMw+KH5GTmgAuTk0rjACnhAkJqE8GnU5CtM6oADRLSy7K1ZoDJCDU3ePk5ebn3RMmFtzoTxFETkcd7ooknk4y9YQLQkpDFvbx67BAoMGDCBMqXAgABAkDCFdkuyEOHYAgnpY0M2hiiZ'+
                     'MZFS1e2BDyXCAAIfkECQQATwAsAAAAAB8AGAAAB6aAT4KDhE8AHisJhYuMjU8gS043A4sAAI6OPU5OSQ+FFjo1EZiGEw6DJElNPZSDA0KbPZeNAC5JRBuDGiAHhQZHm0CzjA5ImzfDjAAfREIZmAmaT'+
                     'i7JjQYEpE8RMymt2d+G1eDj5OXm5+jp6uvnCS46ueyCNZtHC/JPsE5Lo/IWQERasBs2IIE4cgAscPO2LgGRTSnwRdjkpAa+AS6OCJmAz5ADRYEAACH5BAkEAE8ALAAAAAAdACAAAAergE+Cg4SDAzNL'+
                     'IACFjI2MJEtORA6OlU8Di4MuTk5LGZaMACZBOpSCEUJOMwOghQ9HnDOZTwYPs4UTFreCDrBOMruOIElLLsEgQDULrU8AOpxGy4wBwY4ALpE9rMyWAyYr0tzi463eQivb5IUkTZwg6owmnE4k8IUGM0k'+
                     '1BvaM/P0AAwocSLCgwYMIEypcCCrDjHcCHxjpBBFgh0hOXAg0UMNJkAgDDWwIVygQACH5BAkEAE8ALAAAAAAfAB4AAAeogE+Cg4SFACREJACFjI2OFkdORxaOlU8TMiYDhCROniSWjQlDTksphAs9S0'+
                     'ALoYwTSZ41i4MGEwaujAMzS0QZucAAEwq0wIwLLiwJxo0AtAM3njfFzAYzQCqbBqROQpvMgiaeTr9PJkhIiuDh4+UAEA7rtTNHK9TyhAAF+Pz9/v8AAwocSLCgwYOhABi41w+ACSI0lgG0sMTTioCwL'+
                     'AZ0CKQGLoHO+gUCACH5BAkEAE8ALAAAAAAgABcAAAehgE+Cg4SFgwYbC4aLhgATioUGNU1BEYyXAzNLRBuFHU1OTiyXjBFIoTcAhBFETksgpIsJQq4rhQAZNLCxggAbIAlPETImA7yxJktOPcbHx0Ch'+
                     'TpDOsSRJSzfN1KS4Hgbb4OHi4+Tl5ufojAARwelPBDVHQhjuFtFAT+sTquQOR6EynpA4kmQFv3EZZpAYMCBIKCLf0AGYgUrbuQMpUrRDFwgAIfkECQQATwAsAAAAABoAHwAAB6qAT4KDhAALBoSJios'+
                     'AJkQ9EYuSig5HTk43AIoAHi4TmoUJA08LQpcyoIQgS05DDoQDLkQ6CwAbOjMJmzKXThuEGaxOLIIAqYkTREs8iIMTSZcmk4QLFs2qOi6j09xPx93g4eLj5OXm54IOFt/kIEdJNdvmwk6R5zOXQrrnAC'+
                     'QrD+gCChxIsKDBg4QMyDPHiYiQX+cMWHIChF24Aaac9LAYzoKOGvbQGRMUCAAh+QQJBABPACwAAAAAHQAfAAAHqoBPgoOEggszLAaFi4yLAzdOTSyNlIIAER0JTwlHTk5ClZQZnUAJADJLSSShjTOeT'+
                     'RtPABkTAJQAA7aLHUlLQQqsTwYyQCsDjBkkDsFPJJ5OscyUJE2e0dKMBjNExtiVuYIGHQ663oUJOktCFuaLIM8y7YURQ05JHvLzKx3l+f7/AAMKHEiwoMGDCA8CcGAKIAAXS4hkABghiacb/dolEOKp'+
                     'RcAJMlIckxYIACH5BAkEAE8ALAAAAAAgABoAAAemgE+Cg4SEC0BEE4WLjIQABgCDNU5OPQONmIMJN0Iul09ElEcGmZkrlEsZgh5HSSalmSaUSRaaC7CZBi49JJG4vwC+v8PEjQARHQnFpRihQMrLjS5'+
                     'OTU6q0YwbSU1C0NiFABkoDt+CIDorpOWEEUmUM+uEFpROOvGDAy5EQBH3gwAJPvkbSLAgNoAC7wFgsQQIOX8VqjmRMTACEmopBgLoAMSFunWBAAAh+QQJBABPACwAAAAAHAAcAAAHooBPgoOEhYQLCw'+
                     'CGi08AFicThhlCRCCKjIQZSU5GEYQAQE5OQgaYhCmiTSCfN6JAA6aDEUJLQAuFCzM3kbGDCxkJiwCXwr+lvbEWtDzHyIwzok4Zzpggm0S31MIdLrza3+Dh4uPk5eamxOURNS7B5AmhTjPp4A5Eoj303'+
                     'wAmREIb5gA4SKDvnDYAEbKVm5GECEByDpbgK3cgiKgV5iK4MDEgEAAh+QQJBABPACwAAAAAGgAgAAAHp4BPgoOEAAYAhImKiwMpQjIGi5KKHUtOTiaSBRkPiJIblk0kjDlOSBuFh4IAJkAuA4sRSpc6'+
                     'gwMrQjoJg56LCUBNS5mCFk2XK5OJDikevRNJlyjJ0wAgPS6R09Ta3N3e3+Dh4gksNxm94TqXR7viT0eXSw7uTxtBRyzo4QYL+vT/AAMKHEiwoEFuCyzAopfhyJIbC8XduKRkAj0SloK0EzeARY8MyQI'+
                     'BACH5BAkEAE8ALAAAAAAfAB0AAAemgE+Cg4RPBiATAIWLjI0DNU5HGY2UlBZJTk41ipWUAJxPCUJOSySdlAMrPSsDgg4pHqCnhSCZSx2zsyS2HrmnBjNEMq2+nQAGxYQDFguyyYUDN0lCFs+NFplOM9'+
                     'aMCkSkIAnBJs7JEy4gADOZStXcgpyQpO7vgws1Rynl9fz9/v8AAwocSLARgA0giPUDkGLJkk3+AASxleDfikw5FPID0AEEsneBAAAh+QQJBABPACwAAAAAHwAaAAAHpoBPgoOETwAZLhaFi4yNTxZHT'+
                     'kIOjpWMK05OTSCWjgIkMxuCE0RLQgudjSBLTkcRgg8eqKmMLJlNorSWD0JJNwa6nQYPA8HGx4MABsXIjAAkQkATzYsORpk3ANSEC0OZNdrbgxk9NQzihQDh6Ozt7u/w8ccJwIQWJA/IACBFRx3jR5YQ'+
                     'oWTMgJBMPZi10OSEk7EBQLCFg+QEyCxjEWrUIGgogix2gQAAIfkECQQATwAsAAAAABQAIAAAB5uAT4KDhIIGLjcPhYuFK0tOPQOMggARHQmCQU5OSQuTTxlHTkCYIElLMwCfM5tNG4IPFqqfHadBCp+'+
                     'MGSQOuZQJs76DAyxCOpjCghabTivJghOPTibPTwAgQC4G1ZTc3t/g4YyVyM8DMkdDFtUZ0jfBvg5CmynVABYzJpLi/P3+//YsKLInY0kSENUWINkUpNqAHk6WOKsW4Ya2QAAh+QQJBABPACwAAAAAEQ'+
                     'AfAAAHmYBPgoOEgxNCNQiFgwAGAE8GPU5OLotPCTdCLgAJRpNAliuTSxkAK0lHHZYmk0kWgg4Llk8FLj0kj5YGsoIAuZYTQEQrv7NPN6MOxoMyk0QJy4IJLjql0YzX2drbyw4bBNkeR0k6BtdBk06v0'+
                     'StNTkK8ywAdKxHc9/j5+vvX8yTm11YsWXJjwLUhraBFM7HEyY1sACxsMEgoEAAh+QQJBABPACwAAAAAEgAdAAAHnYBPgoOEgwAmSywAhYQWFosTR05JHYxPACtLiU8eTp4plgNBnklPBjdOPQ6WADSa'+
                     'NYsDEQaEAAm0TwMkJLiMACRDQBmWjAlEnjqLxIMGQJ6wy403MwvRhQDK1trb0QAbJArcLkqpvdFLnk4R2ytHSzjmywAWIPHc9/j5+vu++Bk1KwZsW3DMyYpsyyYkeYaQ2AAXR3pM4AbAgT1CgQAAIfk'+
                     'ECQQATwAsAAAAABMAGgAAB52AT4KDhIQDNUsghYQAEy4gAE8mS05EDIuCDkJOSyRPM05OShaYTxmUTi5PE0JLMwOMBpEDN0tEE4IGDpGDAytCOglPAxMLpYIWTaErx4UTSaEozYwgPS4G04W82dzaGy'+
                     '4b29MelEmk3C6hTp7cDkBJPcbdBhbY3ff4+frcAOLZDjeAdPBXCkCNUEeEZQOgA6HCbBF6BFF0DwAsboEAACH5BAkEAE8ALAAAAAAUABgAAAeUgE+Cg4QAAIMLPT0OhI1PBi49JIc3Tk43h46CKZZIE'+
                     '09HlkQDjYaCK5ZJFk8kSUkmjQMrPSsDCTdCKZkMjI0glksdTwAGmo0kwB7GmgYzRDKky47E0tXWABk6LgnWgw9Eli6Z1hRJlpjdwylHQp/pggnR7/P09dXj6QkzOhv41S6WjHBLZ8OSkgXvIgDJ5a+a'+
                     'rXmBAAAh+QQJBABPACwAAAAAFAAWAAAHmoBPgoOEAA4OAIIWPSwDhI9PHkREIE8JQU5LJJCDAEJOTklPE0qgN48AGxkAAD2gQE8DN0tHGYUsTk0sTwwyMw+CAxYLjwOfTkQET6ychTNLSzOJzZADICS'+
                     'O1J3T2o8OPEEe3N0ANqBHCd2dN+fp6oIOPUTi753Z9fj5+uoAKUIu99SBaAJqUz0SoJwYfGfAhZAaBvIBMDBOXSAAIfkECQQATwAsAAAAABUAEwAAB5KAT4KDhE8GIBMATwAbLhaKhYQDNU5HGU8TRE'+
                     '5CEZGLghZJTk41TyajTiSFACQ9KwMJQk5LqhFCS0IOhRlKsyBPDikeigAOHQuRHagmns1PAzJJNQnOhAAJxNjVgwMsQjrU24QWqCvihBNLo8znggAgQC4G7db09s0A8/cTQEcp90NGLdFFr8eoJMjoL'+
                     'ZgBpMO9QAAh+QQJBABPACwAAAAAFgASAAAHj4BPgoOETwAZLhaChyQGhY9PFkdOQg5PIElOMwOPAA4TAE8rTk5NIE8/pEeWhR5HSzJPE0RLQgtPKUtOOpyFQqRJCU8PHreGICXCjy66P46Qjwa3AyYs'+
                     'DNCPE0BEK9jYN6RLrN6FMqREyuSECS46GqHqnfHz8Qug9IMgR0eb+E+6pB74A+cEnT9qLiL4exIIACH5BAkEAE8ALAAAAAAXABEAAAeSgE+Cg4SCBi43D4MBCwCFj4IrS049A08LNkIpjo8TMiaWQU5'+
                     'OSQtPLqNJFo8LQ05LKU8gSUszjiujRxGdSaM1jg8WnIc6IJyEADNLRBmQgseCGzUkAAATCtDOghG9SyDa2hujTizgzgY3SUIO5s4DDwbt8vNPABYkpvSDJkdOQPH6nhAZtyogvyVAEASsdy9fwEAAIf'+
                     'kECQQATwAsAAAAABgAEAAAB5WAT4KDhIMTQjUIhYuEAAYATwY9Tk4uggAbKxaQiwk3Qi4ACUaUQIIbSU5HE4wrlEsZACtJRx2CKpROIIwmlEkWgg4Lgw9CS0AJgwATE44uPSScnRYGyiZJSSoA24zdT'+
                     'wNAlEfV3t0ANJQ1A+XeBikpyezy840bLhvS9IIeS06/+oRc5CIBcJADIEl6DCsoyAA1hoICAQAh+QQJBABPACwAAAAAGQAPAAAHj4BPgoOEgwAmSywAhYyFFhaLE0dOSR2GC4uNACtLiU8fTqEpggY3'+
                     'RDUHjQNBoUlPpU49DoIgS6EkhZkznTWLAxEGgxu2S5aCAylCJgADJCTBmiY4y4MeocWNmoUkoU4g2eCDBjVLNdDh4QmZ6LkZOi7n7IUPRKEu6/KDFEmhN/j5TwCkOCJkAsBGCQYcLBQIACH5BAkEAE8'+
                     'ALAAAAAAaAA4AAAeSgE+Cg4SFG0Q1BoWLhAATLiAATwlCTksrjJkOlUskTxFHTk45gxEkEZKMGUuiLk8ALktEFoIORE5GtIMDGxkAAzeyE4MOCYMgTaIpg7BOTSu/EwuZT5tLRsOCBpVOQgPUhA4gEY'+
                     '01S0suqeDUAyQg3+uMAyDv8dQAMq3q9oTbot78FgFgkSQJi30BBQHgsAFhwkAAIfkECQQATwAsAAAAABwADgAAB46AT4KDhAAAhAk1MwmEjY6CBi49JIdPNU5OM4+bgimYSBOCS5hJnIWVK6QWgitJS'+
                     'SmEBw6VgwMrPSsDCTdCKZUAFqGDEz1HLgOEIJhLHU8ABKYAl05JwoIkyx6mgwCpTkQLhAYzRDLI24K2NRmDCbMABuiOtBNCxrTyj9NNEfmcnt/C+XukbtVATvgODgoEACH5BAkEAE8ALAAAAAAdAA0A'+
                     'AAeWgE+Cg4QADg4AgwAZIAaEj5CDHkREIIMgSU4zA5GdTwBCTk5JnABAokcOnoobGQAAPaJAnE8pS046tJ4ALE5NLE8MMjMPiiAlCauCA6FORASficoOKSDSggAzS0sz18oJp00kjwMgJLrKTxOiuJ8'+
                     'kQCne6cs3S0cbTxm3S5bz5BkRBHVgN86fJwM1ktxIZnBVAnkNBwUCACH5BAkEAE8ALAAAAAAfAAwAAAeXgE+Cg4RPAB4rCYQBCwCFj5CCIEtONwOCCzZCKY6Rj51PPU5OSQ+CLqNJFp6EACQ9K5ckSU'+
                     '09l08ro0cRrIMZSk5LIIIbIAeDBi46IKCsHaNOJqzNvQMySTWKvb0HIBsAAAnU2w81NQ6CAzXBKduQA0CjN44LR6M649sGQ6O2TwM2liQxka8XABBHjnRA5iFDQXcJDAgKBAAh+QQJBABPACwCAAAAH'+
                     'gAPAAAHloBPgoOEAClAHoSKi4yEHUtORA6ECREAjYUOE5dPLk5OSx2DE0BJMgOYgh5HSzKXE0JLOQaCADOfSxapT0KfSQmCCRa0tSaQQpOpLpA/BZgDJjUWnIsGC0/QLAy7T9SLpEQr3ONPN7jJ5Jgy'+
                     'n0TA6ZgJLjoa3u/dBtT19gY1Qi7E9haV+OSEREBGJAiCOLhogAshKQAEAgAh+QQJBABPACwCAAAAHgAUAAAHmoBPgoOETwAmSywAhYyNjRNHTkkdjpWCEzImA4IgTp4phQYJlk8LQ05LoE8GN049DoQ'+
                     'WPUIki44TSZ41tgMRBoQArU5EC5UAM0tEGZbHnkGjhRs1tQATCraVCzM6y4URuUsgpIzYhBueTizj66xJQrDr4wMPv/H29/j5+vv8/ZUgRGrA8+cglxMg/gQV9IQw4RMSRHQMTDhgUSAAIfkECQQATw'+
                     'AsAgAAABwAGwAAB52AT4KDhIMbRDUGhYuLABMLgwlCTk0rjJdPAzNLRBuCD0dOTjmYjBFIojcATwAunBali5JOS5aDDgmLES4mA4OOE6sRMr2xs062ACZJSSursbdLojqCA0CiR4rQgzNJnYIANKI1v'+
                     'tvgj4QGKSm55u7v8PHy8/T19vf4+fr7/P2MCSlQaKM3oMa4Z/MMhHICpNw8ACqYmUD4cEKwJ4EAACH5BAkEAE8ALAMAAAAcACAAAAemgE+Cg4SEAyQgAIWLhREzLAmEM05LKYyXBkBOTjWKTwZJm0eX'+
                     'jAlDm0GeADVOTS6kg6omSUYbhAUgIAOwAylCJrtPC5Gwlx6bS7bFxSSbTiDLjBMbBJ81SzUG0YUgSEwzuwAJntuCADabSg7ljACzTjra7IsAHikK8/n6+/z9/v8AAwocSLCgwYMILyVYAPDDkSQm/hE'+
                     'RJY8fKyc9gvFbAITIhGiBAAAh+QQJBABPACwDAAAAHQAQAAAHhoBPgoNPAA4OAISCAImKjooeREQgig4zMw6PmgBCTk5JA4MDQJ49jZqEAD2eQKGCA0eeQ66PAxsZjQ4yMw+KH5KUmy5OTSuNjI8JCa'+
                     'hPBp1OQrTMzAA0S0sup9PMAyQg0tvh4uPk5eQTJhba5oIRRE5HHeyKJJ5OMvOEC0JKQxb5+joseBIIACH5BAkEAE8ALAQAAAAbABgAAAeMgE+Cg08AHisJhIqLjE8gS043A4sAAI2LPU5OSQ+KFjo1E'+
                     'YwAEw6DJElNPZODA0KaPZaEAC5JRBuDGiAHigZHmkCygw5ImjfCox9EQhmLCZlOLsiNBgSNETMprJfcg5Xd4OHi4+Tl5ufo6err7O3o0+cAFtnb5wlEminqEZpONeoDXBwRMmEdAAeJAgEAIfkECQQA'+
                     'TwAsBQAAABgAIAAAB42AT4KDggMzSyAAhIuMhCRLTkQOjYwDioMuTk5LGZSDACZBOpOCEUJOMwOegg9HmjOXTwYPsYsTFooOrk4ytZ4gSUsuiiBANQurnzqaRshPAb6eAC6QParJjQMmK87Y3t/g4eL'+
                     'j5OXm5+jp6uvs7e7v8N8ZMyDlD0ab9eMdkE4u5AZqOAkSoZyBDd0WBQIAIfkECQQATwAsBgAAABkADQAAB2WAT4KDgwAkRCQAhIuMixZHTkcWjYsTMiYDhCROnCSUgwlDTksphAs9S0ALn4ITSZw1io'+
                     'MGEwasggMzS0QZt6wAEwqyvsTFTwDDxrMzQCqZyoMmnE690ILSnNXWBjNHK8nQAAWCgQAh+QQJBABPACwHAAAAGQAUAAAHaIBPgoOETwYbC4WKTwATiYUGNU1BEYuDAzNLRBuFHU1OTiyWghFIoDcAh'+
                     'BFETksgo08JQq0rhQAZNK+wTxEyJgO7wcLDxMXGx8jJysvMzcoAEQnKBDVHQhjJFqBOQMkOR6AuyhkzJAOBACH5BAkEAE8ALAkAAAARAB8AAAdugE8ACwZPhoeIhwAmRD0RiZBPDkdOTjcAkAAeLhOD'+
                     'QpUymIkgS05DDgAbOjMJmTKVThuGAKKQE0RLPIWRiQsWu7zBwsPExcbHyMnKy8zNzs/Q0c0GA8WaREKywwaUTkC1vAOfTj3gvBY6NY/WooEAIfkECQQATwAsCwAAABIACgAAB0SATwszLAZPh4iJiAM'+
                     '3Tk0sipGHCUdOTkKSkQAyS0kkmZoZEwCgAAOkoIkGMkArA6mIJJZOG7CHJE2WtbYGM0SutoukgQAh+QQJBABPACwOAAAAEgARAAAHRYALQEQTT4aHiIg1Tk49A4mQhkSMRwaRkB5HSSaXkQkLnaGio6'+
                     'SlpqeoqapPIDorlqMRSYwzpBaMTjqkAy5EQBGlAAmPgQAh+QQJBABPACwRAAAACwAcAAAHTIBPTwsLAIKHghlCRCCGiABATk5CCIhPADeSQAOWgzM3E52CAI6ipqeoqaqrrK2ur7CxsqgAEQumM0lEG'+
                     '50OS5I9nQdBkiuiES4mA4EAIfkECQQATwAsFQACAAUABQAABxuATwUZDwADOU5IGxFKTk45CUBOSyZPDikeAIEAIfkECQQATwAsGgAIAAUABgAABx6ATwMWCwADN0lCFhZOjTMKRE5LIE8TLiAAT0+Z'+
                     'T4EAIfkECQQATwAsGQAUAAYABQAABx6AT08JBoIAIEVHHU8GQk5OPQMDQI83AE8RNTQOT4EAOw==';
</script>
</body>
</html>