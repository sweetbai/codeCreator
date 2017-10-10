<?php
namespace commons\framework;
use commons\framework\ext\image\GdDriver;

/**
 * User: sweetbai
 * Date: 2017/3/15
 * Time: 10:27
 */
class ImageTool extends GdDriver
{
    public function __construct($img)
    {
        parent::__construct($img);
    }
    public function buildImageVerify($width=48,$height=22,$randval=NULL,$verifyName='verify') {
        if( !isset($_SESSION) ) {
            session_start();//如果没有开启，session，则开启session
        }
        $randval =empty($randval)? ("".rand(1000,9999)):$randval;
        $_SESSION[$verifyName]= $randval;
        $length=4;
        $width = ($length*10+10)>$width?$length*10+10:$width;
        $im = imagecreate($width,$height);
        $r = array(225,255,255,223);
        $g = array(225,236,237,255);
        $b = array(225,236,166,125);
        $key = mt_rand(0,3);

        $backColor = imagecolorallocate($im, $r[$key],$g[$key],$b[$key]);    //背景色（随机）
        $borderColor = imagecolorallocate($im, 100, 100, 100);                    //边框色
        $pointColor = imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));                 //点颜色

        @imagefilledrectangle($im, 0, 0, $width - 1, $height - 1, $backColor);
        @imagerectangle($im, 0, 0, $width-1, $height-1, $borderColor);
        $stringColor = imagecolorallocate($im,mt_rand(0,200),mt_rand(0,120),mt_rand(0,120));
        // 干扰
        for($i=0;$i<10;$i++){
            $fontcolor=imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
            imagearc($im,mt_rand(-10,$width),mt_rand(-10,$height),mt_rand(30,300),mt_rand(20,200),55,44,$fontcolor);
        }
        for($i=0;$i<25;$i++){
            $fontcolor=imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
            imagesetpixel($im,mt_rand(0,$width),mt_rand(0,$height),$pointColor);
        }
        for($i=0;$i<$length;$i++) {
            imagestring($im,5,$i*10+5,mt_rand(1,8),$randval{$i}, $stringColor);
        }
        self::output($im,'png');
    }
}