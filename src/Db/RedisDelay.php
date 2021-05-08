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
namespace SilangPHP\Db;
class RedisDelay
{
    protected $prefix = 'redisDelayQueue';
    protected $redis = null;
    protected $key = '';

    public function __construct($queue, $config = [])
    {
        $this->key = $this->prefix . $queue;
        $this->redis = new \Redis();
        $this->redis->connect($config['host'], $config['port'], $config['timeout']);
        if(!empty($config['auth']))
        {
            $this->redis->auth($config['auth']);
        }
        if(!empty($config['db']))
        {
            $this->redis->select($config['db']);
        }
    }

    public function delTask($value)
    {
        return $this->redis->zRem($this->key, $value);
    }

    public function getTask()
    {
        //获取任务，以0和当前时间为区间，返回一条记录
        return $this->redis->zRangeByScore($this->key, 0, time(), ['limit' => [0, 1]]);
    }

    public function addTask($name, $data, $time)
    {
        return $this->redis->zAdd(
            $this->key,
            $time,
            json_encode([
                'taskName' => $name,
                'taskTime' => $time,
                'taskParams' => $data,
            ], JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * 每次返回一个
     * @param callable $fun
     * @return bool
     */
    public function run(callable $fun = null)
    {
        $task = $this->getTask();
        if (empty($task)) {
            return false;
        }
        $task = $task[0];
        if ($this->delTask($task)) {
            $task = json_decode($task, true);
            // 处理任务
            $fun($task);
            return true;
        }
        return false;
    }
}