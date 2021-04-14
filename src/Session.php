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
class Session{
    public static $start = 0;
    /**
     * 设置Session变量
     */
    public static function set($key,$value)
    {
        $_SESSION[$key] = $value;
        return true;
    }

    /**
     * 设置session type
     * @todo 动态切换的方法
     * @param $type
     */
    public static function setSessionType($type)
    {
        \SilangPHP\Cache\SessionHandler::$session_type = $type;
    }

    /**
     * 开始
     */
    public static function start()
    {
        if(self::$start == 0)
        {
            // 可以读取配置文件
            \SilangPHP\Cache\SessionHandler::register();
            self::$start = 1;
        }
        session_start();
        return true;
    }

    /**
     * 获取相关变量
     */
    public static function get($key)
    {
        return $_SESSION[$key] ?? false;
    }
}