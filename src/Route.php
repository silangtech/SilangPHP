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
/**
 * 简单路由，后期再更改
 */
class Route extends \FastRoute\Route
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
    public static function start($path = '' ,$method = 'GET')
    {
        $path = parse_url($path,PHP_URL_PATH);
        // 默认加载的类
        if(empty($path) || $path === '/' || $path === '/index.php')
        {
            $path =  SilangPHP::$app->ct."/".SilangPHP::$app->ac;
        }
        $uri = $path;
        $dispatcher = false;
        if(!class_exists(App\Router::class)){
            $dispatcher = \FastRoute\simpleDispatcher(\App\Router::initialize());
        }
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);
        if($dispatcher)
        {
            $routeInfo = $dispatcher->dispatch($method, $uri);
            switch ($routeInfo[0]) {
                case \FastRoute\Dispatcher::NOT_FOUND:
                    // 看看有没默认模式，没有就直接404
                    if(Config::get("Site.routemode") == '2')
                    {
                        return '404';
                    }else{
                        return self::found($path,$method);
                    }
                    break;
                case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                    $allowedMethods = $routeInfo[1];
                    break;
                case \FastRoute\Dispatcher::FOUND:
                    $handler = $routeInfo[1];
                    $vars = $routeInfo[2];
                    if(is_callable($handler))
                    {
                        $Reflection = new \ReflectionFunction($handler);
                        $acParams = $Reflection->getParameters();
                        $argsParam = [];
                        if($acParams)
                        {
                            foreach($acParams as $acParam)
                            {
                                $acParamClass = $acParam->getType() && !$acParam->getType()->isBuiltin()  ? new \ReflectionClass($acParam->getType()->getName()) : null;
                                if(!empty($acParamClass))
                                {
                                    $paramClassName = $acParamClass->name;
                                    $argsParam[] = Di::instance()->make($paramClassName);
                                }else{
                                    if($vars)
                                    {
                                        $pathtmp = array_shift($vars);
                                        if($pathtmp)
                                        {
                                            $argsParam[] = $pathtmp;
                                        }
                                    }
                                }
                            }
                        }
                        return call_user_func_array($handler, $argsParam );
                    }else{
                        return self::found($handler,$method,$vars);
                    }
                    break;
            }
        }else{
            return self::found($path,$method);
        }
    }

    public static function found($path,$method,$vars = [])
    {
        $path = trim($path,"/");
        self::load_rule();
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
                foreach(self::$rules as $rulekey => $rulepath)
                {
                    $ruleStatus = preg_match("/^".$rulekey."$/",$path.'_'.$method);
                    if($ruleStatus)
                    {
                        $middlewares = self::$rule_pipe[$rulekey.'_'.$method] ?? [];
                        $path =  $rulepath;
                        break;
                    }
                }
            }
        }
        self::$path_array = preg_split("/[\/]/",$path,-1,\PREG_SPLIT_NO_EMPTY);
        $controllerpath = PS_APP_PATH.'/Controller/';
        $controlstack = [];
        foreach(self::$path_array as $searchkey => $searchpath)
        {
            $controllerpath2 = $controllerpath.ucfirst($searchpath).'/';
            if(file_exists($controllerpath2))
            {
                $controllerpath = $controllerpath2;
                array_push($controlstack,ucfirst($searchpath));
                unset(self::$path_array[$searchkey]);
            }else{
                $controller = ucfirst($searchpath);
                array_push($controlstack,$controller);
                unset(self::$path_array[$searchkey]);
                $action = isset(self::$path_array[$searchkey+1]) ? self::$path_array[$searchkey+1] : '';
                unset(self::$path_array[$searchkey + 1]);
                break;
            }
        }
        if($vars)
        {
            self::$path_array = $vars;
        }
        return self::load_controller($controlstack,$action,$middlewares);
    }

    /**
     * @param $base
     * @param $action
     * @return bool|mixed
     * @throws \ReflectionException
     */
    private static function load_controller(array $controlstack = [], string $action = '',array $middlewares = []){
        $cts = implode("/",$controlstack);
        $file = PS_APP_PATH.'/Controller/'. $cts.'Controller.php';
        if(file_exists($file)){
            include_once($file);
            $cls = PS_APP_NAME.'\\Controller\\'. $cts . 'Controller';
            if(!class_exists($cls)){
                throw new \Exception("Controller $cls not found!");
            }
            $ins = new $cls();
            $ins->request = SilangPHP::$app->request;
            $ins->response = SilangPHP::$app->response;
            $found = false;
            if(method_exists($ins, $action)){
                $ins->action = $action;
                SilangPHP::$app->ct = $cts;
                SilangPHP::$app->ac = $action;
                $found = true;
                $ctlRef = new \ReflectionClass($cls);
                $acRef = $ctlRef->getMethod($action);
                $acParams = $acRef->getParameters();
                $argsParam = [];
                if($acParams)
                {
                    foreach($acParams as $acParam)
                    {
                        $acParamClass = $acParam->getType() && !$acParam->getType()->isBuiltin()  ? new \ReflectionClass($acParam->getType()->getName()) : null;
                        if(!empty($acParamClass))
                        {
                            $paramClassName = $acParamClass->name;
                            $argsParam[] = Di::instance()->make($paramClassName);
                        }else{
                            $pathtmp = array_shift(self::$path_array);
                            if($pathtmp)
                            {
                                $argsParam[] = $pathtmp;
                            } 
                        }
                    }
                }
            }
            if($found){
                if(method_exists($ins,'beforeAction'))
                {
                    $response = call_user_func_array(array($ins, 'beforeAction'), [$action]);
                    // 有return会直接返回，避免die,exit的操作
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
                $response = $next(\SilangPHP\SilangPHP::$app->request);
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
