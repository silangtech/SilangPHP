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

    // _GET 变量
    public $gets = [];

    // _POST 变量
    public $posts = [];

    public $header = [];

    public $server = [];

    public $method = 'GET';

    public $files;

    public $raw = '';

    public $request;
    
    public $validator = null;

    public $hander = null;

    public $uri_target = '';

    public $uri = '';

    public function __construct()
    {
        if(SilangPHP::$httpmode == 0)
        {
            $this->posts = $_POST ?? [];
            $this->gets = $_GET ?? [];
            // $this->server = $_SERVER ?? [];
            $this->cookies = $_COOKIE ?? [];
            $this->request = $_REQUEST ?? [];
            $this->raw = file_get_contents("php://input") ?? '';
            $this->withMethod($_SERVER['REQUEST_METHOD']);
            $this->withUri($_SERVER["REQUEST_URI"]);
            $this->withRequestTarget($_SERVER['REQUEST_URI']);
            // 跑取获得的header
            foreach ($_SERVER as $key => $val) {
                $this->server[strtolower($key)] = $val;
                if (substr($key, 0, 5) === 'HTTP_') {
                    $key = substr($key, 5);
                    $key = str_replace('_', ' ', $key);
                    $key = str_replace(' ', '-', $key);
                    $key = strtolower($key);
                    $this->header[$key] = $val;
                }
            }
        }
    }

    public function isAjax()
    {
        return $this->header["X-Requested-With"] === "XMLHttpRequest";
    }

    public function getRequestTarget()
    {
        return $this->uri_target;
    }
    
    public function withRequestTarget($requestTarget)
    {
        $this->uri_target = $requestTarget;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function withMethod($method)
    {
        $this->method = $method;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function withUri($uri, $preserveHost = false)
    {
        $this->uri = $uri;
    }

    public function getUploadedFiles()
    {
        
    }

    /**
     * 校验数据
     * 默认返回错误码为11000
     * 
     * @param array $input
     * @param [type] $rules
     * @param integer $erorcode
     * @return void
     */
    public function validate($input = [], array $rules = [] , $message = [], $code = 11000)
    {
        if(empty($input))
        {
            $input = [];
        }
        
        $messages = [
            'required'       => 'The :attribute field is required.',
            'email.required' => '我们需要知道你的email地址',
            'same'           => 'The :attribute and :other must match.',
            'size'           => 'The :attribute must be exactly :size.',
            'between'        => 'The :attribute value :input is not between :min - :max.',
            'in'             => 'The :attribute must be one of the following types: :values',
        ];

        foreach($message as $key => $val)
        {
            $messages[$key] = $val;
        }
        // $translationPath = PS_RUNTIME_PATH.'/lang';
        if(empty($this->validator))
        {
            $translationLocale = 'zh';
            $translationPath = '';
            $transFileLoader = new \Illuminate\Translation\FileLoader(new \Illuminate\Filesystem\Filesystem, $translationPath);
            $translator = new \Illuminate\Translation\Translator($transFileLoader, $translationLocale);
            $this->validator = new \Illuminate\Validation\Factory($translator);
        }
        // $validator = \Illuminate\Support\Facades\Validator::make($input, $rules, $message);
        $validator = $this->validator->make($input, $rules, $messages);
        if ($validator->fails()) {
            $message = $validator->messages();
            // $errors = $validator->errors();
            foreach($message->all() as $mess)
            {
                $messagefirst = $mess;
                break;
            }
            $fail =  \SilangPHP\SilangPHP::$app->response->json($code, 'error', $messagefirst);
            throw new \Exception($fail, $code);
        }
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
        return $this->raw;
    }

    /**
     * Alias getRaw
     * @return false|string
     */
    public function postjson()
    {
        $data = $this->getRaw();
        if($data)
        {
            $data = json_decode($data,true);
        }
        return $data;
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
