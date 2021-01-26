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

// use SilangPHP\Db\Medoo;
use SilangPHP\Exception\dbException;
use Illuminate\Database\Eloquent\Model as Eloquent_Model;
use Illuminate\Database\Capsule\Manager as Capsule;

class Model extends Eloquent_Model
{
    //表格名
    public $table_name = "";
    //每页条数
    public $limit = 20;
    //指定数据库 又名connection
    public $database = 'master';
    public $connection_name = '';
    //指定数据库名
    public $db_name = '';
    //当前页数
    public $page = 1;
    //主键
    // primaryKey primary_key
    public $primaryKey = 'id';
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
            $this->connection_name = $this->database ?? $this->connection;
            $config = \SilangPHP\Config::get("Db.mysql")[$this->connection_name];
            $capsule = new Capsule;
            $db_arr = [
                'driver'    => $this->db_type ?? 'mysql',
                'host'      => $config['host'],
                'database'  => !empty($this->db_name)?$this->db_name:$config['dbname'],
                'username'  => $config['username'],
                'password'  => $config['password'],
                'charset'   => 'utf8',
                'collation' => 'utf8_general_ci',
                'prefix'    => '',
            ];
            $prikey = $this->primary_key ?? $this->primaryKey;
            $this->setKeyName($prikey);
            $capsule->addConnection($db_arr,$this->connection_name);
            $capsule->setAsGlobal();
            $capsule->bootEloquent();
            parent::__construct();
            $this->conn_status = true;
        }catch(dbException $e)
        {
            $this->conn_status = false;
            Facade\Log::alert("数据库链接失败".$e->getSql()."|".$e->getMessage());
//            echo $e->getSql();
            throw new \PDOException($e->getMessage());
        }
    }

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
        if(!empty($this->table))
        {
            $this->table_name = $this->table;
        }
        if(!empty($table_name))
        {
            $this->table_name = $table_name;
        }
        if($this->table_name === ""){
            $table_name = get_called_class();
            $table_name = str_replace(["mod_","Model"], "", $table_name);
            $this->table_name = $table_name;
        }
        $this->table = $this->table_name;
        return $this;
    }

    /**
     * 获取指定sql一条数据
     */
    public function get_sql_one($sql)
    {
        $data = Capsule::connection($this->connection_name)->selectOne($sql)->toArray();
        return $data;
    }

    /**
     * 获取指定sql所有数据
     */
    public function get_sql_all($sql)
    {
        $data = Capsule::connection($this->connection_name)->select($sql);
        $data = json_decode(json_encode($data), true);
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
        $tmp = self::where($where)->first($this->fields)->toArray();
        $this->fields = '*';
        return $tmp;
    }

    /**
     * 返回所有数据
     */
    public function get_all($where = [])
    {
        // $tmp = parent::select($this->table_name,$this->fields,$where);
        $tmp = self::where($where)->get($this->fields)->toArray();
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
        $total = self::where($where)->count();
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
        $tmp = self::insert($attrs);
        return $tmp;
    }

    /**
     * 更新数据
     * @param $attrs
     */
    public function update1($attrs,$where){
        //这个里where
        $data = self::where($where)->update($attrs);
        if($data == false)
        {
            return false;
        }
        return $data;
        // return  $data->rowCount();
    }

    /**
     * 删除数据
     * 只针对id处理
     * @param $id
     */
    public function delete1($id){
        parent::where(['id'=>$id])->delete();
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