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
namespace SilangPHP\Cache;
use SilangPHP\Config;

class Redis
{
    public static $hand_ob = null;

    public static function handle()
    {
        if(self::$hand_ob == null)
        {
            self::renew();
        }
    }

    public static function renew()
    {
        $redis = new \Redis();
        //config文件夹读取 不连接会出现Redis server went away
        $config = Config::get("Db")['redis']['master'];
        $redis->connect($config['host'], (int)$config['port']);
        if(!empty($config['auth']))
        {
            $redis->auth($config['auth']);
        }
        if(isset($config['db']))
        {
            $redis->select((int)$config['db']);
        }
        self::$hand_ob = $redis;
    }

    /**
     * 获取句柄
     */
    public function getHand()
    {
        self::handle();
        return self::$hand_ob;
    }

    /**
     * 静态调用方法
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        self::handle();
        return call_user_func_array(array(self::$hand_ob,$name),$arguments);
    }

    /**
     * 调用方法
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        self::handle();
        return call_user_func_array(array(self::$hand_ob,$name),$arguments);
    }
}