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
use Workerman\Worker;

Class Appworker{
    public $appDir;
    public $config = [];
    public $ct = 'index';
    public $ac = 'index';
    public $debug = 1;
    public $debug_ip = '';
    public $startTime = '';
    public $endTime = '';
    public $cacheType = 'file';
    public $max_request = 10000;
    // 内存里的缓存
    public $cache = [];
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
                // 注安卓ip模式 composer要加载\SilangPHP\Util工具类
                $ip = \SilangPHP\Util\Util::get_client_ip();
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
    public function updateR($request, $connection)
    {
        $this->request = new \SilangPHP\Request();
        $this->response = new \SilangPHP\Response();
        // $this->request->hander = new \SilangPHP\Httpmode\worker\Request();
        $this->request->hander = $request;
        $this->request->gets = $this->request->hander->get();
        $this->request->posts = $this->request->hander->post();
        $this->request->header = $this->request->hander->header();
        $this->request->cookies = $this->request->hander->cookie();
        $this->request->files = $this->request->hander->file();
        $this->request->raw = $this->request->hander->rawBody();

        $this->response->hander = new \SilangPHP\Httpmode\worker\Response($connection);

        \SilangPHP\Di::instance()->set(\SilangPHP\Request::class,$this->request);
        \SilangPHP\Di::instance()->set(\SilangPHP\Response::class,$this->response);
    }

    /**
     * 运行程序
     */
    public function run($pathinfo = '')
    {
        if(PHP_SAPI != 'cli')
        {
            echo 'mode not in cli';
            return false;
        }
        try{
            if(empty($this->appDir))
            {
                return false;
            }else{
                $this->initialize();
            }
            // 运行数据或启动服务
            if($pathinfo == 'command')
            {
                return \SilangPHP\Console::start();
            }else{
                $frameconfig = $this->config;
                $serviceHost = $frameconfig['host'] ?? '0.0.0.0';
                $servicePort = $frameconfig['port'] ?? 8080;
                $serverWorkerCount = $frameconfig['count'] ?? 1;
                //每次运行框架的日期
                $nowdate = date("Ymd",time());
                Worker::$stdoutFile = PS_RUNTIME_PATH."/log/warning".$nowdate.".log";
                $worker = new Worker("http://{$serviceHost}:{$servicePort}");
                // php进程用户
                $worker->user = 'www-data';
                $worker->count = $serverWorkerCount;
                $worker->onWorkerStart = array($this, 'onWorkerStart');
                $worker->onMessage = array($this, 'onMessage');
                Worker::runAll();
            }
        }catch(\SilangPHP\Exception\routeException $e){
            return 'app route error';
        }
        
    }

    public function onWorkerStart($worker)
    {
        
    }

    public function onMessage($connection = null, $request = null)
    {
        static $request_count = 0;
        if(++$request_count >= $this->max_request)
        {
            \Workerman\Worker::stopAll();
        }
        // echo $request->path()."|".$connection->id.lr;
        $this->startTime = microtime(true);
        $method = $request->method();
        $path = $request->path();
        $this->updateR($request,$connection);
        $res = \SilangPHP\Route::start($path,$method);
        $this->endTime = microtime(true);
        $connection->send($res);
        // return $this->response->end($res);
    }
}