<?php

namespace commons\framework;
/**
 * User: sweetbai
 * Date: 2017/3/25
 * Time: 14:37
 */
class SiteFileUtil
{
    const TPL_ROOT = ROOT.'/tpl';
    const TPL_ADMIN_DIR = 'admin';
    const TPL_BASE_DIR = 'localhost';

    public static function CreateSiteDir($dirName,$access=0777,$recursive=true){
        Verify::isFalseWillException(self::TPL_ADMIN_DIR!=$dirName,'无法创建为后台管理目录',HTTP_REQUEST_NOT_ARRIVE);
        Verify::isFalseWillException(self::TPL_BASE_DIR!=$dirName,'无法创建为为网站基本目录',HTTP_REQUEST_NOT_ARRIVE);
        $path = TPL_ROOT.DIR_SEPARATOR.$dirName;
        Verify::isFalseWillException(!is_dir($path),"站点目录:'".$dirName."'已存在",HTTP_REQUEST_NOT_ARRIVE);
        if(mkdir($path, $access, $recursive)){
            return true;
        } else {
            return false;
        }
    }

    public static function RenameSiteDir($target,$newName){
        try{
            Verify::isFalseWillException(!Tools::IsEmpty($target),'原名称不可为空',HTTP_REQUEST_NOT_ARRIVE);
            Verify::isFalseWillException(!Tools::IsEmpty($newName),'新名称不可为空',HTTP_REQUEST_NOT_ARRIVE);
            Verify::isFalseWillException(self::TPL_ADMIN_DIR!=$target,'后台管理目录无法更改',HTTP_REQUEST_NOT_ARRIVE);
            Verify::isFalseWillException(self::TPL_ADMIN_DIR!=$newName,'无法更改为后台管理目录',HTTP_REQUEST_NOT_ARRIVE);
            Verify::isFalseWillException(self::TPL_BASE_DIR!=$target,'网站基本目录无法更改',HTTP_REQUEST_NOT_ARRIVE);
            Verify::isFalseWillException(self::TPL_BASE_DIR!=$newName,'无法更改为为网站基本目录',HTTP_REQUEST_NOT_ARRIVE);
            $old = TPL_ROOT.DIR_SEPARATOR.$target;
            Verify::isFalseWillException(is_dir($old),'原路径不是一个有效路径',HTTP_REQUEST_NOT_ARRIVE);
            $new = TPL_ROOT.DIR_SEPARATOR.$newName;
            $r = @rename($old,$new);
            return $r;
        }catch (\Exception $e){
            throw $e;
        }
    }
}