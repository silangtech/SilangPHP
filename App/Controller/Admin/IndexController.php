<?php
namespace App\Controller\Admin;

use App\Middleware\HelloMiddleware;
use App\Middleware\TestMiddleware;

class IndexController extends \SilangPHP\Controller
{
    public $middlewares = [HelloMiddleware::class,TestMiddleware::class];
    public $onlyAction = [];
    public $exceptAction = [];

    public function index(\SilangPHP\Request $request)
    {
        echo  'admin_index'.lr;
    }
    public function test2($abc = '1')
    {
        var_dump($abc);
    }
}