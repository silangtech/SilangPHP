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
 * Class SilangPHP
 * @package SilangPHP
 */
final Class SilangPHP
{
    const VERSION = '2.0.1';
    public static $app;
    public static $input = '';
    public static $user = 'www-data';
    public static $http = 1;
    public static $debug = 1;

    /**
     * 错误接管函数
     */
    public static function handler_debug_error($errno, $errmsg, $filename, $linenum, $vars = [])
    {
        if(\SilangPHP\SilangPHP::$debug == 1)
        {
            // 这里直接输出了
            self::debug_format_errmsg('debug', $errno, $errmsg, $filename, $linenum, $vars);
        }
    }

    /**
     * exception接管函数
     */
    public static function handler_debug_exception($e)
    {
        $errno     = $e->getCode();
        $errmsg    = $e->getMessage();
        $linenum   = $e->getLine();
        $filename  = $e->getFile();
        $backtrace = $e->getTrace();
        self::handler_debug_error($errno, $errmsg, $filename, $linenum, $backtrace);
    }

    /**
     * 格式化错误信息
     */
    public static function debug_format_errmsg($log_type, $errno, $errmsg, $filename, $linenum, $vars)
    {
        $user_errors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);
        //处理从 catch 过来的错误
        if (in_array($errno, $user_errors))
        {
            foreach($vars as $k=>$e)
            {
                if( is_object($e) && method_exists($e, 'getMessage') )
                {
                    $errno     = $e->getCode();
                    $errmsg    = $errmsg.' '.$e->getMessage();
                    $linenum   = $e->getLine();
                    $filename  = $e->getFile();
                    $backtrace = $e->getTrace();
                }
            }
        }
        //读取源码指定行
        if( !is_file($filename) )
        {
            return '@';
        }
        $fp = fopen($filename, 'r');
        $n = 0;
        $error_line = '';
        while( !feof($fp) )
        {
            $line = fgets($fp, 1024);
            $n++;
            if( $n==$linenum ) {
                $error_line = trim($line);
                break;
            }
        }
        fclose($fp);
        //如果错误行用 @ 进行屏蔽，不显示错误
        if($error_line)
        {
            if( $error_line[0]=='@' || preg_match("/[\(\t ]@/", $error_line) ) {
                return '@';
            }
        }
        $err = '';
        if( $log_type=='debug' )
        {
            $err = "<div style='font-size:14px;line-height:160%;border-bottom:1px dashed #ccc;margin-top:8px;'>\n";
        }
        else
        {
            $nowurl = '';
            //替换不安全字符
            $f_arr_s = array('<', '*', '#', '"', "'", "\\", '(');
            $f_arr_r = array('〈', '×', '＃', '“', "‘", "＼", '（');
            $nowurl = str_replace($f_arr_s, $f_arr_r, $nowurl);

            $nowtime = date('Y-m-d H:i:s');
            $err = "Time: ".$nowtime.' @URL: '.$nowurl."\n";
        }
        $error_line = htmlspecialchars($error_line);
        $err .= "<strong>SilangPHP框架应用错误跟踪：</strong><br />\n";
        $err .= "发生环境：" . date("Y-m-d H:i:s", time()).'::' . "<br />\n";
        $err .= "错误类型：" . $errno . "<br />\n";
        $err .= "出错原因：<font color='#3F7640'>" . $errmsg . "</font><br />\n";
        $err .= "提示位置：" . $filename . " 第 {$linenum} 行<br />\n";
        $err .= "断点源码：<font color='#747267'>{$error_line}</font><br />\n";
        $err .= "详细跟踪：<br />\n";
        $backtrace = debug_backtrace();
        array_shift($backtrace);
        $narr = array('class', 'type', 'function', 'file', 'line');
        foreach($backtrace as $i => $l)
        {
            foreach($narr as $k)
            {
                if( !isset($l[$k]) ) $l[$k] = '';
            }
            $err .= "<font color='#747267'>[$i] in function {$l['class']}{$l['type']}{$l['function']} ";
            if($l['file']) $err .= " in {$l['file']} ";
            if($l['line']) $err .= " on line {$l['line']} ";
            $err .= "</font><br />\n";
        }
        $err .= $log_type=='debug' ? "</div>\n" : "------------------------------------------\n";
        echo $err;
    }

    /**
     * exception的处理
     */
    public static function fatal_handler()
    {
        
    }

    public static function register()
    {
//        $whoops = new \Whoops\Run;
//        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
//        $whoops->register();
        set_exception_handler(array('SilangPHP\SilangPHP', 'handler_debug_exception'));
        set_error_handler(array('SilangPHP\SilangPHP', 'handler_debug_error'), E_ALL);
        register_shutdown_function(array('SilangPHP\SilangPHP', 'fatal_handler'));
    }
    
    /**
     * 运行Command
     */
    public static function runCmd($action = '', $input = '', $usergroup = [])
    {
        self::changeUser($usergroup);
        $welcome = '
_________.__.__                       __________  ___ _____________ 
/   _____/|__|  | _____    ____    ____\______   \/   |   \______   \
\_____  \ |  |  | \__  \  /    \  / ___\|     ___/    ~    \     ___/
/        \|  |  |__/ __ \|   |  \/ /_/  >    |   \    Y    /    |    
/_______  /|__|____(____  /___|  /\___  /|____|    \___|_  /|____|    
        \/              \/     \//_____/                 \/           
';
        echo PHP_EOL."\033[34m ".$welcome." \033[0m".PHP_EOL;
        echo PHP_EOL."\033[34m -------".self::VERSION."------- \033[0m".PHP_EOL;
        $action = self::getAction($action);
        if($input)
        {
            $command = self::getOpt($input);
            self::$input = $command;
        }
        try{
            if(!class_exists($action[0])){
                throw new \Exception("Commander $action[0] not found!");
            }
            $ins = new $action[0]();
            if(property_exists($ins, 'input'))
            {
                $ins->input = self::$input;
            }
            if(method_exists($ins, $action[1])){
                // 注入$input即可
                return call_user_func([$ins, $action[1]]);
            }else{
                throw new \Exception("Commander_Action $action[1] not found!");
            }
        }catch(\Exception $e)
        {
            echo $e->getMessage();
        }
    }

    /**
     * 获取控制器
     * @param $cmd
     */
    public static function getAction($cmd = ''){
        if(strpos($cmd, '@') != false){
            $cmd = trim($cmd, "@");
            $cmd = explode("@", $cmd);
        }
        return $cmd;
    }

    /**
     * 获取参数
     * @param $cmd
     * @return array
     */
    public static function getOpt($cmd = ''){
        if(empty($cmd)){
            return [];
        }
        $cmd = trim($cmd);
        $args = explode("&", $cmd);
        $argv = [];
        foreach($args as $val)
        {
            $tmp = explode("=", $val);
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
        $info = posix_getpwnam(self::$user);
        if($info == false)
        {
            // www备用
            $info = posix_getpwnam('www');
        }
        if($info)
        {
            posix_setuid($info['uid']);
            posix_setgid($info['gid']);
        }
    }

    public static function engine(string $pathroot = '')
    {
        if(empty($pathroot)){
            die('请设置项目路径');
        }
        if(!empty($pathroot)){
            $path = [
                'root' => $pathroot,
                'config' => $pathroot.'/config',
                'tmp' => $pathroot.'/runtime',
            ];
        }

        if(isset($path['root']))
        {
            define('PS_ROOT_PATH', $path['root']);
        }
        if(isset($path['config']))
        {
            define('PS_CONFIG_PATH', $path['config']."/");
        }
        if(isset($path['tmp']))
        {
            define('PS_RUNTIME_PATH', $path['tmp']."/");
        }
        return true;
    }

    /**
     * 运行程序
     */
    public static function run($port = '8080', $action = 'start', $config = [])
    {
        date_default_timezone_set('Asia/Shanghai');
        self::$app = new Http($config);
        self::$app->port = $port;
        try{
            // 运行程序 
            $result = self::$app->run($action);
        }catch(\Exception $e)
        {
            $result = $e->getMessage();
            return $result;
        }
        // 只有fpm才有结果输出
        if(self::$http == 1){
            echo $result;
        }
        return $result;
    }
}