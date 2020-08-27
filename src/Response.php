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
    // 状态码一般正确返回是200
    public $status = 200;
    public $httpCode = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required'
    ];
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
        ];

        // 直接设置header
        foreach($this->cors as $key=>$val)
        {
            header($key.":".$val);
        }
    }

    /**
     * toJson
     */
    public function toJson($result,$param = '')
    {
        return json_encode($result);
    }

    /**
     * headers
     * 输出json
     */
    public function json($code=0,$msg='',$data='',$jsontype = JSON_UNESCAPED_UNICODE)
    {
        $result = $this->returnArray($code,$msg,$data);
        return json_encode($result,$jsontype);
    }

    /**
     * 输出结尾
     */
    public function end($result = '')
    {
        // 输出header与body
        echo $result;
    }

    /**
     * 向客服端写入内容
     */
    public function write($result)
    {
        $this->body .= $result;
    }

    /**
     * 发送数据
     * @param string $data
     */
    public function send($data = '')
    {
        if($data)
        {
            echo $data;
        }else{
            echo $this->body;
        }
    }

    /**
     * 返回array
     * @param string $code
     * @param string $msg
     * @param string $data
     */
    public function returnArray($code='0',$msg='',$data='')
    {
        $result = array(
            'code' => $code,
            'message' => $msg,
            'data' => $data,
        );
        return $result;
    }

}
