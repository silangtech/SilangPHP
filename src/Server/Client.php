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

/**
 * rpc访问的客户端
 * Class client
 * @package SilangPHP\Server
 */
class Client extends \Swoole\Client{
    public $host = '127.0.0.1';
    public $port = 9501;
    public $status = false;
    public function __construct($type='tcp')
    {
        parent::__construct(SWOOLE_SOCK_TCP,SWOOLE_SOCK_SYNC);
        // 要访问的地址
        $this->status = @$this->connect($this->host, $this->port);
    }

    /**
     * 简单的应答
     * @param string $data
     * @return string
     */
    public function get($service, $action = '', $param = [])
    {
        $data = [
            'service' => $service,
            'action' => $action,
            'param' => $param
        ];
        $data = json_encode($data);
        if($this->status)
        {
            $this->send($data);
            $response = $this->recv();
            return $response;
        }
        return false;
    }

    /**
     * todo 让服务端运行task
     */
    public function task()
    {

    }

    /**
     * 结束调用
     */
    public function end()
    {
        $this->close();
    }
}