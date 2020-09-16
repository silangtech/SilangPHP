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
class Event{
    public static $event_one_arr = [];
    public static $event_arr = [];
    /**
     * @param $event
     * @param null $method
     */
    public function register($event,$method = null)
    {
        if (!is_callable($method))
        {
            throw new \Exception('event_method_error');
        }
        self::$event_one_arr[$event][] = $method;
    }

    /**
     * 触发一次事件
     * @param $event
     * @param array $params
     * @return bool
     */
    public static function one( $event, $params = [] )
    {
        if (!isset(self::$event_one_arr[$event]))
        {
            return false;
        }
        // 把 $event 插入 $params 数组中
        $method = array_unshift($params, $event);
        if($method)
        {
            call_user_func_array($method, $params);
            return true;
        }else{
            return false;
        }
    }

    /**
     * 设置event
     */
    public function on($event,$method)
    {
        if (!is_callable($method))
        {
            throw new \Exception('event_method_error');
        }
        self::$event_one_arr[$event] = $method;
    }

    /**
     * 触发事件
     * @param $event
     * @param null $param
     * @return bool
     */
    function trigger($event , $param=null){
        if (!isset(self::$event_arr[$event]))
        {
            return false;
        }
        call_user_func_array( self::$event_arr[$event] , $param);
        return true;
    }


}