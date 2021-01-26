<?php
/**
 * 站点配置文件
 * 此文件可以删除，默认框架有判断
 * Author:shengsheng
 */
return [
    // 框架里的模式 [0 fpm];
    'mode' => 0,
    // 'routemode' => 2,
    // cookie
    'cookie_domain' => '',
    // 调试模式
    'debug' => 1,
    'debug_ip' => '',
    'cacheType' => 'file', // [ file | redis ]
    'defaultController' => 'index',
    'defaultAction' => 'index',

    'host' => '0.0.0.0',
    //启动的端口
    'port' => 8080,
    //进程数
    'count' => 4,
];