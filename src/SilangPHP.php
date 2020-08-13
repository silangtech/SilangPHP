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

// 统一中国时区
date_default_timezone_set('Asia/Shanghai');
// 核心地址
define('DS',DIRECTORY_SEPARATOR);
defined('CORE_PATH') or define('CORE_PATH', __DIR__);

/**
 * Class SilangPHP
 * @package SilangPHP
 */
final Class SilangPHP
{
    const VERSION = '1.0.0';
    protected static $appDir;
    public static $config = [];
    public static $ct = 'index';
    public static $ac = 'index';
    public static $mode = 0;
    public static $debug = 1;
    public static $debug_ip = '';
    public static $cookie_domain = '';
    public static $startTime = '';
    public static $endTime = '';
    // 内存里的缓存
    public $cache = [];

    /**
     * 初始化
     */
    public static function initialize()
    {
        $appName = basename(self::$appDir);
        define("PS_APP_PATH",        self::$appDir);
        define("PS_APP_NAME",       $appName);
        define("PS_CONFIG_PATH",		PS_APP_PATH."/Config/");
        define("PS_RUNTIME_PATH",		PS_ROOT_PATH."/Runtime/");
        self::$config = Config::get("Site");
        if(self::$config)
        {
            self::$ct = self::$config['defaultController'];
            self::$ac = self::$config['defaultAction'];
            self::$debug_ip = self::$config['debug_ip'];
            self::$cookie_domain = self::$config['cookie_domain'];
        }
        if(PHP_SAPI == 'cli')
        {
            define("run_mode",2);
            define("lr",PHP_EOL);
        }else{
            define("run_mode",1);
            define("lr","<br/>");
            // fpm模式下
            if(self::$debug = '1' && self::$mode == 0)
            {
                $safe_ip = '';
                if(self::$debug_ip)
                {
                    $safe_ip = explode(",",self::$debug_ip);
                }
                $debug = 1;
                // 开启ip的情况
                if($safe_ip)
                {
                    $ip = \SilangPHP\Util\Util::get_client_ip();
                    if( (in_array($ip,$safe_ip)) )
                    {
                        $debug = 1;
                    }else{
                        $debug = 0;
                    }
                }
                if($debug)
                {
                    error_reporting(E_ALL);
                    Error::register();
                }else{
                    error_reporting(0);
                }
            }else{
                error_reporting(0);
            }
        }
    }

    /**
     * 获取临时缓存
     */
    public static function get($key)
    {
        return self::$cache[$key] ?? '';
    }

    /**
     * 设置临时缓存
     */
    public static function set($key,$value)
    {
        self::$cache[$key] = $value;
    }

    /**
     * 设置程序目录
     * @param $path
     */
    public static function setAppDir($path)
    {
        self::$appDir = $path;
    }

    /**
     * 运行程序
     */
    public static function run($pathinfo = '')
    {
        self::$startTime = microtime(true);
        if(empty(self::$appDir))
        {
            return false;
        }else{
            self::initialize();
        }
        if(run_mode == '2')
        {
            $cli = 1;
            if(self::$config['mode'] != 0)
            {
                $cli = 0;
            }
        }
        if($cli == 1)
        {
            Console::start();
        }else{
            $res = Route::start($pathinfo);
        }
        self::$endTime = microtime(true);
        return Response::end($res);

    }
}