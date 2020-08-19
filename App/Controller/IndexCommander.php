<?php
namespace App\Controller;

use App\Model\IndexModel;
use SilangPHP\Tpl;
use SilangPHP\Cache;

class IndexCommander
{
    public function index()
    {
        echo 'console'.PHP_EOL;
        Tpl::assign("test","test1");
        Tpl::display('index');
    }

}