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
class Request
{
    // 用户的cookie
    public $cookies = [];

    // 把GET、POST的变量合并一块，相当于 _REQUEST 表单提交
    public $forms = [];

    // _GET 变量
    public $gets = [];

    // _POST 变量
    public $posts = [];

    public $header = [];

    public $server = [];

    public $method = 'GET';

    public $request;

    private $mode = 0;

    public function __construct()
    {
        if(SilangPHP::$mode == 0)
        {
            $this->posts = $_POST ?? [];
            $this->gets = $_GET ?? [];
            $this->server = $_SERVER ?? [];
            $this->cookies = $_COOKIE ?? [];
            $this->request = $_REQUEST ?? [];
        }
        // 跑取获得的header
        foreach ($_SERVER as $key => $val) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $key = substr($key, 5);
                $key = str_replace('_', ' ', $key);
                $key = str_replace(' ', '-', $key);
                $key = strtoupper($key);
                $this->header[$key] = $val;
            }
        }
        $this->method = $this->server['REQUEST_METHOD'];
    }

    public function isAjax()
    {
        return $this->header("X-Requested-With") === "XMLHttpRequest";
    }

    /**
     * 获得get表单值
     */
    public function get( $formname, $defaultvalue = '', $filter_type='' )
    {
        if( isset( $this->gets[$formname] ) ) {
            return $this->filter( $this->gets[$formname], $filter_type );
        } else {
            return $defaultvalue;
        }
    }

    /**
     * 获得post表单值
     */
    public function post( $formname, $defaultvalue = '', $filter_type='' )
    {
        if( isset( $this->posts[$formname] ) ) {
            return $this->filter( $this->posts[$formname], $filter_type );
        } else {
            return $defaultvalue;
        }
    }

    /**
     * 获得指定cookie值
     */
    public function cookie( $key, $defaultvalue = '', $filter_type='' )
    {
        if( isset( $this->cookies[$key] ) ) {
            return $this->filter( $this->cookies[$key], $filter_type);
        } else {
            $value = $defaultvalue;
        }
        return $value;
    }

    /**
     * 获得raw
     * postjson
     */
    public function getRaw()
    {
        if($this->mode == 0)
        {
            return file_get_contents("php://input");
        }
        return '';
    }

    /**
     * Alias getRaw
     * @return false|string
     */
    public function postjson()
    {
        return $this->getRaw();
    }

    /**
     * 获取所有
     * @param $formname
     * @param string $defaultvalue
     * @param string $filter_type
     * @return mixed|string
     */
    public function item($formname, $defaultvalue = '', $filter_type='')
    {
        if( isset( $this->posts[$formname] ) ) {
            return $this->filter($this->posts[$formname], $filter_type);
        }elseif( isset( $this->gets[$formname] ) ) {
            return $this->filter( $this->gets[$formname], $filter_type);
        }else{
            $value = $defaultvalue;
        }
        return $value;
    }

    /**
     * 强制转换类型
     * @param $value
     * @param string $type
     */
    public function filter($value,$type = '')
    {
        switch($type)
        {
            case 'int':
                $value = intval($value);
                break;
            case 'float':
                $value = floatval( $value );
                break;
            case 'array':
                $value = (array)$value;
                break;
            default:
                break;
        }
        return $value;
    }


}
