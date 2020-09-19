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

abstract class Controller
{
    public $is_ajax = false;
    public $request;
    public $response;
    //默认使用的页数
    public function __construct()
    {
        $this->is_ajax = $this->is_ajax();
        $this->request = SilangPHP::$request;
        $this->response = SilangPHP::$response;
//        $this->request = Di::instance()->get(\SilangPHP\Request::class);
//        $this->response = Di::instance()->get(\SilangPHP\Response::class);
    }

    /**
     * 同个控制器，开始的时候调用
     */
    public function beforeAction()
    {
        return true;
    }

    /**
     * 同个控制器,end的时候调用
     */
    public function afterAction()
    {
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
    public function success($msg = '',$data = '')
    {
        return $this->response->json(0,$msg,$data);
    }

    /**
     * 失败返回
     * @param int $code
     * @param string $msg
     * @return mixed
     */
    public function fail($code = -1,$msg = '')
    {
        return $this->response->json($code,$msg);
    }

}