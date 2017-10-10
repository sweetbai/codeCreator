<?php
namespace commons\framework;
/**
 * User: sweetbai
 * Date: 2017/3/21
 * Time: 14:00
 */
class Config
{
    /**
     * 全局配置
     * @var array
     */
     protected static $config = array();
    /**
     * 初始化配置
     * @return void
     */
     public static function init() {
        self::$config = array(
            'ROUTE'=>array()
        );
    }
     public static function loadConfig($file){
        if( !file_exists($file) ){
            throw new \Exception("Config file '{$file}' not found", 500);
        }
        $config = require($file);
        foreach($config as $k=>$v){
            if( is_array($v) ){
                if( !isset(self::$config[$k]) ) self::$config[$k] = array();
                self::$config[$k] = array_merge((array)self::$config[$k], $config[$k]);
            }else{
                self::$config[$k] = $v;
            }
        }
    }
    /**
     * 获取配置项
     * @param  string $key 配置名
     * @return mixed
     */
     public static function get($key=NULL){
        if( empty($key) ) return self::$config;
        $arr = explode('.', $key);
        switch( count($arr) ){
            case 1 :
                if( isset(self::$config[ $arr[0] ])) {
                    return self::$config[ $arr[0] ];
                }
                break;
            case 2 :
                if( isset(self::$config[ $arr[0] ][ $arr[1] ])) {
                    return self::$config[ $arr[0] ][ $arr[1] ];
                }
                break;
            case 3 :
                if( isset(self::$config[ $arr[0] ][ $arr[1] ][ $arr[2] ])) {
                    return self::$config[ $arr[0] ][ $arr[1] ][ $arr[2] ];
                }
                break;
            default: break;
        }
        return NULL;
    }

    /**
     * 设置配置项
     * @param string $key   配置名
     * @param mixed $value 配置值
     *
     * @return bool
     */
     public static function set($key, $value){
        $arr = explode('.', $key);
        switch( count($arr) ){
            case 1 :
                self::$config[ $arr[0] ] = $value;
                break;
            case 2 :
                self::$config[ $arr[0] ][ $arr[1] ] = $value;
                break;
            case 3 :
                self::$config[ $arr[0] ][ $arr[1] ][ $arr[2] ] = $value;
                break;
            default: return false;
        }
        return true;
    }
}