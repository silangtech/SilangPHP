<?php
namespace App\Controller;

use App\Middleware\HelloMiddleware;
use App\Middleware\TestMiddleware;

class IndexController extends \SilangPHP\Controller
{
    public $middlewares = [HelloMiddleware::class,TestMiddleware::class];
    public $onlyAction = [];
    public $exceptAction = [];
    public function beforeAction($action = '')
    {
        echo 'before'.lr;
    }

    public function index(\SilangPHP\Request $request)
    {
        echo  'action内容：index'.lr;
    }

    public function index2()
    {
        echo 'index3';
    }

    public function sessiontest()
    {
        \SilangPHP\Session::start();
        \SilangPHP\Session::set("a1234",1234);
        $tmp = \SilangPHP\Session::get("a1234");
        var_dump($tmp);

        setcookie("test","1234");
        var_dump($_COOKIE);
    }


}