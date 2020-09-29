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

use SilangPHP\Db\Medoo;
use SilangPHP\Exception\dbException;

class Model extends Medoo implements \ArrayAccess, \JsonSerializable
{
    //表格名
    public $table_name = "";
    //每页条数
    public $limit = 20;
    //指定数据库 又名connection
    public $database = 'master';
    //指定数据库名
    public $db_name = '';
    //当前页数
    public $page = 1;
    //主键
    public $primary_key = 'id';
    //数据库类型，暂时只支持mysql
    public $db_type = 'mysql';
    //查询字段
    public $fields = '*';
    //表格数据
    public $attr;
    public $conn_status = false;

    public function __construct()
    {
        try{
            //自动效验表格名
            $this->table();
            $config = \SilangPHP\Config::get("Db.mysql")[$this->database];
            $options = [
                'database_type' => $this->db_type,  //'mysql',
                'database_name' => !empty($this->db_name)?$this->db_name:$config['dbname'],
                'server' => $config['host'],
                'username' => $config['username'],
                'password' => $config['password'],
                'charset' => 'utf8',
                'port' => $config['port'],
            ];
            parent::__construct($options);
            $this->conn_status = true;
        }catch(dbException $e)
        {
            $this->conn_status = false;
            Facade\Log::alert("数据库链接失败".$e->getSql());
//            echo $e->getSql();
        }
    }

    public function offsetExists($offset)
    {

    }

    public function offsetGet($offset)
    {

    }

    public function offsetSet($offset, $value)
    {

    }

    public function offsetUnset($offset)
    {

    }

    public function jsonSerialize()
    {

    }

//    public function create()
//    {
//        return new static();
//    }

    public function __set($key,$value)
    {
        $this->attr[$key] = $value;
    }

    public function __get($key)
    {
        return $this->attr[$key];
    }

    /**
     * 数据库名
     * @return string
     */
    public function table($table_name = ''){
        if(!empty($table_name))
        {
            $this->table_name = $table_name;
        }
        if($this->table_name === ""){
            $table_name = get_called_class();
            $table_name = str_replace(["mod_","Model"],"",$table_name);
            $this->table_name = $table_name;
        }
        return $this;
    }

    /**
     * 获取指定sql一条数据
     */
    public function get_sql_one($sql)
    {
        $data = $this->query($sql)->fetch(\PDO::FETCH_ASSOC);
        return $data;
    }

    /**
     * 获取指定sql所有数据
     */
    public function get_sql_all($sql)
    {
        $data = $this->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        return $data;
    }

    /**
     * 指定字段
     * @param string $fields
     * @return $this
     */
    public function field($fields = '*')
    {

        $this->fields = $fields;
        $this->fields = explode(",",$this->fields);
        if(count($this->fields) == 1)
        {
            $this->fields = $fields;
        }
        return $this;
    }

    /**
     * get_one
     */
    public function get_one($where = [])
    {
        $tmp = parent::get($this->table_name,$this->fields,$where);
        $this->fields = '*';
        return $tmp;
    }

    /**
     * 返回所有数据
     */
    public function get_all($where = [])
    {
        $tmp = parent::select($this->table_name,$this->fields,$where);
        $this->fields = '*';
        return $tmp;
    }

    /**
     * 列出列表
     */
    public function list($where = [])
    {
        $limit = [($this->page-1) * $this->limit,$this->limit];
        $where['LIMIT'] = $limit;
        $data = $this->get_all($where);
        unset($where['LIMIT']);
        $total = $this->count($this->table_name,$where);
        return [
            'list' => $data,
            'total' => $total
        ];
    }

    /**
     * 插入新数据
     * @param $attrs
     */
    public function insert1($attrs = '')
    {
        if(empty($attrs) && !empty($this->attr) )
        {
            $attrs = $this->attr;
        }
//        $tmp = parent::debug()->insert($this->table_name,$attrs);
        $tmp = parent::insert($this->table_name,$attrs);
        if($this->error()['0'] != '00000')
        {
            Facade\Log::alert(json_encode($this->error()));
            //debug下打印一下
            return false;
        }else{
            $insert_id = $this->id();
        }
        return $insert_id;
    }

    /**
     * 更新数据
     * @param $attrs
     */
    public function update1($attrs,$where){
        //这个里where
        $data = parent::update($this->table_name,$attrs,$where);
        return  $data->rowCount();
    }

    /**
     * 删除数据
     * 只针对id处理
     * @param $id
     */
    public function delete1($id){
        parent::delete($this->table_name,['id'=>$id]);
    }

    /**
     * 解释排序字段
     * game_id|ascend  字段|升降  ascend descend
     */
    public function orderField($sort_field = '')
    {
        $sort_field = explode("_",$sort_field);
        if(empty($sort_field) || !isset($sort_field['1']))
        {
            return '';
        }
        if($sort_field['1'] == 'ascend')
        {
            $sort_type = 'ASC';
        }else{
            $sort_type = 'DESC';
        }
        $order[$sort_field['0']] = $sort_type;
        return $order;
    }

}