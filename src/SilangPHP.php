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
    private static $welcome = '
_________.__.__                       __________  ___ _____________ 
/   _____/|__|  | _____    ____    ____\______   \/   |   \______   \
\_____  \ |  |  | \__  \  /    \  / ___\|     ___/    ~    \     ___/
/        \|  |  |__/ __ \|   |  \/ /_/  >    |   \    Y    /    |    
/_______  /|__|____(____  /___|  /\___  /|____|    \___|_  /|____|    
        \/              \/     \//_____/                 \/           
';

    const VERSION = '2.0.0';
    public static $app;
    // 默认运行模式
    public static $cache = [];
    public static $devlog = 0;
    protected $dbpool;
    private $stack = [];
    private $top = -1;
    public static $action = [];
    public static $input = '';
    public static $output;
    public static $user = 'www-data';
    protected static $container = false;
    public static $http = 1;

    /**
     * 获取ip
     *
     * @return void
     */
    public static function ip()
    {
        if(isset(\SilangPHP\SilangPHP::$app->request->header['x-real-ip']))
        {
            return \SilangPHP\SilangPHP::$app->request->header['x-real-ip'];
        }
        if( isset(\SilangPHP\SilangPHP::$app->request->header['x-forwarded-for']) )
        {
            $arr = explode(',', \SilangPHP\SilangPHP::$app->request->header['x-forwarded-for']);
            foreach ($arr as $ip)
            {
                $ip = trim($ip);
                if ($ip != 'unknown' ) {
                    $client_ip = $ip; break;
                }
            }
        }
        else
        {
            $client_ip = isset(\SilangPHP\SilangPHP::$app->request->server['remote_addr']) ? \SilangPHP\SilangPHP::$app->request->server['remote_addr'] : '';
        }
        preg_match("/[\d\.]{7,15}/", $client_ip, $onlineip);
        $client_ip = ! empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
        return $client_ip;
    }

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
     * 入栈
     */
    public function push($data)
    {
        $this->top = ++$this->top;
        $this->stack[$this->top] = $data;
    }
    /**
     * 出栈
     */
    public function pop()
    {
        if($this->top == -1){
            return false;
        }
        $tmp = $this->stack[$this->top];
        $this->top = --$this->top;
        return $tmp;

    }
    
    public function create($size = 20, $middle = null)
    {
        // 生成池中内容
        for ($i = 0; $i < $size; $i++)
        {
            if($middle)
            {
                $this->put($middle);
            }
        }
    }

    /**
     * 添加
     * @param $middle
     */
    function put($middle)
    {
        $this->dbpool->push($middle);
    }

    /**
     * 获取
     * @return bool|mixed
     */
    function get()
    {
        return $this->dbpool->pop();
    }

    /**
     * 获取临时缓存
     */
    public static function setCache($key)
    {
        return self::$cache[$key] ?? '';
    }

    /**
     * 设置临时缓存
     */
    public static function getCache($key, $value)
    {
        self::$cache[$key] = $value;
    }

    public static $_debug_errortype = array (
        E_WARNING         => "警告",
        E_NOTICE          => "普通警告",
        E_USER_ERROR      => "用户错误",
        E_USER_WARNING    => "用户警告",
        E_USER_NOTICE     => "用户提示",
        E_STRICT          => "运行时错误",
        E_ERROR           => "致命错误",
        E_PARSE           => "解析错误",
        E_CORE_ERROR      => "核心致命错误",
        E_CORE_WARNING    => "核心警告",
        E_COMPILE_ERROR   => "编译致命错误",
        E_COMPILE_WARNING => "编译警告"
    );

    private static $_debug_error_msg;

    /**
     * 获取加载的文件
     * @return string[]
     */
    public static function get_include_file()
    {
        $files = get_included_files();
        return $files;
    }

    /**
     * 错误接管函数
     */
    public static function handler_debug_error($errno, $errmsg, $filename, $linenum, $vars = [])
    {
        if(\SilangPHP\SilangPHP::$app->debug == 1)
        {
            // 这里直接输出了
            $err = self::debug_format_errmsg('debug', $errno, $errmsg, $filename, $linenum, $vars);
            if( $err != '@' )
            {
                self::$_debug_error_msg .= $err;
            }
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
            if( !empty($_SERVER['REQUEST_URI']) )
            {
                $scriptName = $_SERVER['REQUEST_URI'];
                $nowurl = $scriptName;
            } else {
                $scriptName = $_SERVER['PHP_SELF'];
                $nowurl = empty($_SERVER['QUERY_STRING']) ? $scriptName : $scriptName.'?'.$_SERVER['QUERY_STRING'];
            }
            //替换不安全字符
            $f_arr_s = array('<', '*', '#', '"', "'", "\\", '(');
            $f_arr_r = array('〈', '×', '＃', '“', "‘", "＼", '（');
            $nowurl = str_replace($f_arr_s, $f_arr_r, $nowurl);

            $nowtime = date('Y-m-d H:i:s');
            $err = "Time: ".$nowtime.' @URL: '.$nowurl."\n";
        }
        if( empty(self::$_debug_errortype[$errno]) )
        {
            self::$_debug_errortype[$errno] = "<font color='#466820'>手动抛出</font>";
        }
        $error_line = htmlspecialchars($error_line);
        $err .= "<strong>SilangPHP框架应用错误跟踪：</strong><br />\n";
        $err .= "发生环境：" . date("Y-m-d H:i:s", time()).'::' . "<br />\n";
        $err .= "错误类型：" . self::$_debug_errortype[$errno] . "<br />\n";
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
        // 直接输出就ok了
        if(\SilangPHP\SilangPHP::$app->response)
        {
            \SilangPHP\SilangPHP::$app->response->write($err);
        }else{
            echo $err;
        }
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
        self::print(self::$welcome);
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
        if(strpos($cmd, '/') != false){
            $cmd = trim($cmd, "/");
            $cmd = explode("/", $cmd);
        }elseif(strpos($cmd, '@') != false){
            $cmd = trim($cmd, "@");
            $cmd = explode("@", $cmd);
        }else{
            $cmd = trim($cmd, ":");
            $cmd = explode(":", $cmd);
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

    
    /**
     * 容器的绑定
     * @param $abstract
     * @param $concrete
     */
    public static function setDi(String $abstract, $concrete){
        self::$container[$abstract] = $concrete;
    }

    /**
     * 直接获取容器
     * @param $abstract
     * @return mixed|string
     */
    public static function getDi($abstract)
    {
        return self::$container[$abstract] ?? null;
    }

    /**
     *  判断是否有存在
     *
     * @param [type] $abstract
     * @return boolean
     */
    public static function hasDi($abstract)
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
    public function makeDi($abstract, $parameters = []){
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
        return call_user_func_array(self::$container[$abstract], $parameters);
    }

    /**
     * c.HTML(http.StatusOK, "index.html", gin.H{"title": "我是测试", "ce": "123456"})
     *
     * @return void
     */
    public function HTML($file, $params = [])
    {
        \extract($params);
        \ob_start();
        try {
            // include PS_APP_PATH.'/View/'.$file_name.".php";
            include $file;
            // ob_flush();
        } catch (\Throwable $e) {
            echo $e;
        }
        return \ob_get_clean();
    }

    /**
     * 设置目录(废弃的方法)
     * @param [type] $path
     * @return void
     */
    public static function setAppDir(string $path = '')
    {
        return true;
    }

    /**
     * 运行程序
     */
    public static function run($port = '8080', $action = 'start', $config = [])
    {
        date_default_timezone_set('Asia/Shanghai');
        self::$app = new Http($config);
        if(is_object(self::$app))
        {
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
            echo $result;
            return $result;
        }else{
            echo 'no engine';
        }
    }
}