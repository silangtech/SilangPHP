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

/**
 * swoole协程版
 */
Class AppswooleCo extends Appbase{
    public $appname = 'swooleco';
    /**
     * 更新双R
     */
    public function updateR($request, $response)
    {
        $this->request = new \SilangPHP\Request();
        $this->response = new \SilangPHP\Response();
        $this->request->hander = $request;
        $this->request->gets = $this->request->hander->get;
        $this->request->posts = $this->request->hander->post;
        $this->request->cookies = $this->request->hander->cookie;
        $this->request->server = $this->request->hander->server;
        $this->request->files = $this->request->hander->files;
        $this->request->header = $this->request->hander->header;
        $this->request->raw = $this->request->hander->rawContent();

        $this->response->hander = $response;

        \SilangPHP\Di::instance()->set(Request::class,$this->request);
        \SilangPHP\Di::instance()->set(Response::class,$this->response);
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
                
                $http = new \Swoole\Coroutine\Http\Server($serviceHost, $servicePort);
                $http->set([
                    'worker_num' => swoole_cpu_num() * 2,
                    'user' => 'www-data',
                    'group' => 'www-data',
                    'daemonize' => 1,
                    'backlog' => 128,
                    'pid_file' => PS_RUNTIME_PATH.$servicePort.'.pid',
                    'log_file' => PS_RUNTIME_PATH.$servicePort.'.log',
                ]);
                $http->on("start", function ($server) {
                    
                });
                $app = $this;
                $http->on('request', function ($request, $response) use($app) {
                    $app->updateR($request,$response);
                    $path = $request->server['request_uri'];
                    $method = $request->server['request_method'];
                    $res = \SilangPHP\Route::start($path,$method);
                    $response->end($res);
                });
                $http->start();
                return true;
            }
        }catch(\SilangPHP\Exception\routeException $e){
            return 'app route error';
        }
        
    }

}