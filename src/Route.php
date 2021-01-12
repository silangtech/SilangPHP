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
use \SilangPHP\Facade\Log;
use \SilangPHP\Traits\Instance;
class Route
{
    public static $rules = [];
    public static $rules_exec = [];
    public static $rule_pipe = [];
    public static $path_array = [];
    use Instance;
    /**
     * 封装常用get方法
     */
    public static function get($path,\Closure $cb)
    {
        self::$rules_exec[$path.'_GET'] = $cb;
    }

    /**
     * 封装常用post方法
     */
    public static function post($path,\Closure $cb)
    {
        self::$rules_exec[$path.'_POST'] = $cb;
    }

    public static function middle($method,$path_array = [],$middlewares = [])
    {
        if($path_array)
        {
            $method = strtoupper($method);
            foreach($path_array as $path)
            {
                self::$rule_pipe[$path.'_'.$method] = $middlewares;
            }
            return true;
        }
        return false;
    }

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
        if(isset(self::$rules_exec[$path.'_'.$method]))
        {
            return call_user_func(self::$rules_exec[$path.'_'.$method]);
        }
        $middlewares = self::$rule_pipe[$path.'_'.$method] ?? [];
        // 直接找
        if(isset(self::$rules[$path.'_'.$method]))
        {
            $path = self::$rules[$path.'_'.$method];
        }else{
            // 开启某种模式跳404
            if(self::$rules)
            {
                foreach(self::$rules as $rulekey => $re)
                {
                    $ruleStatus = preg_match("/^".$rulekey."$/",$path.'_'.$method);
                    if($ruleStatus)
                    {
                        $middlewares = self::$rule_pipe[$rulekey.'_'.$method] ?? [];
                        $path = self::$rules[$rulekey];
                        break;
                    }
                }
            }
        }
        // 默认加载的类
        if(empty($path) || $path === '/' || $path === 'index.php')
        {
            $path =  SilangPHP::$ct."/".SilangPHP::$ac;
        }
        self::$path_array = preg_split("/[\/]/",$path,-1,PREG_SPLIT_NO_EMPTY);
        // 统一规范
        $controller = ucfirst(self::$path_array[0] ?? '');
        $action = strtolower(self::$path_array[1] ?? '');
        \SilangPHP\SilangPHP::$ct = $controller;
        \SilangPHP\SilangPHP::$ac = $action;
        unset(self::$path_array[0],self::$path_array[1]);
        return self::load_controller($controller,$action,$middlewares);
    }

    /**
     * @param $base
     * @param $action
     * @return bool|mixed
     * @throws \ReflectionException
     */
    private static function load_controller($base, $action, $middlewares = []){
        $dir = PS_APP_PATH . '/Controller/' . $base;
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
            $ins->request = SilangPHP::$request;
            $ins->response = SilangPHP::$response;
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
                if(method_exists($ins,'beforeAction'))
                {
                    $response = call_user_func_array(array($ins, 'beforeAction'), [$action]);
                    if(!empty($response) && !is_bool($response))
                    {
                        return $response;
                    }
                }
                $next = function ($request) use ($ins,$action,$argsParam) {
                    return call_user_func_array(array($ins, $action), $argsParam );
                };
                if(empty($middlewares))
                {
                    $middlewares = $ins->middleware();
                }
                if($middlewares)
                {
                    if( (empty($ins->exceptAction) && empty($ins->onlyAction)) || (!empty($ins->exceptAction) && !in_array($action,$ins->exceptAction)) || ( !empty($ins->onlyAction) && in_array($action,$ins->onlyAction)) )
                    {
                        foreach ($middlewares as $middleware) {
                            $next = function ($request) use ($next, $middleware) {
                                return (new $middleware)->handle($request, $next);
                            };
                        }
                    }
                }
                $response = $next(\SilangPHP\SilangPHP::$request);
                if(method_exists($ins,'afterAction'))
                {
                    $response2 = call_user_func_array(array($ins, 'afterAction'), [$action, $response]);
                    if(!empty($response2) && !is_bool($response2))
                    {
                        return $response2;
                    }
                }
                return $response;
            }else{
                throw new routeException("ca error!");
            }
        }
        return false;
    }

    /**
     * 加载 rewrite rule 文件
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
                    $Rulenew[$val['1'].'_GET'] = 'get';
                    $Rulenew[$val['1'].'_POST'] = 'post';
                    $Rulenew[$val['1'].'_PUT'] = 'put';
                    $Rulenew[$val['1'].'_DELETE'] = 'delete';
                    $Rulenew[$val['1'].'_PATCH'] = 'patch';
                    $Rulenew[$val['1'].'_HEAD'] = 'head';
                    $Rulenew[$val['1'].'_OPTIONS'] = 'options';
                }
                if(!isset($val['0']) || !isset($val['1']) || !isset($val['2']) )
                {
                    continue;
                }
                $val['0'] = strtoupper($val['0']);
                $Rulenew[$val['1'].'_'.$val['0']] = $val['2'];
                if(isset($val['3']))
                {
                    if(is_array($val['3']))
                    {
                        self::$rule_pipe[$val['1'].'_'.$val['0']] = $val['3'];
                    }
                }
            }
            self::$rules = $Rulenew;
        }
    }

}
