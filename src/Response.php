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
class Response
{
    public $header = [];
    public $body = '';
    public $status = 200;
    public $protocol = '1.1';
    public $hander = null;
    public $reasonPhrase;
    public $cors;
    /**
     * cors跨域请求
     */
    public function setCors($host = '*')
    {
        $this->cors = [
            'Access-Control-Allow-Origin' => $host,
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            // uc浏览器windows版如果Access-Control-Allow-Headers 使用 * 是有问题的
            'Access-Control-Allow-Headers' => 'Accept,AUTHORIZATION,DNT,X-Token,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Authorization',
            // 'Access-Control-Allow-Headers' => '*',
        ];
        if($host != '*')
        {
            $this->cors['Access-Control-Allow-Credentials'] = "true";
        }else{
            unset($this->cors['Access-Control-Allow-Credentials']);
        }

        // 直接设置header
        foreach($this->cors as $key=>$val)
        {
            $this->withHeader($key, $val);
        }
    }

    /**
     * 立即输出
     * 设置头部
     */
    public function header($key, $value = '')
    {
        if(is_object($this->hander))
        {
            if(method_exists($this->hander, 'header'))
            {
                $this->hander->header($key, $value);
                return ;
            }
        }
        header($key.":".$value);
    }

    /**
     * 输出并结束
     */
    public function end($data = '')
    {
        if(is_object($this->hander))
        {
            if(method_exists($this->hander, 'end'))
            {
                $this->hander->end($data);
            }
            return ;
        }
        // 输出header与body
        echo $data;exit();
    }

    public function redirect($url, $code = 302){
        if(is_object($this->hander))
        {
            if(method_exists($this->hander, 'redirect'))
            {
                $this->hander->redirect($url, $code);
            }
            return ;
        }
        header('Location:'.$url);exit();
    }

    /**
     * 向客服端写入内容
     * 分段写入
     */
    public function write($data = '')
    {
        if(is_object($this->hander))
        {
            if(method_exists($this->hander, 'write'))
            {
                $this->hander->write($data);
            }
            return ;
        }
        echo $data;
    }

    public function getProtocolVersion()
    {
        return $this->protocol;
    }

    public function withProtocolVersion($version)
    {
        $this->protocol = $version;
    }

    public function getHeaders()
    {
        return $this->header;
    }

    public function hasHeader($name)
    {
        return isset($this->header[$name]);
    }

    public function getHeader($name)
    {
        return $this->header[$name];
    }

    public function withHeader($name, $value)
    {
        $this->header[$name] = $value;
        $this->header($name, $value);
    }

    public function withAddedHeader($name, $value)
    {
        $this->header[$name] .= $value;
    }

    public function withoutHeader($name)
    {
        unset($this->header[$name]);
        return true;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function withBody($body)
    {
        $this->body = $body;
    }

    public function getStatusCode()
    {
        return $this->status;
    }

    public function withStatus($code, $reasonPhrase = '')
    {

        if(is_object($this->hander))
        {
            if(method_exists($this->hander, 'status'))
            {
                $this->hander->status($code, $reasonPhrase);
            }
            return ;
        }
        $this->status = $code;
        $this->reasonPhrase = $reasonPhrase;
        http_response_code($code);
    }

    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }
}
