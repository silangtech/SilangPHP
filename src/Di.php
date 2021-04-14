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
use SilangPHP\Traits\Instance;
class Di
{
    use Instance;
    protected static $container = false;
    /**
     * 容器的绑定
     * @param $abstract
     * @param $concrete
     */
    public function set(String $abstract, $concrete){
        self::$container[$abstract] = $concrete;
    }

    /**
     * 直接获取容器
     * @param $abstract
     * @return mixed|string
     */
    public function get($abstract)
    {
        return self::$container[$abstract] ?? null;
    }

    /**
     *  判断是否有存在
     *
     * @param [type] $abstract
     * @return boolean
     */
    public function has($abstract)
    {
        if(isset(self::$container[$abstract]))
        {
            return true;
        }else{
            return false;
        }
    }

    /**
     * 容器调用
     * @param $abstract
     * @param array $parameters
     * @return mixed
     */
    public function make($abstract, $parameters=[]){
        if(!isset(self::$container[$abstract]))
        {
            if(class_exists($abstract)) {
                $tmp =  new $abstract(...$parameters);
                self::$container[$abstract] = $tmp;
                return $tmp;
            } else {
                return '';
            }
        }
        if(empty($parameters))
        {
            return self::$container[$abstract];
        }
        return call_user_func_array(self::$container[$abstract],$parameters);
    }
}
