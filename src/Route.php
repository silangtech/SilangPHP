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
namespace SilangPHP;
use SilangPHP\Facade\Log;
class Route
{
    public static $rules = array();
    protected static $is_load = false;
    public static $path_array = [];

    /**
     * 路由开始
     * @param string $pathInfo
     * @return bool|mixed
     * @throws \ReflectionException
     */
    public static function start($pathInfo = '')
    {
        if(!empty($pathInfo)){
            $path= $pathInfo;
        }elseif(!empty($_SERVER['PATH_INFO'])){
            $path= $_SERVER["PATH_INFO"];
        }elseif(!empty($_SERVER['REQUEST_URI'])){
            $path= $_SERVER["REQUEST_URI"];
        }
        // 默认加载的类
        if(empty($path) || $path === '/')
        {
            $ct = SilangPHP::$ct;
            $ac = SilangPHP::$ac;
            $path =  SilangPHP::$ct."/".SilangPHP::$ac;
        }

        self::$path_array = preg_split("/[\/]/",$path,-1,PREG_SPLIT_NO_EMPTY);
        $controller = self::$path_array[0];
        $action = self::$path_array[1];
        return self::load_controller($controller,$action);
    }

    /**
     * @param $base
     * @param $action
     * @return bool|mixed
     * @throws \ReflectionException
     */
    private static function load_controller($base, $action){
        $dir = PS_APP_PATH . '/controller/' . $base;
        $file = $dir . 'Controller.php';
        #echo join(', ', array($base, $action, $file)) . "\n";
        if(file_exists($file)){
            include($file);
            $ps = explode('/', $base);
            $controller = ucfirst($ps[count($ps) - 1]);
            $cls = PS_APP_NAME.'\\Controller\\'. $controller . 'Controller';
            if(!class_exists($cls)){
                throw new \Exception("Controller $cls not found!");
            }
            $ins = new $cls();

            $found = false;
            if(method_exists($ins, $action)){
                $ins->action = $action;
                $found = true;
                $ctlRef = new \ReflectionClass($cls);
                $acRef = $ctlRef->getMethod($action);
                $acParams = $acRef->getParameters();
                $argsParam = [];
                if($acParams)
                {
                    foreach($acParams as $acParam)
                    {
                        $acParamClass = $acParam->getClass();
                        if(!empty($acParamClass))
                        {
                            $paramClassName = $acParamClass->name;
                            $argsParam[] = Di::instance()->make($paramClassName);
                        }else{
                            $argsParam[] = array_shift(self::$path_array);
                        }
                    }
                }
            }
            if($found){
                Log::info("Controller: $file");
            }
            return call_user_func_array(array($ins, $action), $argsParam );
        }
        return false;
    }


    /**
     * 加载 rewrite rule 文件
     */
    protected static function load_rule()
    {
        self::$is_load = true;
        $rulefile = PS_CONFIG_PATH.'/rewrite.ini';
        if( file_exists($rulefile) )
        {
            $ds = file($rulefile);
            foreach($ds as $line)
            {
                $line = trim($line);
                if( $line=='' || $line[0]=='#')
                {
                    continue;
                }
                list($s, $t) = preg_split('/[ ]{4,}/', $line); //用至少四个空格分隔，这样即使s、t中有空格也能识别
                $s = rtrim($s);
                $t = ltrim($t);
                if( $s != '' && $t !='' )
                {
                    $_s = preg_replace("#(^[\^]|[\$]$)#", '', $s);
                    $sok = $s[0]=='^' ? '<rw>'.$_s : '<rw>(.*)'.$_s;
                    $s = $s[strlen($s)-1]=='$' ? $sok.'</rw>' : $sok.'([^<]*)</rw>';
                    $s = preg_replace("#(^[\^]|[\$]$)#", '', $s);
                    //$s = '<rw>'.$_s.'</rw>';
                    self::$rules[ $s ] = $t;
                }
            }
        }
    }

    /**
     * 转换要输出的内容里的网址
     * @parem string $html
     */
    public static function convert_html(&$html)
    {
        if( !self::$is_load ) {
            self::load_rule();
        }
        //echo '<xmp>';
        foreach(self::$rules as $s => $t) {
            //echo "$s -- $t \n";
            $html = preg_replace('~'.$s.'~iU', $t, $html);
        }
        //exit();
        $html = preg_replace('#<[/]{0,1}rw>#', '', $html);
        return $html;
    }

    /**
     * 转换单个网址
     * @parem string $url
     */
    public static function convert_url($url)
    {
        if( !self::$is_load )
        {
            self::load_rule();
        }
        foreach(self::$rules as $s=>$t)
        {
            $url = preg_replace('/'.$s.'/iU', $t, $url);
        }
        return $url;
    }
    
}
