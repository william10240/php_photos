<?php
/**
 * Created by PhpStorm.
 * User: haibin
 * Date: 2017/3/24
 * Time: 16:53
 */

// url path
define('BASEURL','/');

// local path @ linuc
#define('PHOTOPATH', '/mnt/XiaoMi/Zero/Photos');
#define('PHOTOTEMPPATH', '/mnt/XiaoMi/Zero/Photos/tmb/');

// local path @windows
define('PHOTOPATH', '/data');
define('PHOTOTEMPPATH', PHOTOPATH.'/tmp');


function isImg($fname) {
    if (!$fname) {return false;}
    if(!file_exists($fname)){return false;}

    $fnameinfo = pathinfo($fname);
    $fext = $fnameinfo['extension'];
    if (in_array($fext, array('jpg', 'jpeg', 'png', 'bmp', 'gif', 'webp'))) {
        return $fext;
    } else {
        return false;
    }
}