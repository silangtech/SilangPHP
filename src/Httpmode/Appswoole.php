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

Class Appswoole extends Appbase{
    public $appname = 'swoole';
    public $http;
    /**
     * 更新双R
     */
    public function updateR($request, $response)
    {
        $this->request = new \SilangPHP\Request();
        $this->response = new \SilangPHP\Response();
        $this->request->hander = $request;
        $this->request->method = $this->request->hander->server['request_method'];
        $this->request->gets = $this->request->hander->get;
        $this->request->posts = $this->request->hander->post;
        $this->request->cookies = $this->request->hander->cookie;
        $this->request->files = $this->request->hander->files;
        $this->request->server = $this->request->hander->server;
        $this->request->header = $this->request->hander->header;
        $this->request->raw = $this->request->hander->rawContent();

        $this->response->hander = $response;

        \SilangPHP\Di::instance()->set(Request::class, $this->request);
        \SilangPHP\Di::instance()->set(Response::class, $this->response);
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
            $argv = $_SERVER['argv'];
            if(!isset($argv['1']))
            {
                $argv = 'start';
            }else{
                $argv = $argv['1'];
            }
            // 运行数据或启动服务
            if($pathinfo == 'command')
            {
                return \SilangPHP\Console::start();
            }else{
                $frameconfig = $this->config;
                $serviceHost = $frameconfig['host'] ?? '0.0.0.0';
                $servicePort = $frameconfig['port'] ?? 8080;
                $pid_file = PS_RUNTIME_PATH.$servicePort.'.pid';
                $log_file = PS_RUNTIME_PATH.$servicePort.'.log';
                $serverWorkerCount = $frameconfig['count'] ?? 1;
                if($argv == 'start')
                {
                    $this->http = new \Swoole\Http\Server($serviceHost, $servicePort);
                    $this->http->set([
                        'worker_num' => swoole_cpu_num() * 2,
                        'user' => 'www-data',
                        'group' => 'www-data',
                        'daemonize' => 1,
                        'backlog' => 128,
                        'pid_file' => $pid_file,
                        'log_file' => $log_file,
                    ]);
                    $this->http->on("start", function ($server) use ($servicePort) {
                        swoole_set_process_name("SilangPHP_HTTP_SERVER".$servicePort);
                    });
                    $this->http->on("ManagerStart", function($server) use ($servicePort){
                        swoole_set_process_name("SilangPHP_HTTP_SERVER".$servicePort."_manager");
                    });
                    $this->http->on("WorkerStart", function ($server) use ($servicePort) {
                        swoole_set_process_name("SilangPHP_HTTP_SERVER".$servicePort."_worker");
                    });
                    $app = $this;
                    $this->http->on('request', function ($request, $response) use($app) {
                        $app->updateR($request,$response);
                        $path = $request->server['request_uri'];
                        $method = $request->server['request_method'];
                        try{
                            $res = \SilangPHP\Route::start($path, $method);
                        }catch(\Exception $e)
                        {
                            $this->logger->error($e->getMessage());
                            $res = 404;
                        }
                        $response->end($res);
                    });
                    return $this->http->start();
                }elseif($argv == 'stop'){
                    if(file_exists($pid_file))
                    {
                        $pid = file_get_contents($pid_file);
                        if($pid)
                        {
                            \Swoole\Process::kill((int)$pid, SIGTERM);
                        }
                        $pid = file_put_contents($pid_file, "");
                        echo '停止web服务!'.PHP_EOL;
                    }else{
                        echo '没有找到可停止的web服务';
                    }
                }
            }
            return true;
        }catch(\SilangPHP\Exception\routeException $e){
            return 'app route error';
        }
        
    }

}