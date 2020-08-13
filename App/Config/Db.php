<?php
/**
 * 数据库配置文件
 * Author:shengsheng
 */
return [
    'mysql' => [
        //相当于default
        'master' => [
            'host' => '172.20.0.77',
            'port' => '3306',
            'dbname' => 'phpshow',
            'username' => 'root',
            'password' => 'root',
        ],
    ],
    'redis' => [
        'master' => [
            'host' => '172.20.0.5',
            'port' => '6379',
            'db' => '0',
            'auth' => '',
        ]
    ],
];