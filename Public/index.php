<?php
/**
 * 普通模式下的入口
 */

// app地址
define("PS_APP_NAME",       "App");
define("PS_ROOT_PATH",       dirname(__FILE__)."/../");
define("PS_APP_PATH",        PS_ROOT_PATH.PS_APP_NAME);
define("PS_CONFIG_PATH",		PS_APP_PATH."/Config");
require_once(PS_APP_PATH."../vendor/autoload.php");

// 设置应用地址
\SilangPHP\SilangPHP::setAppDir(PS_APP_PATH);
// 运行框架
\SilangPHP\SilangPHP::run();