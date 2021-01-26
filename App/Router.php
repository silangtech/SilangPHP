<?php
namespace App;
use \FastRoute\RouteCollector;
use \SilangPHP\Request;
use \SilangPHP\Response;

class Router
{
    public static function initialize()
    {
        return function (RouteCollector $routeCollector){
            $routeCollector->get('/testindex', 'index/index');
            $routeCollector->get('/phpshow/{id:\d+}', function (Request $request, Response $response,$id='1') {
                echo 'phpshow'.$id;
            });
        };
    }
}