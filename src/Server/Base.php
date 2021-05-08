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
namespace SilangPHP\Server;

interface Base
{
    public function onStart(\Swoole\Server $server);

    public function onShutdown(\Swoole\Server $server);

    public function onWorkerStart(\Swoole\Server $server, $worker_id);

    public function onWorkerError(\Swoole\Server $serv, $worker_id, $worker_pid, $exit_code, $signal);

    public function onWorkerStop(\Swoole\Server $server, $worker_id);

    public function onManagerStart(\Swoole\Server $serv);

    public function onManagerStop(\Swoole\Server $serv);

    public function onTask(\Swoole\Server $serv, $task_id, $from_id, $data);

    public function onFinish(\Swoole\Server $serv, $task_id, $data);

    public function onClose(\Swoole\Server $server, $fd, $reactorId);

    public function onConnect(\Swoole\Server $server, $fd, $from_id);

    public function onReceive(\Swoole\Server $server, $fd, $reactor_id, $data);
}