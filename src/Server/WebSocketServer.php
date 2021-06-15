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
namespace SilangPHP\Server;
Class WebSocketServer extends \Swoole\WebSocket\Server
{
    public $serv = null;
    public $worker_num = 2;
    public $user = 'www-data';
    public $group = 'www-data';
    // 测试的时候设为false
    public $daemonize = false;
    public $backlog = 128;
    // 异步耗时处理数
    public $task_worker_num = 4;
    public $tmp_path = '/tmp';
    public $pid_file;
    public $log_file;
    public $host = '0.0.0.0';
    public $port = 9501;
    public $processName = 'SilangPHP_WsServer';

    public $service = [];

    public function __construct($serverName = '')
    {
        if(defined("PS_RUNTIME_PATH"))
        {
            $this->tmp_path = PS_RUNTIME_PATH;
        }else{
            $this->tmp_path = "/tmp/";
        }
        $this->pid_file = $this->tmp_path.'server'.$this->port.'.pid';
        $this->log_file = $this->tmp_path.'swoole'.$this->port.'.log';
        $this->processName .= $serverName;
    }

    /**
     * server配置
     */
    public function config()
    {
        parent::__construct($this->host, $this->port);
        $this->set([
            'worker_num' => $this->worker_num,
            'user' => $this->user,
            'group' => $this->group,
            'daemonize' => $this->daemonize,
            'backlog' => $this->backlog,
            'pid_file' => $this->pid_file,
            'log_file' => $this->log_file
        ]);
    }


    /**
     * 设置好事件
     */
    public function event()
    {
        // 事件
        $this->on('Start', array($this, 'onStart'));
        $this->on('WorkerStart', array($this, 'onWorkerStart'));
        $this->on('ManagerStart', array($this, 'onManagerStart'));

        $this->on('Open', array($this, 'onOpen'));
        $this->on('Message', array($this, 'onMessage'));
        $this->on('Close', array($this, 'onClose'));
        // http功能暂时不增加
        // $this->on('Request', array($this, 'onRequest'));
    }

    /**
     * 开始运行
     */
    public function run()
    {
        $this->config();
        $this->event();
        $this->start();
        return true;
    }

    /**
     * 停止服务
     */
    public function stoppid()
    {
        // 获取pid,然后清空pid
        echo "[server_pid]".$this->pid_file.PHP_EOL;
        $pid = file_get_contents($this->pid_file);
        if($pid)
        {
            \Swoole\Process::kill((int)$pid, SIGTERM);
        }
        $pid = file_put_contents($this->pid_file,"");
        echo '停止成功'.PHP_EOL;
    }

    /**
     * 重新启动服务
     */
    public function restartpid()
    {
        $this->stoppid();
        $this->run();
    }

     /**
     * 开始进程
     * @param \Swoole\Server $server
     */
    public function onStart(\Swoole\Server $server)
    {
        swoole_set_process_name($this->processName);
    }

    public function onWorkerStart(\Swoole\Server $server, $worker_id)
    {
        swoole_set_process_name($this->processName."_worker");
    }

    public function onManagerStart(\Swoole\Server $serv)
    {
        swoole_set_process_name($this->processName."_manager");
    }

    function onOpen(\Swoole\WebSocket\Server $server, $request) {
        echo "server: handshake success with fd{$request->fd}\n";
    }

    function onMessage(\Swoole\WebSocket\Server $server, $frame) {
        echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
        $server->push($frame->fd, "this is server");
    }

    function onClose($ser, $fd) {
        echo "client {$fd} closed\n";
    }

    function onRequest(\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
        global $server;//调用外部的server
        // $server->connections 遍历所有websocket连接用户的fd，给所有用户推送
        foreach ($server->connections as $fd) {
            // 需要先判断是否是正确的websocket连接，否则有可能会push失败
            if ($server->isEstablished($fd)) {
                $server->push($fd, $request->get['message']);
            }
        }
    }
}