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

// abstract
class Controller
{
    public $is_ajax = false;
    public $request;
    public $response;
    public $middlewares = [];
    public $exceptAction = [];
    public $onlyAction = [];
    //默认使用的页数
    public function __construct()
    {
        $this->is_ajax = $this->is_ajax();
        $this->request = SilangPHP::$app->request;
        $this->response = SilangPHP::$app->response;
    }

    /**
     * 同个控制器，开始的时候调用
     */
    public function beforeAction($action = '')
    {
        return true;
    }

    /**
     * 同个控制器,end的时候调用
     */
    public function afterAction($action = '' , $result = '')
    {
        return true;
    }

    /**
     * 中间件
     */
    public function middleware()
    {
        // 系统级别的中间件
        $config = \SilangPHP\Config::get('Middleware');
        if($config)
        {
            foreach($config as $key=> $val)
            {
                $this->middlewares[] = $val;
            }
        }
        $this->middlewares = array_unique($this->middlewares);
        return $this->middlewares;
    }

    /**
     * 排除某方法之后调用中间件
     */
    public function except(Array $action = [])
    {
        $this->exceptAction = $action;
        return true;
    }

    /**
     * 只允许某方法调用中间件
     */
    public function only(Array $aciton)
    {
        $this->onlyAction = $aciton;
        return true;
    }

    /**
     * 判断是否ajax请求
     * @return bool
     */
    public function is_ajax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'])=='XMLHTTPREQUEST';
    }

    /**
     * 成功返回
     * @param string $msg
     * @return mixed
     */
    public function success($msg = '', $data = '')
    {
        return $this->response->json(0, $msg, $data);
    }

    /**
     * 失败返回
     * @param int $code
     * @param string $msg
     * @return mixed
     */
    public function fail($code = -1, $msg = '')
    {
        return $this->response->json($code, $msg);
    }

    /**
     * 默认生成swagger api文档
     * @return mixed
     */
    public function doc0221()
    {
        try{
            $openapi = \OpenApi\scan(PS_APP_PATH.'/Controller');
            $json_file = PS_ROOT_PATH.'/Public/docswagger.json';
            file_put_contents($json_file,$openapi->toJson());
            $this->response->send('写入docswagger.json成功');
            return true;
        }catch(\Exception $e)
        {
            $this->response->send('写入docswagger.json失败:'.$e->getMessage());
            return false;
        }
    }

}