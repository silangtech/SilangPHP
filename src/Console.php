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
    public static $user = 'www-data';

    /**
     * 输出
     *
     * @return void
     */
    public static function print(string $content = '')
    {
        echo PHP_EOL."\033[34m ".$content." \033[0m".PHP_EOL;
    }

    /**
     * 运行Command
     */
    public static function start($action = '', $input = '', $usergroup = [])
    {
        self::changeUser($usergroup);
        self::print(self::$welcome);
        $argv = $_SERVER['argv'];
        if($action)
        {
            $action = self::getAction($action);
        }else{
            if(isset($argv[1]))
            {
                $action = self::getAction($argv[1]);
            }else{
                self::print("缺少 action!!");
                return false;
            }
        }
        if($input)
        {
            $command = self::getOpt($input);
            self::$input = $command;
        }else{
            if(isset($argv[2]))
            {
                $command = self::getOpt($argv[2]);
                self::$input = $command;
            }
        }

        $controller = $action[0];
        $action = $action[1];
        // 指定配置的读取
        $config = \SilangPHP\Config::get("Console");
        if(isset($config[$controller]))
        {
            $controller = $config[$controller];
        }
        $cls = PS_APP_NAME.'\\Command\\'. $controller . 'Commander';
        if(!class_exists($cls)){
            throw new \Exception("Commander $cls not found!");
        }
        $ins = new $cls();
        if(property_exists($cls,'input'))
        {
            $ins->input = self::$input;
        }

        if(method_exists($ins, $action)){
            // 注入$input即可
            return $ins->$action();
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
        $argv = [];
        foreach($args as $val)
        {
            $tmp = explode("=",$val);
            if(isset($tmp[1]))
            {
                $argv[$tmp[0]] = $tmp[1];
            }
        }
        return $argv;
    }

    /**
     * 改变进程的用户ID
     * @param $user
     */
    public static function changeUser(array $usergroup = [])
    {
        if(isset($usergroup['user']))
        {
            self::$user = $usergroup['user'];
        }
        $info=posix_getpwnam(self::$user);
        if($info == false)
        {
            // www备用
            $info=posix_getpwnam('www');
        }
        if($info)
        {
            posix_setuid($info['uid']);
            posix_setgid($info['gid']);
        }
    }


}