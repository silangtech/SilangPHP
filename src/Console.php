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

/**
 * Class Console
 * @package SilangPHP
 */
Class Console{
    use Instance;
    private static $welcome = '
  _________.__.__                       __________  ___ _____________ 
 /   _____/|__|  | _____    ____    ____\______   \/   |   \______   \
 \_____  \ |  |  | \__  \  /    \  / ___\|     ___/    ~    \     ___/
 /        \|  |  |__/ __ \|   |  \/ /_/  >    |   \    Y    /    |    
/_______  /|__|____(____  /___|  /\___  /|____|    \___|_  /|____|    
        \/              \/     \//_____/                 \/           
';

    public static $action = [];
    public static $input = '';
    public static $output;
    public static $uid = '33';
    public static $gid = '33';

    /**
     * 运行Command
     */
    public static function start()
    {
        echo self::$welcome;
        $argv = $_SERVER['argv'];
        $action = self::getAction($argv[1]);
        $command = self::getOpt($argv[2]);
        self::$input = $command;
        $controller = $action[0];
        $action = $action[1];
        $cls = PS_APP_NAME.'\\Controller\\'. $controller . 'Controller';
        if(!class_exists($cls)){
            throw new \Exception("Controller $cls not found!");
        }
        $ins = new $cls();
        if(method_exists($ins, $action)){
            // 注入$input即可
            $ins->$action();
        }
    }

    /**
     * 获取控制器
     * @param $cmd
     */
    public static function getAction($cmd)
    {
        $cmd = trim($cmd,"/");
        $cmd = explode("/",$cmd);
        return $cmd;
    }

    /**
     * 获取参数
     * @param $cmd
     * @return array
     */
    public static function getOpt($cmd)
    {
        if(empty($cmd))
        {
            return [];
        }
        $cmd = trim($cmd);
        $args = explode("&",$cmd);
        foreach($args as $val)
        {
            $tmp = explode("=",$val);
            $argv[$tmp[0]] = $tmp[1];
        }
        return $argv;
    }

    /**
     * 改变进程的用户ID
     * @param $user
     */
    public static function changeUser($uid='33',$gid='33')
    {
        posix_setuid($uid);
        posix_setgid($gid);
    }


}