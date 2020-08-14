<?php
namespace App\Controller;

use App\Model\IndexModel;
use SilangPHP\Tpl;
use SilangPHP\Cache;

class IndexController extends \SilangPHP\Controller
{
    public function index(\SilangPHP\Request $request)
    {
        $tmp = $this->request->get("test","",'int');
        echo $tmp;
        Tpl::assign("test","test1");
        Tpl::display('index');
    }
}