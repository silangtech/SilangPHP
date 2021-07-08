<?php
namespace SilangPHP\Helper;

class authuser{
    public $userid = null;
    public $roleid = null;
    public $backendName = 'Admin/';
    //  \App\Model\Admin\AdminRoleModel
    public $roleModel = null;
    public function __construct($auth, $roleModel = null)
    {
        if(is_null($roleModel))
        {
            $roleModel = new \App\Model\Admin\AdminRoleModel();
        }
        $this->auth = $auth;
        if(isset($this->auth->userid))
        {
            $this->userid = $this->auth->userid;
        }
        if(isset($this->auth->roleid))
        {
            $this->roleid = $this->auth->roleid;
        }
        // 这里没考虑多角色的情况
        $this->policy = new Policy();
        if($this->roleid == '1')
        {
            //所有都允许
            $this->policy->add('/*', Policy::P_ALLOW);
        }else{
            $role = $roleModel::where('roleid', '=', $this->roleid)->first()->toArray();
            if($role)
            {
                $policy_data = json_decode($role['policy'],true);
                foreach($policy_data as $ct=>$acs)
                {
                    foreach($acs as $ac => $op)
                    {
                        $path = "{$ct}/{$ac}";
                        $this->policy->add($path,$op);
                    }
                }
            }
        }
    }
    /**
     * 判断是否管理员
     *
     * @return boolean
     */
    public function isAdmin()
    {
        if(isset($this->roleid) && $this->roleid == 1)
        {
            return true;
        }else{
            return false;
        }
    }

    /**

     * 检查权限
     * 暂时只检测ct和ac够了
     * @param $path
     * @return int
     */
    public function check($path = '')
    {
        if(empty($path))
        {
            $path = "/".\SilangPHP\SilangPHP::$app->ct."/".\SilangPHP\SilangPHP::$app->ac."/";
        }
        // 默认后台的命名
        $path = str_replace($this->backendName, '', $path);
        if($this->policy)
        {
            $auth = $this->policy->check($path);
            return $auth;
        }
        // 没查找出的都拒绝访问
        return Policy::P_DENY;
    }
}

/**
 * 后台权限判断
 */
class Auth{
    public static $auth = null;
    public static $user = null;

    /**
     * jwt访问判断
     * @param string $authorization
     * @return object | boolean
     */
    public static function user($authorization = '')
    {
        if(isset(\SilangPHP\SilangPHP::$app->request->server['authorization']))
        {
            $authorization = \SilangPHP\SilangPHP::$app->request->server['authorization'];
        }
        if(!empty($authorization))
        {
            $jwt = new \SilangPHP\Helper\Jwt();
            //验证authorization
            self::$auth = $jwt->decode($authorization);
        }
        self::$user = new authuser(self::$auth);
        return self::$user;
    }
}

?>