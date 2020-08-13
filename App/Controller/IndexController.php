<?php
namespace App\Controller;

use App\Model\IndexModel;
use SilangPHP\Tpl;
use SilangPHP\Cache;

class IndexController extends \SilangPHP\Controller
{
    public function index()
    {
        $Index = new \App\Model\IndexModel();
        $tmp = $Index->insert1([]);
        var_dump($tmp);
        exit();
        \SilangPHP\Config::env();

        Tpl::assign("test","test1");
        Tpl::display('index');
    }
}