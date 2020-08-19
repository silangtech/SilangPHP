<?php
/**
 * 普通模式下的入口
 */
define("PS_ROOT_PATH",       dirname(dirname(__FILE__)));
require_once(PS_ROOT_PATH."/vendor/autoload.php");

$app = $_GET['app'] ?? 'App';
$apps = ['App','User'];
if(!in_array($app,$apps))
{
    return 'app error';
}

// 设置应用地址
\SilangPHP\SilangPHP::setAppDir(PS_ROOT_PATH.'/'.$app);
// 运行框架
\SilangPHP\SilangPHP::run();
