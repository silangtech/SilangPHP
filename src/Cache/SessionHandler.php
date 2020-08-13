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
use SilangPHP\Cache;

//将gc_probability 也调成1000，那gc_probability/gc_divisor 就等于1了，也就是百分一百会触发。这样就垃圾回收概率就大的多。
ini_set('session.gc_divisor', 1000);
ini_set('session.gc_probability', 1000);
//session_write_close();
session_set_save_handler(
    "\SilangPHP\Cache\SessionHandler::init",
    "\SilangPHP\Cache\SessionHandler::close",
    "\SilangPHP\Cache\SessionHandler::read",
    "\SilangPHP\Cache\SessionHandler::write",
    "\SilangPHP\Cache\SessionHandler::destroy",
    "\SilangPHP\Cache\SessionHandler::gc"
);
//后面可使用 
$session_path = PS_RUNTIME_PATH;
// $session_path = "/tmp/";
session_save_path( $session_path.'/session' );
//要确保有session的文件夹，不然session将会失效
//echo 'session start';

/**
 * session接口类
 */
class SessionHandler
{
    //session cookie name
    private static $session_name = '';

    //session_path
    private static $session_path = '';

    //session_id
    private static $session_id   = '';

    //session_live_time
    private static $session_live_time = 3600;

    //session类型 file || mysql
    private static $session_type = '';

    //文件缓存类句柄
    private static $fc_handler   = null;

    /**
     * 页面执行了session_start后首先调用的函数
     * @parem $save_path
     * @parem $cookie_name
     * @return void
     */
    public static function init($save_path, $cookie_name)
    {
        self::$session_name = $cookie_name;
        self::$session_path = $save_path;
        self::$session_id   = session_id();
        self::$session_live_time = empty(self::$session_live_time) ? ini_get('session.gc_maxlifetime') : self::$session_live_time;
        return true;
    }

    /**
     * 读取用户session数据
     * @parem $id
     * @return void
     */
    public static function read( $session_id )
    {
        return Cache::get("sess_".$session_id);
    }

    /**
     * 写入指定id的session数据
     * @parem $id
     * @parem $sess_data
     * @return void
     */
    public static function write($session_id, $sess_data)
    {
        return Cache::set("sess_".$session_id,$sess_data,self::$session_live_time);
    }

    /**
     * 注销指定id的session
     * @parem $id
     * @return void
     */
    public static function destroy( $session_id )
    {
        return Cache::del("sess_$session_id");
        return true;
    }

    /**
     * 清理接口
     * @parem $max_lifetime
     * @return void
     */
    public static function gc($max_lifetime)
    {
        return true;
    }

    /**
     * 关闭接口（页面结束会执行）
     */
    public static function close()
    {
        return true;
    }

}