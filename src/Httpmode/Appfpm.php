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

Class Appfpm{
    public $appDir;
    public $config = [];
    public $ct = 'index';
    public $ac = 'index';
    public $debug = 1;
    public $debug_ip = '';
    public $startTime = '';
    public $endTime = '';
    public $cacheType = 'file';
    public $request;
    public $response;

    /**
     * 初始化
     */
    public function initialize()
    {
        $this->config = \SilangPHP\Config::get("Site");
        if($this->config)
        {
            $this->ct = $this->config['defaultController'] ?? 'index';
            $this->ac = $this->config['defaultAction'] ?? 'index';
            $this->debug = $this->config['debug'];
            $this->debug_ip = $this->config['debug_ip'] ?? '';
            $this->cacheType = $this->config['cacheType'] ?? 'file';
        }
        
        if($this->debug = '1')
        {
            $safe_ip = '';
            if($this->debug_ip)
            {
                $safe_ip = explode(",",$this->debug_ip);
            }
            $debug = 1;
            // 开启ip的情况
            if($safe_ip)
            {
                $ip = \SilangPHP\Helper\Util::get_client_ip();
                if( (in_array($ip,$safe_ip)) )
                {
                    $debug = 1;
                }else{
                    $debug = 0;
                }
            }
            if($debug)
            {
                error_reporting(E_ALL);
                \SilangPHP\Error::register();
            }else{
                error_reporting(0);
            }
        }else{
            error_reporting(0);
        }
    }

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
            if($this->debug == 1 || run_mode == 1)
            {
                echo '404';
            }
            return '';
        }
        $this->endTime = microtime(true);
    }
}