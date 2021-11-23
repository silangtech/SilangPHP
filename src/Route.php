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
use \FastRoute\RouteCollector;

/**
 * 简单路由
 */
class Route
{
    public static $middlewares = [];
    public static $group_middlewares = [];
    public static $routes = [];
    public static $vars = [];
    public static $handler = [];
    public static $prefix = '';
    public static function use(...$handler)
    {
        self::$middlewares = array_merge(self::$middlewares, $handler);
    }

    public static function GROUP($prefix, callable $callback, ...$middlewares)
    {
        self::addGroup($prefix, $callback, $middlewares);
    }

    public static function GET($route, $handler, ...$middlewares)
    {
        self::addRoute('GET', $route, $handler, $middlewares);
    }

    public static function POST($route, $handler, ...$middlewares)
    {
        self::addRoute('GET', $route, $handler, $middlewares);
    }

    public static function PUT($route, $handler, ...$middlewares)
    {
        self::addRoute('PUT', $route, $handler, $middlewares);
    }

    public static function DELETE($route, $handler, ...$middlewares)
    {
        self::addRoute('DELETE', $route, $handler, $middlewares);
    }

    public static function PATCH($route, $handler, ...$middlewares)
    {
        self::addRoute('PATCH', $route, $handler, $middlewares);
    }

    public static function HEAD($route, $handler, ...$middlewares)
    {
        self::addRoute('HEAD', $route, $handler, $middlewares);
    }

    public static function addGroup($prefix, callable $callback, ...$middlewares)
    {
        self::$prefix = $prefix;
        self::$group_middlewares = array_merge(self::$group_middlewares, $middlewares);
        $callback();
        self::$prefix = '';
        self::$group_middlewares = [];
    }

    public static function addRoute($httpMethod, $route, $handler, ...$middlewares)
    {
        $middlewares_tmp = array_merge(self::$middlewares, self::$group_middlewares);
        if(!empty($middlewares))
        {
            $middlewares_tmp = array_merge($middlewares_tmp, $middlewares);
        }
        $handler = array_merge($middlewares_tmp, [$handler]);
        // var_dump($handler);exit();
        $routes = ['method' => $httpMethod, 'route' => self::$prefix.$route, 'handler' => $handler];
        self::$routes = array_merge(self::$routes, [$routes]);
    }

    public static function next(Context $c)
    {
        $res = '';
        $nextcount = count($c->handler);
        if($nextcount > 0)
        {
            $handler = array_shift($c->handler);
            if($nextcount !=1 )
            {
                $res = self::hander($handler, ['c' => $c]);
            }else{
                $res = self::hander($handler, $c->vars);
            }
        }
        return $res;
    }

    /**
     * 路由开始
     * @param string $pathInfo
     * @return bool|mixed
     * @throws \ReflectionException
     */
    public static function start($uri = '', $method = 'GET', Context $c = null)
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $dispatcher = \FastRoute\simpleDispatcher(function (RouteCollector $r) {
            foreach (self::$routes as $route) {
                $r->addRoute($route['method'], $route['route'], $route['handler']);
            }
        });
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);
        if($dispatcher)
        {
            $routeInfo = $dispatcher->dispatch($method, $uri);
            switch ($routeInfo[0]) {
                case \FastRoute\Dispatcher::NOT_FOUND:
                    return '404 NOT_FOUND';
                    break;
                case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                    $allowedMethods = $routeInfo[1][0];
                    return 'METHOD_NOT_ALLOWED|'.$allowedMethods;
                    break;
                case \FastRoute\Dispatcher::FOUND:
                    $handler = $routeInfo[1];
                    $vars = $routeInfo[2];
                    $middlewaresParams = ['c' => $c];
                    $vars = array_merge($vars, $middlewaresParams);
                    $c->vars = $vars;
                    // 多个handler只允许中间件的存在
                    if(is_array($handler))
                    {
                        $c->handler = $handler;
                        if(count($c->handler) == 1)
                        {
                            $handler = array_shift($c->handler);
                            $res = self::hander($handler, $vars);
                        }else{
                            $handler = array_shift($c->handler);
                            $res = self::hander($handler, $middlewaresParams);
                        }
                    }else{
                        $res = self::hander($handler, $vars);
                    }
                    break;
            }
            return $res;
        }
    }

    public static function hander($handler, $vars)
    {
        if(!is_callable($handler))
        {
            $res = '404';
            // 一般是中间件,不允许同时多个handler
            if(class_exists($handler))
            {
                $res = (new $handler)->handle($vars['c']);
            }else{
                $control = explode('@', $handler);
                $control[0] = str_replace('/', '\\', $control[0]);
                if(class_exists($control[0]))
                {
                    $ins = new $control[0];
                    $res = call_user_func_array(array($ins, $control[1]), $vars);
                }
            }
        }else{
            $res = call_user_func_array($handler, $vars);
        }
        return $res;
    }
}
