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
declare(strict_types = 1);
namespace SilangPHP;
use Workerman\Worker;
use Workerman\Protocols\Http\Response as WorkResponse;

Class Http
{
    public $host = '0.0.0.0';
    public $port = 8080;
    public $count = 2;
    public $daemonize = 1;
    public $debug = 1;
    public $request;
    public $response;
    public $max_request = 10000;
    public $logger = null;
    public $tmp = '';

    public function __construct($config = [], $tmp = '')
    {
        $this->host = $config['host'] ?? $this->host;
        $this->port = $config['port'] ?? $this->port;
        $this->count = $config['count'] ?? $this->count;
        $this->daemonize = $config['daemonize'] ?? $this->daemonize;
        $this->debug = $config['debug'] ?? $this->debug;
        $this->tmp = $tmp;
        if(defined(PS_RUNTIME_PATH))
        {
            $this->tmp = PS_RUNTIME_PATH;
        }
    }

    public function initialize($constraint = 0)
    {
        if($constraint == 1)
        {
            $this->debug = 1;
        }
        if($this->debug == '1')
        {
            error_reporting(E_ALL);
            SilangPHP::register();
        }else{
            error_reporting(0);
        }
    }

    /**
     * 运行！
     *
     * @return void
     */
    public function run()
    {
        switch(SilangPHP::$http)
        {
            case 1:
                $this->initialize();
                $res = $this->fpm();
                break;
            case 2:
                $res = $this->workerman();
                break;
            case 3:
                $res = $this->swoole();
                break;
        }
        return $res;
    }

    public function fpm()
    {
        $this->request = new \SilangPHP\request();
        $this->response = new \SilangPHP\response();
        $method = $_SERVER['REQUEST_METHOD'];
        if(!empty($pathInfo)){
            $path= $pathInfo;
        }elseif(!empty($_SERVER['PATH_INFO'])){
            $path= $_SERVER["PATH_INFO"];
        }elseif(!empty($_SERVER['REQUEST_URI'])){
            $path= $_SERVER["REQUEST_URI"];
        }
        $ctx = new Context($this->request, $this->response);
        $res = \SilangPHP\Route::start($path, $method, $ctx);
        return $this->response->end($res);
    }

    public function workerman()
    {
        //每次运行框架的日期
        $nowdate = date("Ymd", time());
        if(defined(PS_RUNTIME_PATH))
        {
            Worker::$stdoutFile = PS_RUNTIME_PATH."/log/warning".$nowdate.".log";
        }
        $worker = new Worker("http://{$this->host}:{$this->port}");
        // php进程用户
        $worker->user = 'www-data';
        $worker->count = $this->count;
        // $worker->onWorkerStart = array($this, 'onWorkerStart');
        $worker->onMessage = function($connection = null, $wrequest = null)
        {
            static $request_count = 0;
            if(++$request_count >= $this->max_request)
            {
                \Workerman\Worker::stopAll();
            }
            // echo $request->path()."|".$connection->id.lr;
            $request = new \SilangPHP\Request();
            $response = new \SilangPHP\Response();
            $request->hander = $wrequest;
            $request->method = $request->hander->method();
            $request->gets = $request->hander->get();
            $request->posts = $request->hander->post();
            $request->header = $request->hander->header();
            $request->cookies = $request->hander->cookie();
            $request->uri = $request->hander->path();
            $request->files = $request->hander->file();
            $request->raw = $request->hander->rawBody();

            $response->hander = new class($connection) {
                public $connection;
                public function __construct($connection)
                {
                    $this->connection = $connection;
                }
                
                public function write($data)
                {
                    $res = new WorkResponse(200, [], $data);
                    $this->connection->send($res);
                }
            
                public function send($data)
                {
                    $res = new WorkResponse(200, [], $data);
                    $this->connection->send($res);
                }
            
                public function end($data)
                {
                    $res = new WorkResponse(200, [], $data);
                    $this->connection->send($res);
                }
            
                /**
                 * @param int $status
                 * @param array $headers
                 * @param string $body
                 * @return Response
                 */
                function response($body = '', $status = 200, $headers = array())
                {
                    return new WorkResponse($status, $headers, $body);
                }
            
                /**
                 * @param $data
                 * @param int $options
                 * @return Response
                 */
                function json($data, $options = JSON_UNESCAPED_UNICODE)
                {
                    return new WorkResponse(200, ['Content-Type' => 'application/json'], json_encode($data, $options));
                }
            
                /**
                 * @param $xml
                 * @return Response
                 */
                function xml($xml)
                {
                    if ($xml instanceof \SimpleXMLElement) {
                        $xml = $xml->asXML();
                    }
                    return new WorkResponse(200, ['Content-Type' => 'text/xml'], $xml);
                }
            };
            $ctx = new Context($request, $response, $connection->id);
            $res = \SilangPHP\Route::start($request->uri, $request->method, $ctx);
            $connection->send($res);
        };
        Worker::runAll();
    }

    public function swoole($run = 'start')
    {
        $pid_file = $this->tmp.$this->port.'.pid';
        $log_file = $this->tmp.$this->port.'.log';
        $servicePort = $this->port;
        if($run == 'start')
        {
            if(empty($this->count))
            {
                $this->count = swoole_cpu_num() * 2;
            }
            $http = new \Swoole\Http\Server($this->host, $this->port);
            $http->set([
                'worker_num' => $this->count,
                'user' => 'www-data',
                'group' => 'www-data',
                'daemonize' => $this->daemonize,
                'backlog' => 128,
                'pid_file' => $pid_file,
                'log_file' => $log_file,
            ]);
            $http->on("start", function ($server) use ($servicePort) {
                swoole_set_process_name("SilangPHP_HTTP_SERVER".$servicePort);
            });
            $http->on("ManagerStart", function($server) use ($servicePort){
                swoole_set_process_name("SilangPHP_HTTP_SERVER".$servicePort."_manager");
            });
            $http->on("WorkerStart", function ($server) use ($servicePort) {
                swoole_set_process_name("SilangPHP_HTTP_SERVER".$servicePort."_worker");
            });
            $http->on('request', function ($srequest, $sresponse) {
                $request = new \SilangPHP\Request();
                $response = new \SilangPHP\Response();
                $request->hander = $srequest;
                $request->method = $request->hander->server['request_method'];
                $request->gets = $request->hander->get;
                $request->posts = $request->hander->post;
                $request->cookies = $request->hander->cookie;
                $request->files = $request->hander->files;
                $request->server = $request->hander->server;
                $request->header = $request->hander->header;
                $request->uri = $request->hander->server['request_uri'];
                $request->raw = $request->hander->rawContent();
                $response->hander = $sresponse;
                try{
                    $ctx = new Context($request, $response);
                    $res = \SilangPHP\Route::start($request->uri, $request->method, $ctx);
                }catch(\Exception $e)
                {
                    // $this->logger->error($e->getMessage());
                    $res = 404;
                }
                $response->end($res);
            });
        }elseif($run == 'stop'){
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
}