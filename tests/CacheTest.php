<?php
/**
 * 缓存测试
 * Author:shengsheng
 */
namespace App\tests;
require_once __DIR__ . '/../vendor/autoload.php';
define("ROOT_PATH", dirname(__DIR__) . "/");

use SilangPHP\Cache;

use PHPUnit\Framework\TestCase;


class CacheTest extends TestCase
{
    public function testCache()
    {
        Cache::set("testKey","testValue");
        $value = Cache::get("testKey");
        return $value;
    }
}