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

use Exception;
use \SilangPHP\Exception\dbException;
use \SilangPHP\Config;
use SilangPHP\Facade;
use SilangPHP\SilangPHP;

/**
 * 最简单的mysql db类
 * 轻量，够快！
 */
class Mysql
{
    //数据库链接
    public $link;
    //慢查询时间
    private $late_time = 3;
    public function __construct($conn = 'master')
    {
        $this->connect($conn);
    }
    
    /**
     * 连接数据库
     */
    public function connect($conn = '')
    {
        if(empty($conn))
        {
            $conn = "master";
        }
        $config = \SilangPHP\Config::get("Db.mysql")[$conn];
        if(empty($config['charset']))
        {
            $charset = 'utf8';
        }else{
            $charset = $config['charset'];
        }
        $dsn    = 'mysql:dbname=' . $config['dbname'] . ';host=' . $config["host"] . ';port=' . $config['port'];
        $this->pdo = new \PDO($dsn, $config['username'], $config["password"],
            array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $charset
            ));
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, false);
        $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
    }

    /**
     * exec
     *When using PDO::EXEC the result returned is not of an PDOStatement but an integer of the rows affected.
     * @param string $sql
     */
    public function exec(string $sql = '')
    {
        $result = $this->pdo->exec($sql);
        return $result;
    }

    /**
     * 最后插入的id
     *
     * @return string
     */
    public function last_insert_id()
    {
        return $this->insert_id();
    }

    public function begin()
    {
        return $this->pdo->beginTransaction();
    }

    public function rollback()
    {
        return $this->pdo->rollBack();
    }

    public function commit()
    {
        return $this->pdo->commit();
    }

    /**
     * 获取错误提示
     *
     * @return string
     */
    public function error()
    {
        $error = '';
        if ($this->pdo) {
            $error = $this->pdo->errorInfo()[2];
        }
        return $error;
    }

    /**
     * 执行mysql处理
     * When using PDO::QUERY the result returned is a PDOStatement.
     * @param $sql
     */
    public function query(string $sql = '')
    {
        $statement = null;
        try{
            $starttime = microtime(true);
            try{
                $statement = $this->pdo->query($sql);
            }catch(Exception $e)
            {
                $message = $e->getMessage();
                throw new dbException(1,$message, $sql);
            }
            $endtime = microtime(true);
            $lasttime = $endtime - $starttime;
            if (!$statement) {
                //调试模式才能显示 查看语句的时效
                if(SilangPHP::$app->debug == 1)
                {
                    $this->logger->debug($sql."sql_time:".$lasttime);
                }
            }
            return $statement;
        }catch(dbException $e)
        {
            if(SilangPHP::$app->debug == 1)
            {
                Facade\Log::alert($e->getSql());
                // echo $e->getSql();
            }
        }
        return $statement;
    }

    public function close()
    {
        $this->pdo = null;
    }

    /**
     * 获取单个数据
     */
    public function get_one(string $sql = '')
    {
        $row = false;
        if(!strpos($sql,'limit'))
        {
            //$sql语句结尾不能有;号
            $sql = $sql." limit 1 ";
        }
        $result = $this->query($sql);
        if($result)
        {
            $row = $result->fetch(\PDO::FETCH_ASSOC);
        }
        return $row;
    }

    /**
     * 获取所有数据
     */
    public function get_all(string $sql = '')
    {
        $row = false;
        $result = $this->query($sql);
        if($result)
        {
            $row = $result->fetchAll(\PDO::FETCH_ASSOC);
        }
        return $row;
    }

    /**
     * 处理大数组
     * @param $sql
     * @return \Generator
     */
    public function get_big_all(string $sql = '')
    {
        $result = $this->query($sql);
        while($row = $result->fetch($result,\PDO::FETCH_ASSOC)) {
            yield $row;
        }
    }

    /**
     * 获取插入的id
     * @return int|string
     */
    public function insert_id()
    {
        return $this->pdo->lastInsertId();
    }

    public function insert($table, array $values, $fields = null)
    {
        if (!count($values)) {
            return false;
        }
        if (is_array($fields)) {
            $insert_sql = "INSERT INTO $table (".join(',', $fields).') VALUES ('.join(',', $values).')';
        } else {
            $insert_sql = "INSERT INTO $table VALUES (".join(',', $values).')';
        }
        return $this->exec($insert_sql);
    }
    

    public function update($table, array $fields, array $values, $where_condition = null)
    {
        $update_sql = "UPDATE $table SET ";
        if (count($fields) !== count($values)) {
            return false;
        }
        $i = 0;
        $update_values = array();
        foreach ($fields as $field) {
            $update_values[] = $field.' = '.$values[$i];
            ++$i;
        }
        $update_sql .= join(',', $update_values);
        if ($where_condition != null) {
            $update_sql .= " WHERE $where_condition";
        }
        return $this->exec($update_sql);
    }

    public function delete($table, $where_condition)
    {
        if ($where_condition) {
            return $this->exec("DELETE FROM $table WHERE $where_condition");
        }
        return $this->exec("DELETE FROM $table");
    }

}