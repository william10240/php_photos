<?php
/**
 * Created by PhpStorm.
 * User: haibin
 * Date: 2017/2/28
 * Time: 17:31
 */
require_once 'common.php';

if (empty($_REQUEST['f'])) { exit; }


$imagePath = PHOTOPATH . $_REQUEST['f'];
if(!isImg($imagePath)){header("Content-type: text/xml");
echo '
<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 16 16" style="enable-background:new 0 0 16 16;" xml:space="preserve">
	<g>
		<path style="fill-rule:evenodd;clip-rule:evenodd;" d="M14,0H2C0.895,0,0,0.895,0,2v12c0,1.105,0.895,2,2,2h12
			c1.105,0,2-0.895,2-2V2C16,0.895,15.105,0,14,0z M14,13c0,0.552-0.448,1-1,1H3c-0.552,0-1-0.448-1-1V3c0-0.552,0.448-1,1-1h10
			c0.552,0,1,0.448,1,1V13z M5.5,7C6.328,7,7,6.328,7,5.5S6.328,4,5.5,4S4,4.672,4,5.5S4.672,7,5.5,7z M7,11L6,9l-2,3h8l-2-6L7,11z"
			/>
	</g>
</svg>';
exit;}

if (!file_exists($imagePath) || !is_file($imagePath)) { exit; }
$finfo = finfo_open(FILEINFO_MIME);
$mimetype = finfo_file($finfo, $imagePath);
finfo_close($finfo);


header("Cache-control: max-age:36000");
header("Content-type: " . $mimetype);

$final_image = get4cache($imagePath);
if($final_image){header('X-mem-cache: HIT');echo $final_image;exit;}

header('X-mem-cache: MISS');
$final_image = resizeImage($imagePath);
save2cache($imagePath, $final_image);
echo $final_image;
exit;

function resizeImage($imagePath, $thumb = null, $width = 200, $height = 200) {
    list($imageWidth, $imageHeight) = getimagesize($imagePath);
    $finf = pathinfo($imagePath);
    $imagesrc = null;
    switch ($finf['extension']) {
        case 'jpg':
            $imagesrc = imagecreatefromjpeg($imagePath);
            break;
        case 'gif':
            $imagesrc = imagecreatefromgif($imagePath);
            break;
        case 'png':
            $imagesrc = imagecreatefrompng($imagePath);
            break;
        case 'bmp':
            $imagesrc = imagecreatefromwbmp($imagePath);
            break;
        default:
            $imagesrc = imagecreatefromjpeg($imagePath);
            break;
    }
    $_zw = 0;
    $_zh=0;
    if($imageWidth/$width > $imageHeight/$height){
        $zh = $height;
        $zw = $zh*($imageWidth/$imageHeight);
        $_zw = ($zw-$width)/2;
    }else{
        $zw = $width;
        $zh = $zw*($imageHeight/$imageWidth);
        $_zh = ($zh-$height)/2;
    }
    $zimg = imagecreatetruecolor($zw, $zh);
    // 先把图像放满区域
    imagecopyresampled($zimg, $imagesrc, 0,0, 0,0, $zw,$zh, $imageWidth,$imageHeight);

    // 再截取到指定的宽高度
    $image = imagecreatetruecolor($width, $height);
    imagecopyresampled($image, $zimg, 0,0, 0+$_zw,0+$_zh, $width,$height, $zw-$_zw*2,$zh-$_zh*2);

    ob_start();
    imagejpeg($image, $thumb, 60);
    $final_image = ob_get_contents();
    ob_end_clean();
    imagedestroy($image);
    return $final_image;
}

function get4cache($imagePath) {
    $imagePath=md5($imagePath);
    $memcache_obj = new Redis();
    $memcache_obj->connect('rds', 6379);
    $res =  $memcache_obj->get($imagePath);
    $memcache_obj->close();
    return $res ;
}
function save2cache($imagePath, $final_image) {
    $imagePath=md5($imagePath);
    $memcache_obj = new Redis();
    $memcache_obj->connect('rds', 6379);
    $memcache_obj->set($imagePath, $final_image);
    $memcache_obj->close();
}








