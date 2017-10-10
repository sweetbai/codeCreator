<?php
namespace commons\framework;
/**
 * User: sweetbai
 * Date: 2017/3/21
 * Time: 15:06
 */
class Cache {
    private static $dir = ROOT.DIR_SEPARATOR.'datas/cache';
    private static $expiration = 36000;
    function __construct($dir = '')
    {
        if(!empty($dir)){ self::$dir = $dir; }
    }

    private static function _name($key)
    {
        return sprintf("%s/%s", self::$dir, sha1($key));
    }

    public static function get($key, $expiration = '')
    {
        if ( !is_dir(self::$dir) OR !is_writable(self::$dir))
        {
            return FALSE;
        }
        $cache_path = self::_name($key);
        if (!@file_exists($cache_path))
        {
            return FALSE;
        }
        $expiration = empty($expiration) ? self::$expiration : $expiration;
        if (filemtime($cache_path) < (time() - $expiration))
        {
            self::clear($key);
            return FALSE;
        }
        if (!$fp = @fopen($cache_path, 'rb'))
        {
            return FALSE;
        }
        flock($fp, LOCK_SH);
        $cache = '';
        if (filesize($cache_path) > 0)
        {
            $cache = unserialize(fread($fp, filesize($cache_path)));
        }
        else
        {
            $cache = NULL;
        }
        flock($fp, LOCK_UN);
        fclose($fp);
        return $cache;
    }

    public static function set($key, $data)
    {
        if ( !is_dir(self::$dir) OR !is_writable(self::$dir))
        {
            self::_makeDir(self::$dir);
        }
        $cache_path = self::_name($key);
        if ( ! $fp = fopen($cache_path, 'wb'))
        {
            return FALSE;
        }
        if (flock($fp, LOCK_EX))
        {
            fwrite($fp, serialize($data));
            flock($fp, LOCK_UN);
        }
        else
        {
            return FALSE;
        }
        fclose($fp);
        @chmod($cache_path, 0666);
        return TRUE;
    }

    public static function clear($key)
    {
        $cache_path = self::_name($key);
        if (file_exists($cache_path))
        {
            unlink($cache_path);
            return TRUE;
        }
        return FALSE;
    }

    public static function clearAll()
    {
        $dir = self::$dir;
        if (is_dir($dir))
        {
            $dh=opendir($dir);
            while (false !== ( $file = readdir ($dh)))
            {
                if($file!="." && $file!="..")
                {
                    $fullpath=$dir."/".$file;
                    if(!is_dir($fullpath)) {
                        unlink($fullpath);
                    } else {
                        delfile($fullpath);
                    }
                }
            }
            closedir($dh);
            // rmdir($dir);
        }
    }

    private static function _makeDir( $dir, $mode = "0666" ) {
        if( ! $dir ) return 0;
        $dir = str_replace( "\\", "/", $dir );
        $mdir = "";
        foreach( explode( "/", $dir ) as $val ) {
            $mdir .= $val."/";
            if( $val == ".." || $val == "." || trim( $val ) == "" ) continue;
            if( ! file_exists( $mdir ) ) {
                if(!@mkdir( $mdir, $mode )){
                    return false;
                }
            }
        }
        return true;
    }
}
