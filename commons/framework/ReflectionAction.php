<?php
namespace commons\framework;
/**
 * User: sweetbai
 * Date: 2017/3/22
 * Time: 15:29
 */
class ReflectionAction
{
    const KEY_ROUTE = 'route';
    public static function getRoutes($classInstance){
        $ref = new \ReflectionClass($classInstance);
        $methods = $ref->getMethods(\ReflectionMethod::IS_PUBLIC);
        $r = array();
        foreach ($methods as $method) {
            $name = $method->getName();
            if(strpos($name,'__')!==false) continue;
            $doc = $method->getDocComment();
            if($doc) $r[$method->getName()] = (new DocParser())->parse($doc);
            $route = $r[$method->getName()][self::KEY_ROUTE];
            if(!isset($route)) continue;
            $r[$method->getName()] = $r[$method->getName()][self::KEY_ROUTE];
        }
        return $r;
    }
}