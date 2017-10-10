<?php
namespace commons\framework;


class App
{
    public static function Run(array $config){
        try{
            $action = Tools::IsEmpty($config['action'])?'Index':$config['action'];
            $actionClass = "\\actions"."\\".$config['area']."\\".ucfirst($action)."Action";
            $file = ROOT.str_replace(array('\\', '_'), DIR_SEPARATOR, $actionClass).'.php';
            if( !file_exists($file) ) {
//                throw new \Exception("action '{$actionClass}' not found", 404);
                throw new \Exception("file '{$file}' not found", 404);
            }
            $o =new $actionClass();
            $method = lcfirst(Tools::IsEmpty($config['method'])?'index':$config['method']);
            if( !method_exists($o, $method) ){
                throw new \Exception("Action '{$actionClass}::{$method}()' not found", 404);
            }
            $o->{$method}();
        }catch (\Exception $e){
            if(IS_APP_DEBUG) throw $e;
            Tools::Error('App.class',"routeUrl:".$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            Tools::Error('App.class',"routeUrl_replace:".json_encode(array(
                    'domain' => $_SERVER['HTTP_HOST'] . (empty(ROOT_URL) ? '' : ROOT_URL . DIR_SEPARATOR),
                    'query' => '?' . $_SERVER["QUERY_STRING"]
                )));
            Tools::Error('App.class',"routeConfig:".json_encode($config));
            Tools::Error('App.class',$e->getMessage());
        }
    }
}