<?php
/**
 * 普通模式下的入口
 */

define("PS_ROOT_PATH",       dirname(dirname(__FILE__)));
require_once(PS_ROOT_PATH."/vendor/autoload.php");

// 设置应用地址
\SilangPHP\SilangPHP::setAppDir(PS_ROOT_PATH.'/App');
// 运行框架
\SilangPHP\SilangPHP::run();
