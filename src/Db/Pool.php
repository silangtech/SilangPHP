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
/**
 * 连接池
 * 类型 mysql|redis
 * 定时更新与释放
 */
class Pool
{
    protected $pool;
    public $type;
    /**
     * MysqlPool constructor.
     * @param int $size 连接池的尺寸
     */
    function __construct($size = 20 , $type='mysql')
    {
        $this->type == $type;
        $this->pool = new Class{
            private $stack = [];
            private $top = -1;
            /**
             * 入栈
             */
            public function push($data)
            {
                $this->top = ++$this->top;
                $this->stack[$this->top] = $data;
            }
            /**
             * 出栈
             */
            public function pop()
            {
                if($this->top == -1){
                    return false;
                }
                $tmp = $this->stack[$this->top];
                $this->top = --$this->top;
                return $tmp;

            }
        };

        // 生成池中内容
        for ($i = 0; $i < $size; $i++)
        {
            if($type == 'mysql')
            {
                $middle = new \SilangPHP\Db\Mysql();
            }elseif($type == 'redis')
            {
                $middle = new \SilangPHP\Cache\Redis();
            }
            if($middle)
            {
                $this->put($middle);
            }
        }
    }

    /**
     * 添加
     * @param $middle
     */
    function put($middle)
    {
        $this->pool->push($middle);
    }

    /**
     * 获取
     * @return bool|mixed
     */
    function get()
    {
        return $this->pool->pop();
    }
}

