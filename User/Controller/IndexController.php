<?php
namespace User\Controller;

use App\Model\IndexModel;
use SilangPHP\Tpl;
use SilangPHP\Cache;

class IndexController extends \SilangPHP\Controller
{
    public function index2(\SilangPHP\Request $request)
    {
        echo 'this is User App';
    }
}