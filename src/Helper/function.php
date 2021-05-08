<?php
// urlDecode
if (!function_exists('urlDecode')) {
    function urlDecode($data) {
        if (is_array($data)) {
            $ret = [];
            foreach ($data as $k => $v) {
                $ret[$k] = urldecode($v);
            }
            return $ret;
        } else {
            return urldecode($data);
        }
    }
}

if (!function_exists('randomCode')) {
    function randomCode($length = 6, $onlyNum = false) {
        mt_srand((double)microtime() * 1000000);
        $hash = '';
        $chars = 'ALLENABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        if ($onlyNum) {
            $chars = '01234567890123456789';
        }
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }
}

if (!function_exists('xmlCurl')) {
    function xmlCurl($url, $arr) {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        $ch = curl_init();

        //https的设置
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            throw new \Exception("curlerror, {$error}", $errno);
        }
        curl_close($ch);
        return $result;
    }
}

if (!function_exists('checkSign')) {
    function checkSign($params, $key='') {
        $sign = $params['sign'];
        unset($params['sign']);
        $params['appkey'] = $key;
        ksort($params);
        $str = implode("",$params);
        $signStr = md5($str);
        return $sign == md5($signStr);
    }
}

if (!function_exists('checkSignOld')) {
    function checkSignOld($params, $key) {
        $sign = $params['sign'];
        unset($params['sign']);
        $params['jh_sign'] = $key;
        ksort($params);
        $signStr = http_build_query($params);

        // 解决兼容ios 获取不到IP 报错信息带空格问题(?)
        $ary = explode('system_name', $params['extra_data']);  //IPADDRESS
        if ($ary[1]) {
//        $str = substr($ary[1], 0, 30);
            $str = $ary[1];
            if (preg_match("/\s/", $str)) {
                //实际要传+号，但解释过来是传了空格
                $signStr = str_replace('+', '%2B', $signStr);
//            $signStr = str_replace('+', '%20', $signStr);
            }
        }
        return $sign == md5($signStr);
    }
}

if (!function_exists('config'))
{
    /**
     * 获取配置相关
     * @param $key
     * @return mixed
     */
    function config($key)
    {
        return \SilangPHP\Config::get($key);
    }
}