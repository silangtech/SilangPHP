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

Class Appworker extends Appbase{
    public $appname = 'worker';
    /**
     * 更新双R
     */
    public function updateR($request, $connection)
    {
        $this->request = new \SilangPHP\Request();
        $this->response = new \SilangPHP\Response();
        // $this->request->hander = new \SilangPHP\Httpmode\worker\Request();
        $this->request->hander = $request;
        $this->request->method = $this->request->hander->method();
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
    }
}