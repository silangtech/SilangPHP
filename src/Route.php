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
use \SilangPHP\Exception\routeException;
use SilangPHP\Facade\Log;
class Route
{
    public static $rules = [];
    public static $path_array = [];

    /**
     * 路由开始
     * @param string $pathInfo
     * @return bool|mixed
     * @throws \ReflectionException
     */
    public static function start($pathInfo = '')
    {
        self::load_rule();
        if(!empty($pathInfo)){
            $path= $pathInfo;
        }elseif(!empty($_SERVER['PATH_INFO'])){
            $path= $_SERVER["PATH_INFO"];
        }elseif(!empty($_SERVER['REQUEST_URI'])){
            $path= $_SERVER["REQUEST_URI"];
        }
        // 未必要从$_SERVER里获取
        $method = $_SERVER['REQUEST_METHOD'];
        $path = trim($path,"/");
        $path = parse_url($path,PHP_URL_PATH);
        if(isset(self::$rules[$path.'_'.$method]))
        {
            $path = self::$rules[$path.'_'.$method];
        }else{
            // 开启某种模式跳404
        }
        // 默认加载的类
        if(empty($path) || $path === '/' || $path === 'index.php')
        {
            $path =  SilangPHP::$ct."/".SilangPHP::$ac;
        }
        self::$path_array = preg_split("/[\/]/",$path,-1,PREG_SPLIT_NO_EMPTY);
        $controller = self::$path_array[0];
        $action = self::$path_array[1];
        unset(self::$path_array[0],self::$path_array[1]);
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
                // todo 后续改成event驱动
                if(method_exists($ins,'beforeAction'))
                {
                    call_user_func_array(array($ins, 'beforeAction'), []);
                }
                if(method_exists($ins,'afterAction'))
                {
                    $tmp = call_user_func_array(array($ins, $action), $argsParam );
                    call_user_func_array(array($ins, 'afterAction'), []);
                    return $tmp;
                }else{
                    return call_user_func_array(array($ins, $action), $argsParam );
                }
            }else{
                throw new routeException("ca error!");
            }
        }
        return false;
    }

    /**
     * 加载 rewrite rule 文件
     * @todo 正则的支持
     */
    protected static function load_rule()
    {
        if(!empty(self::$rules))
        {
            return self::$rules;
        }
        $Rule = Config::get('Route');
        if($Rule)
        {
            foreach($Rule as $key=>$val)
            {
                if($val['0'] == 'rest' && !empty($val['1']))
                {
                    // rest模式
                    $Rule[$val['1'].'_GET'] = 'get';
                    $Rule[$val['1'].'_POST'] = 'post';
                    $Rule[$val['1'].'_PUT'] = 'put';
                    $Rule[$val['1'].'_DELETE'] = 'delete';
                    $Rule[$val['1'].'_PATCH'] = 'patch';
                    $Rule[$val['1'].'_HEAD'] = 'head';
                    $Rule[$val['1'].'_OPTIONS'] = 'options';
                }
                if(!isset($val['0']) || !isset($val['1']) || !isset($val['2']) )
                {
                    continue;
                }
                $val['0'] = strtoupper($val['0']);
                $Rule[$val['1'].'_'.$val['0']] = $val['2'];
            }
            self::$rules = $Rule;
        }
    }

}
