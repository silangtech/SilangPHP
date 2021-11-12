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
| Copyright (C) 2021. All Rights Reserved.                              |
+-----------------------------------------------------------------------+
| Supports: http://www.github.com/silangtech/SilangPHP                  |
+-----------------------------------------------------------------------+
*/
namespace SilangPHP;
/**
 * 进程处理
 */
class Process
{
    public $input;
    public $daemonize = true;
    public $worker_num = 1;
    public $workers = [];
    public $masterId = '';
    public $processName = "";
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
     * 用户逻辑
     */
    public function process()
    {
        $process = new \Swoole\Process(function (\Swoole\Process $worker) {
            \Swoole\Timer::tick(1000, function() use($worker){
                $this->checkMasterPid($worker);
                echo 'SilangPHP';
            }); 
        }, false, 0);
        $process->name("subProcess");
        $pid = $process->start();
        // 把pid加入
        $this->workers[] = $pid;
    }

    public function run()
    {
        if($this->daemonize)
        {
            \Swoole\Process::daemon();
        }
        $this->masterId = getmypid();
        echo "parent_id:".$this->masterId."\n";
        file_put_contents($this->pid_file,$this->masterId);
        cli_set_process_title($this->processName);
        // 运行里面的逻辑
        $this->process();
        $this->processWait();
    }

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

    public function checkMasterPid(\Swoole\Process &$worker)
	{
        // 【$signo=0，可以检测进程是否存在，不会发送信号】
		if (!\Swoole\Process::kill((int)$this->masterId, 0)) {
            echo '父进程不在了';
			$worker->exit(0);
		}
	}

    /**
	 * 处理僵尸进程，并重启进程
	 * @throws Exception
	 */
	public function processWait()
	{
		while (true) {
			if (count($this->workers)) {
				$ret = \Swoole\Process::wait();
				if ($ret) {
					// $this->rebootProcess($ret);
				}
			} else {
				break;
			}
		}
    }
}