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
declare(strict_types = 1);
namespace SilangPHP;

Class Context
{
    public $id;
    public $request;
    public $response;
    public $route;
    public $hander;
    public $vars;

    public function __construct(\SilangPHP\Request $request, \SilangPHP\Response $response, $id = ''){
        $this->request = $request;
        $this->response = $response;
        $this->id = $id;
    }

    /**
     * 获取用户IP
     */
    public function ClientIP()
    {
        if(isset($this->request->header['x-real-ip']))
        {
            return $this->request->header['x-real-ip'];
        }
        if( isset($this->request->header['x-forwarded-for']) )
        {
            $arr = explode(',', $this->request->header['x-forwarded-for']);
            foreach ($arr as $ip)
            {
                $ip = trim($ip);
                if ($ip != 'unknown' ) {
                    $client_ip = $ip; break;
                }
            }
        }
        else
        {
            $client_ip = isset($this->request->server['remote_addr']) ? $this->request->server['remote_addr'] : '';
        }
        preg_match("/[\d\.]{7,15}/", $client_ip, $onlineip);
        $client_ip = ! empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
        return $client_ip;
    }

    /**
     * c.HTML(http.StatusOK, "index.html", gin.H{"title": "我是测试", "ce": "123456"})
     *
     * @return void
     */
    public function HTML($httpcode = 200, $file = '', $params = [])
    {
        $this->response->withStatus($httpcode, '');
        \extract($params);
        \ob_start();
        try {
            // include PS_APP_PATH.'/View/'.$file_name.".php";
            include $file;
            // ob_flush();
        } catch (\Throwable $e) {
            echo $e;
        }
        $data = \ob_get_clean();
        $this->response->end($data); 
    }

    /**
     * json格式化
     */
    public function JSON($httpcode = 200, $data = [])
    {
        $this->response->withStatus($httpcode, '');
        $data = json_encode($data, \JSON_UNESCAPED_UNICODE);
        $this->response->end($data);
    }

    public function success($message = '', $data = [])
    {
        return $this->JSON(200, ['code' => 0, 'message' => $message, 'data' => $data]);
    }

    public function fail($code = -1, $message = '', $data = [])
    {
        return $this->JSON(200, ['code' => $code, 'message' => $message, 'data' => $data]);
    }

    /**
     * 输出string
     */
    public function String($httpcode = 200, $str = '')
    {
        $this->response->withStatus($httpcode, '');
        $this->response->end($str);
    }

    /**
     * 输出XML
     */
    public function XML($httpcode = 200, $data = [])
    {

    }

    /**
     * 输出yaml
     */
    public function YAML($httpcode = 200, $data = [])
    {

    }


}