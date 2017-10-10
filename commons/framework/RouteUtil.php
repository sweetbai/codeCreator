<?php

namespace commons\framework;
/**
 * User: sweetbai
 * Date: 2017/3/17
 * Time: 14:30
 */
class RouteUtil
{

    const KEY_SITE_DOMAIN = 'DOMAIN';
    const KEY_SITE_DIR_NAME = 'DIR_NAME';
    const KEY_SITE_NAME = 'NAME';
    const KEY_SITE_SIGN = 'SIGN';
    const KEY_ROUTE = 'ROUTE';
    const KEY_BAN = 'BAN';
    const KEY_SITE_ID = 'ID';
    const KEY_AREA = 'area';
    const KEY_ACTION = 'action';
    const KEY_METHOD = 'method';
    const KEY_ADMIN_URL = 'operation';
    private static $staticFileExtName = array(
        'jpg', 'jpeg', 'png', 'gif', 'bmp', 'psd', 'tga', 'exif', 'pdf',
        'js', 'html', 'css', 'map', 'sass',
        'eot', 'svg', 'ttf', 'woff',
        'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
    );
    private static $siteCache = array();

    public static function getCache($hostName)
    {
        if (count(self::$siteCache) > 0) {
            if (self::$siteCache[self::KEY_SITE_DOMAIN] == $hostName)
                return self::$siteCache;
        }
        self::$siteCache = FileCache::get($hostName);
        return self::$siteCache;
    }

    public static function getSiteName($hostName)
    {
        $SiteCache = self::getCache($hostName);
        if (!array_key_exists(self::KEY_SITE_NAME, $SiteCache)) return false;
        return $SiteCache[self::KEY_SITE_NAME];
    }

    public static function banOldOrNot($hostName)
    {
        $SiteCache = self::getCache($hostName);
        if (!array_key_exists(self::KEY_BAN, $SiteCache)) return false;
        return $SiteCache[self::KEY_BAN];
    }

    public static function routeSearch($routingKey, $hostName)
    {
        $SiteCache = self::getCache($hostName);
        if (!array_key_exists(self::KEY_ROUTE, $SiteCache)) return false;
        if (!array_key_exists($routingKey, $SiteCache[self::KEY_ROUTE])) return false;
        return $SiteCache[self::KEY_ROUTE][$routingKey];
    }

    public static function redirect($url, $code = 302)
    {
        header('location:' . $url, true, $code);
        exit;
    }

    public static function url($routingValue)
    {
        $SiteCache = self::getCache(APP_DOMAIN);
        $result = "http://" . APP_DOMAIN . ROOT_URL . DIR_SEPARATOR;
        $find = self::routeInTable($routingValue, $SiteCache[self::KEY_ROUTE]);
        if (!array_key_exists($routingValue, $SiteCache[self::KEY_ROUTE]) && !$find) {
            $result = $result . $routingValue;
        } else {
            $result = $result . $find;
        }
        return $result;
    }

    public static function getExt($url)
    {
        $urlInfo = parse_url($url);
        $file = basename($urlInfo['path']);
        if (strpos($file, '.') !== false) {
            $ext = explode('.', $file);
            return $ext[count($ext) - 1];
        }
        return false;
    }

    public static function filterStaticFile($url)
    {
        $extName = self::getExt($url);
        if(!$extName) return true;
        foreach (self::$staticFileExtName as $ext) {
            if(strtolower($extName)==strtolower($ext)){
                return true;
            }else{
                continue;
            }
        }
        return false;
    }

    public static function routeInTable($routingValue, $routingTable)
    {
        foreach ($routingTable as $k => $v) {
            if (strtolower($v) == strtolower($routingValue)) {
                return $k;
            }
        }
        return false;
    }

    public static function enRouting(array $areas, $siteId)
    {
        $data = array();
        foreach ($areas as $area) {
            $bases = self::routingListFromAction($area);
            foreach ($bases as $key => $base) {
                foreach ($base as $k => $v) {
                    $data[$key]['site_routing_action'] = $key;
                    $data[$key]['site_routing_method'] = $k;
                    $data[$key]['site_routing_original'] = $v;
                    $data[$key]['site_routing_current'] = $v;
                    $data[$key]['vpn_site_id'] = $siteId;
                }
            }
        }
        return $data;
    }

    public static function routingListFromAction($area)
    {
        $target_dir = ACTION_ROOT . DIR_SEPARATOR . $area;
        $files = scandir($target_dir);
        $result = array();
        foreach ($files as $k => $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $file = str_replace('.php', '', $file);
            $action = $area . DIR_SEPARATOR . $file;
            $result[$action]['index'] = $area . DIR_SEPARATOR . lcfirst(str_replace('Action', '', $file));
            $routes = self::fixRouteFromDocument($area, $file);
            if (!$routes) continue;
            foreach ($routes as $key => $i) {
                $result[$action][$key] = $i;
            }
        }
        return $result;
    }

    private static function fixRouteFromDocument($area, $file)
    {
        $class = $area . "\\" . $file;
        if (!class_exists($class, true)) return false;
        $o = new $class;
        if (!$o instanceof Action) return false;
        $routes = ReflectionAction::getRoutes($o);
        return $routes;
    }

    public static function deRoutingKey()
    {
        $route = str_replace(
            array(
                'domain' => $_SERVER['HTTP_HOST'] . (empty(ROOT_URL) ? DIR_SEPARATOR : ROOT_URL . DIR_SEPARATOR),
                'page'=>'index.php/',
                'query' => '?' . $_SERVER["QUERY_STRING"]
            ),
                '',$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
        );
        return $route;
    }

    public static function Routing()
    {
        $route                   = self::deRoutingKey();
        $find                     = self::routeSearch($route,$_SERVER['HTTP_HOST']);
        $ban                     = self::banOldOrNot($_SERVER['HTTP_HOST']);
        $routeConfig        = self::routingRedirectConfig($find,$ban,$route);
        return $routeConfig;
    }
    public static function routingRedirectConfig($find, $ban, $route)
    {
        if ($route == ""||$route=='/') {
            $route = "site/index";
            $find = $route;
        }
        $isAdminUrl = strpos($route,self::KEY_ADMIN_URL)!==false;
        if(!$isAdminUrl){
            if (!$find) {
                if ($ban) {
                    echo '404<br>';
                    exit(0);
                }
            } else {
                $route = $find;
            }
        }
        list($area, $action, $method) = explode('/', $route);
        $routeConfig = array(
            self::KEY_AREA => lcfirst($area),
            self::KEY_ACTION => lcfirst($action),
            self::KEY_METHOD => lcfirst($method),
        );
//        if(!Tools::IsEmpty($params)) $routeConfig['params'] = $params;
        return $routeConfig;
    }

}