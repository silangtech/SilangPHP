<?php
/**
 * 站点配置文件
 * Author:shengsheng
 */
return [
    //框架里的模式 [0 fpm];
    'mode' => 0,
    //绑定的主机地址
    'host' => '0.0.0.0',
    //启动的端口
    'port' => 8080,
    //进程数
    'count' => 4,
    //数据库池的数量
    'mysql_pool_num' => 5,
    'session_dir' => 'session',
    //cookie
    'cookie_domain' => '',
    //调试模式
    'debug' => 1,
    'debug_ip' => '119.129.115.225,172.20.0.1',
    'defaultController' => 'index',
    'defaultAction' => 'index',
];