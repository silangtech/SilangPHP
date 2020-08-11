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
| Supports: http://www.github.com/phpsl/SilangPHP                       |
+-----------------------------------------------------------------------+
*/
namespace SilangPHP;
class Response
{
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
            'Access-Control-Allow-Headers' => 'Accept,AUTHORIZATION,DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Authorization',
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
     * 设置响应状态码
     */
    public static function code($type = "success")
    {
        $arr_type = [
            'error' => 500,
            //Unauthorized
            'unauth' => 401,
            //Forbidden
            'forbidden' => 403,
            //默认success
            'success' => 200,
            'notfound' => 404,
        ];
        if(!isset($arr_type[$type]))
        {
            $type = "success";
        }
        $code = $arr_type[$type];
        http_response_code($code);
    }

    /**
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
        echo $result;
    }

    /**
     * 向客服端写入内容
     */
    public static function write($result)
    {
        echo $result;
    }
    
    /**
     * 通用返回
     */
    public static function commonData($data,$page,$pageSize)
    {
        $rows['list'] = $data['list'];
        $rows['pagination'] = [
            'total' => (int)$data['total'],
            'pageSize' => (int)$pageSize,
            'current' => (int)$page,
        ];
        return $rows;
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
