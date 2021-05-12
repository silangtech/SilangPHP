<?php
namespace SilangPHP\Helper;
/**
 * 权限控制
 * Author:shengsheng
 * Class Policy
 * --------------------------------------------------------
 * // 每个角色不同的Policy
 *   $policy = new Policy();
 *   $policy->add("/index/login/test2",Policy::P_ALLOW);
 *   $policy->add("/index/login/test3",Policy::P_ALLOW);
 *   $policy->add("/index/*",Policy::P_ALLOW); // 全选的状态
 *   $policy->add("/*",Policy::P_ALLOW);
 *   $policy->add("/index/login/test4",Policy::P_DENY);
 *   $check = $policy->check("/index");
 *   var_dump($check);
 */
class Policy{
    // 允许
    const P_ALLOW = 1;
    // 拒绝
    const P_DENY = 2;
    // 未知
    const P_UNKNOWN = 3;
    public $root;
    public function __construct($op = Policy::P_UNKNOWN)
    {
        $this->root = $this->node("*",$op);
    }

    /**
     * 节点
     * @param $path
     * @param $op
     */
    public function node($path,$op=Policy::P_UNKNOWN)
    {
        $node = new class{
            public $name = "";
            public $op = Policy::P_UNKNOWN;
            public $leaf = [];

            public function check($key)
            {
                if(isset($this->leaf[$key]))
                {
                    return true;
                }else{
                    return false;
                }
            }
        };
        $node->name = $path;
        if($op)
        {
            $node->op = $op;
        }
        return $node;
    }

    /**
     * 添加权限
     */
    public function add($path,$op = Policy::P_ALLOW)
    {
        $path = trim($path,"/");
        $path = explode("/",$path);
        if($path)
        {
            $temp = $this->root;
            foreach($path as $lowpath)
            {
                if(!isset($temp->leaf[$lowpath]))
                {
                    // 一路向下
                    $tmp = $this->node($lowpath,Policy::P_UNKNOWN);
                }else{
                    $tmp = $temp->leaf[$lowpath];
                }
                $temp->leaf[$lowpath] = $tmp;
                $temp = $tmp;
            }
            $temp->op = $op;
        }
    }

    /**
     * 检查权限
     */
    public function check($path,$parent_op = '')
    {
        $path = strtolower($path);
        $parentop = Policy::P_UNKNOWN;
        $path = explode("/",trim($path,"/"));
        $root = $this->root;
        while($leaf = array_shift($path))
        {
            // 同级情况
            if($root->check("*"))
            {
                if($root->leaf['*']->op != Policy::P_UNKNOWN)
                {
                    $parentop = $root->leaf['*']->op;
                }
            }
            if($root->check($leaf))
            {
                $root = $root->leaf[$leaf];
            }else{
                return $parentop;
            }
        }
        if($root->op == Policy::P_UNKNOWN)
        {
            return $parentop;
        }else{
            return $root->op;
        }
    }

}

?>