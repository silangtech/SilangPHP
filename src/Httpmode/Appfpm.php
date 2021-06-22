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
namespace SilangPHP\Httpmode;

use SilangPHP\Log;

Class Appfpm extends Appbase{
    public $appname = 'fpm';

    /**
     * 更新双R
     */
    public function updateR()
    {
        $this->request = new \SilangPHP\Request();
        $this->response = new \SilangPHP\Response();
        \SilangPHP\Di::instance()->set(\SilangPHP\Request::class,$this->request);
        \SilangPHP\Di::instance()->set(\SilangPHP\Response::class,$this->response);
    }

    /**
     * 运行程序
     */
    public function run($pathinfo = '')
    {
        $this->startTime = microtime(true);
        try{
            if(empty($this->appDir))
            {
                return false;
            }else{
                $this->initialize();
            }
            if(run_mode == '2')
            {
                return \SilangPHP\Console::start($pathinfo);
            }else{
                $this->updateR();
                $method = $_SERVER['REQUEST_METHOD'];
                if(!empty($pathInfo)){
                    $path= $pathInfo;
                }elseif(!empty($_SERVER['PATH_INFO'])){
                    $path= $_SERVER["PATH_INFO"];
                }elseif(!empty($_SERVER['REQUEST_URI'])){
                    $path= $_SERVER["REQUEST_URI"];
                }
                $res = \SilangPHP\Route::start($path,$method);
                return $this->response->end($res);
            }
        }catch(\SilangPHP\Exception\routeException $e){
            $this->logger->error($e->getMessage());
            if($this->debug == 1 || run_mode == 1)
            {
                echo '404';
            }
            return '';
        }
        $this->endTime = microtime(true);
    }
}