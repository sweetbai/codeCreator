<?php
/* APP GLOBAL*/
define("IS_APP_DEBUG", false);
define("IS_APP_LOG", true);
define("IS_APP_FIRST_RUN", true);
define("APP_NAME", 'vpn');
define('APP_DOMAIN', $_SERVER['HTTP_HOST']);
define("DIR_SEPARATOR", '/');
define("APP_DES_KEY", '@$s1j4jz');
define("ROOT", str_replace('/config', '', str_replace('\\', '/', dirname(__FILE__))));
define('ACTION_ROOT',  ROOT.'/actions');
define('ROOT_URL',  '/tb');
define('PUBLIC_URL', ROOT_URL . '/commons');
define('PUBLIC_STATIC_URL', ROOT_URL . '/static');
define('TPL_ROOT', ROOT . '/tpl');
define('TPL_URL', ROOT_URL . '/tpl');
define('PROJECT_PTAH', ROOT . '/commons/project');
/*END APP GLOBAL*/
if(IS_APP_DEBUG){
    error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_WARNING);
}else{
    error_reporting(0);
}
if (version_compare(PHP_VERSION, '5.6.0','<')) {
    header("Content-Type: text/html; charset=UTF-8");
    exit('PHP环境不能低于5.6.0');
}
if(!defined('IN_APP')) {
    exit('请从正确网址访问.');
}

if(!isset($_SESSION))
    session_start();
date_default_timezone_set('Asia/Shanghai');
header("Content-Type: text/html; charset=UTF-8");

include ROOT.'/config/config.php';
include ROOT.'/commons/function/fzr.php';
include ROOT.'/commons/smarty/Smarty.Conf.php';
include ROOT.'/commons/framework/ClassLoader.php';
//register autoload function
commons\framework\ClassLoader::register();
//common functions
/**
 * smarty 注册函数 见Action->initSmarty()
 * 动态生成实际URL对应配置的URL
 * @param string $url 实际URL
 *
 * @return string
 */
function url($url){
    return \commons\framework\RouteUtil::url($url);
}


