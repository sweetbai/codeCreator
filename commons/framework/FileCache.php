<?php
namespace commons\framework;

class FileCache
{
    const EXPIRE_10_YEAR = 315360000;
    const SPLIT_TIME_BEGIN = '<TIME>';
    const SPLIT_TIME_END = '</TIME>';
    const SPLIT_NAME_BEGIN = '<NAME>';
    const SPLIT_NAME_END = '</NAME>';
    const SPLIT_START = '<?php exit;?>';
    protected static $config = array(
        'CACHE_PATH' => 'datas/cache/',
        'GROUP' => 'tmp',
        'HASH_DEEP' => 0,
    );
    public function __construct() {}
    public static function setConfig(array $config)
    {
        self::$config = array_merge(self::$config, (array)$config);
    }
    public static function get($key,$dir='') {
        $content = @file_get_contents( self::_getFilePath($key,$dir) );
        if( empty($content) ) return false;
        $time_begin_at = strpos($content,self::SPLIT_TIME_BEGIN)+strlen(self::SPLIT_NAME_BEGIN);
        $time_end = strpos($content,self::SPLIT_TIME_END);
        $expire  =  (int) substr($content, $time_begin_at, $time_end-$time_begin_at);
        if( time() >= $expire ) return false;
        $name_begin_at = strpos($content,self::SPLIT_NAME_BEGIN)+strlen(self::SPLIT_NAME_BEGIN);
        $content_begin_at = strpos($content,self::SPLIT_NAME_END)+strlen(self::SPLIT_NAME_END);
        $md5Sign  =  substr($content, $name_begin_at, 32);
        $content   =  substr($content, $content_begin_at);
        if( $md5Sign != md5($content) ) return false;
        return @unserialize($content);
    }
    public static function set($key, $value, $expire = 1800,$dir='') {
        $value = serialize($value);
        $md5Sign = md5($value);
        $expire = time() + $expire;
        $content    = '<?php exit;?>' . self::SPLIT_TIME_BEGIN.$expire.self::SPLIT_TIME_END . self::SPLIT_NAME_BEGIN.$md5Sign .self::SPLIT_NAME_END. $value;
        return @file_put_contents(self::_getFilePath($key,$dir), $content, LOCK_EX);
    }
    public static function inc($key, $value = 1) {
        return self::set($key, intval(self::get($key)) + intval($value), -1);
    }
    public static function des($key, $value = 1) {
        return self::set($key, intval(self::get($key)) - intval($value), -1);
    }
    public static function del($key,$dir='') {
        return @unlink( self::_getFilePath($key,$dir) );
    }
    public static function clear( $dir='' ) {
        if( empty($dir) ) {
            $dir = self::$config['CACHE_PATH'] . '/' . self::$config['GROUP'] . '/';
            $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
        }
        if ( !is_dir($dir) ) return false;
        $handle = opendir($dir);
        while ( ($file = readdir($handle)) !== false ){
            if ( '.' != $file && '..' != $file ){
                is_dir("$dir/$file")? self::clear("$dir/$file") : @unlink("$dir/$file");
            }
        }
        if ( readdir($handle) == false ){
            closedir($handle);
//            @rmdir($dir);
        }
        return true;
    }
    private static function _getFilePath($key, $dir='') {
        $key = md5($key);
        if($dir==''){
            $dir = self::$config['CACHE_PATH'] . '/' . self::$config['GROUP'] . '/';
        }
        for($i=0; $i<self::$config['HASH_DEEP']; $i++){
            $dir = $dir. substr($key, $i*2, 2).'/';
        }
        $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
        if ( !file_exists($dir) ) {
            if ( !@mkdir($dir, 0777, true) ){
                throw new \Exception("Can not create dir  when use FileCache'{$dir}'", 500);
            }
        }
        if ( !is_writable($dir) ) @chmod($dir, 0777);
        return $dir. $key . '.php';
    }
}