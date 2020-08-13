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
namespace SilangPHP;
/**
 * autoloader
 * Class Loader
 * @package SilangPHP
 */
Class Loader
{
    /**
     * 注册autoload
     */
    public static function register()
    {
        spl_autoload_register(array(Loader::class, 'loadByNamespace'));
    }

    /**
     * Load files by namespace.
     *
     * @param string $name
     * @return boolean
     */
    public static function loadByNamespace($name)
    {
        $_autoloadRootPath = __DIR__;
        $class_path = \str_replace('\\', \DIRECTORY_SEPARATOR, $name);
        if (\strpos($name, 'SilangPHP\\') === 0) {
            $class_file = __DIR__ . \substr($class_path, \strlen('SilangPHP')) . '.php';
        } else {
            if ($_autoloadRootPath) {
                $class_file = $_autoloadRootPath . \DIRECTORY_SEPARATOR . $class_path . '.php';
            }
            if (empty($class_file) || !\is_file($class_file)) {
                $class_file = __DIR__ . \DIRECTORY_SEPARATOR . '..' . \DIRECTORY_SEPARATOR . "$class_path.php";
            }
        }
        if (\is_file($class_file)) {
            require_once($class_file);
            if (\class_exists($name, false)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 框架类的映射
     */
    public static function map()
    {
        $map = [
            'SilangPHP\\SilangPHP' => 'SilangPHP.php',
            'SilangPHP\\Cache' => 'Cache.php',
            'SilangPHP\\Config' => 'Config.php',
            'SilangPHP\\Di' => 'Di.php',
        ];
        return $map;
    }
}