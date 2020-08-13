<?php
/*LICENSE
+-----------------------------------------------------------------------+
| SilangPHP Framework                                                   |
+-----------------------------------------------------------------------+
| This program is free software; you can redistribute it and/or modify  |
| it under the terms of the GNU General Public License as published by  |
| the Free Software Foundation. You should have received a copy of the  |
| GNU General Public License along with this program.  If not, see      |
| http://www.gnu.org/licenses/.                                         |
| Copyright (C) 2020. All Rights Reserved.                              |
+-----------------------------------------------------------------------+
| Supports: http://www.github.com/silangtech/SilangPHP                  |
+-----------------------------------------------------------------------+
*/
declare(strict_types=1);
namespace SilangPHP;

/**
 * 这个类主要引用，平时不用知道它的存在
 * Class Facade
 * @package SilangPHP
 */
class Facade
{
    public static $facade_obj = [];

    /**
     * 获取句柄
     * @param $classname
     * @param $args
     * @return mixed
     */
    public static function getInstance($classname,$args){
        $name = basename($classname);
        if(!isset(self::$facade_obj[$name]))
        {
            self::$facade_obj[$name] = new $classname($args);
        }
        return self::$facade_obj[$name];
    }

    /**
     * 获取新建的类
     * @param $class
     * @return string
     */
    public static function getFacadeAccessor(){
        $classname =  static::class;
        return $classname;
    }

    /**
     * 回调静态方法
     * @param $method
     * @param $arg
     * @return mixed
     */
    public static function __callstatic($method,$arg){
        $instance=static::getInstance(static::getFacadeAccessor(),[]);
        return call_user_func_array(array($instance,$method),$arg);
    }
}