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
| Supports: http://www.github.com/phpsl/SilangPHP                       |
+-----------------------------------------------------------------------+
*/
namespace SilangPHP;

date_default_timezone_set('Asia/Shanghai');

/**
 * Class SilangPHP
 * @package SilangPHP
 */
final Class SilangPHP
{
    const VERSION = '1.0.0';
    public $appDir;
    public $ct;
    public $ac;
    public $pathInfo;
    // 内存里的缓存
    public $cache = [];
    /**
     * 设置程序目录
     * @param $path
     */
    public static function setAppDir($path)
    {
        self::$appDir = $path;
    }

    /**
     * 运行程序
     */
    public static function run()
    {

    }
}