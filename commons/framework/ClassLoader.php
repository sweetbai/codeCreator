<?php
namespace commons\framework;


class ClassLoader
{
    /**
     *保存注册状态
     * @var bool
     */
    protected static $registered = false;
    /**
     * 保存已经加载过的文件
     * @var array
     */
    protected static $classes = array();
    /**
     * 按需加载类文件
     * @param $class
     * @return bool
     */
    public static function loadClass($class) {
        $classFile = str_replace(array('\\', '_'), DIR_SEPARATOR, $class) . '.php';
        $file = ROOT.DIR_SEPARATOR. $classFile;
        if (!isset(self::$classes[$file])) {
            if (!file_exists($file)) {
                return false;
            }
            self::$classes[$file] = $classFile;
            require_once $file;
        }
        return true;
    }
    /**
     * 注册自动加载函数
     * @return void
     */
    public static function register()
    {
        if (! static::$registered) {
            static::$registered = spl_autoload_register([__CLASS__, 'loadClass']);
        }
    }
}
