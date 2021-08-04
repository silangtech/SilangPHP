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
namespace SilangPHP\Db;
use SilangPHP\Db\Db;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

/**
 * Class Db
 * laravel database
 * @package Db
 */
class Dbinit
{
    public static $db;
    public static $logger;
    public static function init($debug = 0)
    {
        if($debug)
        {
            self::$logger = new \SilangPHP\Log("sql");
        }
        $dbconfig = \SilangPHP\Config::get("Db.mysql");
        if($dbconfig)
        {
            self::$db = new Db;
            foreach($dbconfig as $connection_name => $config)
            {
                $db_arr = [
                    'driver'    => $config['dbtype'] ?? 'mysql', 
                    'host'      => $config['host'],
                    'port'      => $config['port'],
                    'database'  => $config['dbname'],
                    'username'  => $config['username'],
                    'password'  => $config['password'],
                    'charset'   => $config['charset'] ?? 'utf8',
                    'collation' => $config['collation'] ?? 'utf8_general_ci',
                    'prefix'    => '',
                ];
                self::$db->addConnection($db_arr, $connection_name);
            }
            self::$db->setEventDispatcher(new Dispatcher(new Container));
            self::$db->setFetchMode(\PDO::FETCH_ASSOC);
            self::$db->setAsGlobal();
            self::$db->bootEloquent();
            if($debug)
            {
                foreach($dbconfig as $connection_name => $config)
                {
                    // 一个做监控listen
                    self::$db::Connection($connection_name)->listen(function($query){
                    // Db::Connection('data')->listen(function($query){
                        $sql = $query->sql;
                        $bingings = $query->bindings;          
                        $time = $query->time / 1000;
                        $sqlStr = str_replace(array('?'), array("'_binging_v_'"), $sql);
                        foreach($bingings as $v){
                            $sqlStr = preg_replace('/_binging_v_/', strval($v), $sqlStr, 1);
                        }
                        self::$logger->debug($sqlStr . "; time({$time} s)" . "|" . time());

                    });
                    break;
                }
            }
        }
        return self::$db;
    }
}