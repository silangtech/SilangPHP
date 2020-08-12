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
namespace SilangPHP;
class Response
{

    public static $header = [];
    public static $body = '';
    // 状态码一般正确返回是200
    public static $status = 200;
    public static $httpCode = [
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
    public static $connection = null;
    public static $hander = null;
    public static $cors = [];
    public static function setConnection($connection)
    {
        self::$connection = $connection;
    }

    /**
     * cors跨域请求
     */
    public static function setCors($host = '*')
    {
        self::$cors = [
            'Access-Control-Allow-Origin' => $host,
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            // 'Access-Control-Allow-Headers' => '*',
            // uc浏览器windows版如果Access-Control-Allow-Headers 使用 * 是有问题的
            'Access-Control-Allow-Headers' => 'Accept,AUTHORIZATION,DNT,X-Token,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Authorization',
        ];
    }

    /**
     * 输出头部信息
     */
    public static function Header($data)
    {
        if(empty($data))
        {
            $data = 'Content-Type: text/html; charset=utf-8';
        }
        if(self::$connection)
        {
            // 兼容workerman 3.x
            if(method_exists("\Workerman\Protocols\Http","header"))
            {
                \Workerman\Protocols\Http::header($data);
            }else{
                // 4.x使用新方式 数组形式
                self::$connection->send(new \Workerman\Protocols\Http\Response(302, $data));
            }
            
        }else{
            header($data);
        }
    }

    /**
     * toJson
     */
    public static function toJson($result,$param = '')
    {
        return json_encode($result);
    }

    /**
     * headers
     * 输出json
     */
    public static function json($code=0,$msg='',$data='')
    {
        $result = self::returnArray($code,$msg,$data);
        return json_encode($result);
    }

    /**
     * 输出结尾
     */
    public static function end($result)
    {
        // 输出header与body
        return $result;
    }

    /**
     * 向客服端写入内容
     */
    public static function write($result)
    {
        self::$body .= $result;
    }

    /**
     * 返回array
     * @param string $code
     * @param string $msg
     * @param string $data
     */
    public static function returnArray($code='0',$msg='',$data='')
    {
        $result = array(
            'code' => $code,
            'message' => $msg,
            'data' => $data,
        );
        return $result;
    }

    /**
     * 输出xml
     */
    public static function xml($data)
    {
        //构造xml数据格式
        $xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        $xml .= "<data>\n";
        foreach ($data as $item_arr) {
            $item = "<item>\n";
            foreach($item_arr as $ikey=>$ival)
            {
                //循环构造xml单项
                $item .= "<{$ikey}>" . $ival . "</{$ikey}>\n";
            }
            $item .= "</item>\n";
            $xml .=$item;
        }
        $xml .= "</data>";

        return $xml;
    }

    /**
     * json to array
     */
    public static function jta($data)
    {
        $data = json_decode($data,true);
        echo "====================\n";
        echo "array(\n";
        foreach($data as $key=>$val)
        {
            self::JsonToArr($val,"\$arr['$key']");
        }
        echo ")";
    }

    /**
     * json to config
     * @param $a
     */
    public static function jtoc($data)
    {
        foreach($data as $key=>$val)
        {
            self::JsonToArr($val,"\$arr[$key]");
        }
    }
    /**
     * 数组配置文件式
     */
    public static function configArr($arr,$e='')
    {
        if(is_array($arr))
        {
            foreach($arr as $k=>$v)
            {
                $kk = $e."['".$k."']";
                self::configArr($v,$kk);
            }
        }else{
            echo $e."=\"".$arr."\";\n";
        }
    }
    /**
     * 一行输出数组
     */
    public static function lineArr($arr)
    {
        if(is_array($arr))
        {
            echo "array(";
            foreach($arr as $k=>$v)
            {
                if(!is_array($v))
                {
                    echo "\"{$k}\"=>\"{$v}\",";
                }
                self::lineArr($v);
            }
            echo "),\n";
        }
    }
    /**
     * json to array
     */
    public static function JsonToArr($arr,$i=1)
    {
        $str = str_repeat(" ",$i);
        $str2 = str_repeat(" ",$i+1);
        if(is_array($arr))
        {
            echo "\n$str array(\n";
            foreach($arr as $k=>$v)
            {
                echo $str2.'"'.$k."\"=>";
                if(is_array($v))
                {
                    $j = $i+2;
                }else{
                    $j = $i;
                }
                self::JsonToArr($v,$j);
            }
            echo $str."),\n";
        }else{
            echo '"'.$arr.'",'."\n";
        }
    }

}
