<?php
namespace SilangPHP\Helper;

/**
 * 分页辅助类
 */
class Paginator{
    
    public $currentPage = 1;
    public $pageSize = 25;
    public $param = [];
    public $fields = "*";
    public $joins = [];
    public $order = ['id', 'desc'];
    /**
     * Undocumented function
     *
     * @param [type] $model
     * @param [type] $param
     * @param array $fields
     */
    public function __construct($model, $param, $fields = ['*'])
    {
        $this->model = $model;
        // $this->param = $param;
        if(isset($param['currentPage']))
        {
            $this->currentPage = $param['currentPage'];
        }
        if(isset($param['pageSize']))
        {
            $this->pageSize = $param['pageSize'];
        }
        
        if(isset($param['paramdata']))
        {
            $this->param = $param['paramdata'];
        }
        $this->model = $this->model::select($fields);
        if($this->param)
        {
            foreach($this->param as $wherefield)
            {
                // call_user_func_array([$this->model,'where'], $wherefield);
                $this->model = $this->model->where($this->param);
            }
            // $this->model = $this->model;
        }
    }

    public function setLeftJoin($table, $key1, $op = '=', $key2)
    {
        $this->model = $this->model->leftJoin($table, $key1, $op, $key2);
    }

    /**
     * 获取结果
     */
    public function getResult()
    {
        $offset = ($this->currentPage -1) * $this->pageSize;
        return $this->model->offset($offset)->limit($this->pageSize)->get();
    }

    /**
     * 获取总数
     *
     * @return void
     */
    public function getTotal()
    {
        $count = $this->model->count();
        return $count;
    }

    /**
     * 获取列表
     */
    public function getList()
    {
        $this->model = $this->model->orderBy($this->order[0], $this->order[1]);
        $data['total'] = $this->getTotal();
        $data['list'] = $this->getResult();
        if($data['list'])
        {
            $data['list'] = $data['list']->toArray();
        }
        return $data;
    }

}

?>