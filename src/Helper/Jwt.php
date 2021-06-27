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
namespace SilangPHP\Helper;


class Jwt
{
    public $leeway = 0;
    // 默认的私钥，新的项目请更新一下
    public $privateKey = 'MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDMzG2Nx6VLaJnXt6ASGDqiBsZg4QIalgIc8LhbguathL178D9bxlwtt7aIsn+mG7jpYeKakYzjJNFGVcRWz0bjX1JwXFVs6gQVXva0Jr3/DnHRP2VwhRMPFejsOkaU2ulE+1T8q5x0tnXNqq2kwITNO2TI6CHxYT7wBlVVr8u+acL8slOxe4MZodygMuGpeWejADTp7yT+zn1h7JqRwfv+22zS0IzBxToiGCNwpSFE24bnlTq4UxoQuwc389TkU2dFNGO0dnJe7F4zdYFlslvD0FcqdOdXWLJnmaXhuR98RiV1BL/+NV1HRjUNailpNjORVJ0eH2nNkT8+Rm5Bj/yNAgMBAAECggEBAIiVnkr8Z2UPcerIcF0hA5mj4xIhfoG0odwjPxDpEGeRK75I0Dio5jknWPl58mAaBQzBV5u2jru+hkPeV3995c0meZiuO9rTh72pA+fKHtTIEWh4W3LbjYZ2Gk3R39jO1txGsruAyGuedJqhxgUm0Gr/vYuwJVMShCMYVjk7cSqftN+i62EF1hIx2hsrGixs5NHX96d59EG1ZXEDQ0XFsgE5t5CyzDB6XZ1XmS2+RSrPD2jgXsiNkDnXXjTrQORrIxMOe/LrcEiqx59W1RaJdAy5otFBnb304eTMIM3n4muAntQKYDDi7VTxWhrN3XVTkKYz7QQRurk1oqPR1ml2A4ECgYEA7ixtCoPgGIgx6lakU6W2ODpk3JaJCLiPvSAgbwrdk5wfzXfA1etd5Rc1T789iXsrprKVNP0mLuexG5OgZEhyiZxVRrXpubp2Dsv9rOBSVVHWtrpiVYHk8sZap0d4HNjjoddUAZ9TLD44YWc1RcRXaGfR60+gdVs5ug3X3+v74YkCgYEA3CCDcleDVl9xI7cBoFCcx3EPTk1AUoM+E5m4LyD5Tpj4h/vP1/4fdzPa2LjhqQZRYHoRVnuW2zuMpkrwU/VHvfjMqFxKHpnyyFQCH9WsDtfawEKAjCqrKDoab+mx6K7wx6vJgYIr7jgJB5CzHm4c0eYsq7cfNftHExXWim0HFeUCgYA0hl2hxQWFw3tml6bzv4bZDZ+yugwMfU9NwSkF9Bp7dCHbWT6QrrBtVlnudVln2V7+C0I2hmGGVJhNYZgspYGE8ZIo3vNlf0aL6tbN6kaeBEda0u3et5+7Hp1daLiHfBlvVqJzHSTli+85I96uvScdok+8GjpjxzOK4YSrIErTKQKBgAyV404PSNgQXuSe2v3yffQ9N5XPfuT40fzG5ivzo61lu5fywsBjrCLhmRqY/3XtgYuVjxY1vLtOHU2IrVLvjCmFZxYdKLO1M5dWjkSJr+utVbl2U+kvq1eCcu6BGwXlsqiH3t6DtcVJ+OIw2LzdqNIradRrhOJOOpMRxZFmiGgtAoGAUo/IQvZYSAtoAu16YCJmyKf5pBKBWAGUPtWzi+Mz/sfjBOedqPPdMH88Dbd71+t56qLnr7Xjz3ThhJmHHwu16FChF4xQvABLW7Gl6ynUNeOt7Rs2AN9P98Y92aTd1EVWCVRYwQ5LYGEsdkMUUFtf2HRSOkoVTtHBVZWdgLafIT8=';
    // 默认的公钥，新的项目请更新一下
    public $publicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAzMxtjcelS2iZ17egEhg6ogbGYOECGpYCHPC4W4LmrYS9e/A/W8ZcLbe2iLJ/phu46WHimpGM4yTRRlXEVs9G419ScFxVbOoEFV72tCa9/w5x0T9lcIUTDxXo7DpGlNrpRPtU/KucdLZ1zaqtpMCEzTtkyOgh8WE+8AZVVa/LvmnC/LJTsXuDGaHcoDLhqXlnowA06e8k/s59YeyakcH7/tts0tCMwcU6IhgjcKUhRNuG55U6uFMaELsHN/PU5FNnRTRjtHZyXuxeM3WBZbJbw9BXKnTnV1iyZ5ml4bkffEYldQS//jVdR0Y1DWopaTYzkVSdHh9pzZE/PkZuQY/8jQIDAQAB';

    public function __construct($config = [])
    {
        if(isset($config['privateKey']))
        {
            $this->privateKey = $config['privateKey'];
        }
        if(isset($config['publicKey']))
        {
            $this->publicKey = $config['publicKey'];
        }
    }

    /**
     * 设置privateKey
     * @param $privateKey
     */
    public function setPrivateKey($privateKey = '')
    {
        $this->privateKey = $privateKey;
        return true;
    }

    /**
     * 设置publicKey
     * @param $publicKey
     */
    public function setPublicKey($publicKey = '')
    {
        $this->publicKey = $publicKey;
        return true;
    }

    /**
     * 加密
     */
    public function encode($payload,$key=''):string
    {
        if(empty($key))
        {
            $key = $this->privatekey($this->privateKey);
        }
        //分三段
        //header.payload.signature[ ha256(b64(header).b64(payload),secret) ]
        //header
        $header = array('typ' => 'JWT', 'alg' => "HS256");
        //payload
        $segments = array();
        $segments[] = $this->DataEncode(json_encode($header));
        $segments[] = $this->DataEncode(json_encode($payload));
        $signing_input = implode('.', $segments);
        //加密
        $signature = $this->sign($signing_input, $key);
        $segments[] = $this->DataEncode($signature);
        $segments_str = implode('.', $segments);
        return $segments_str;
    }

    /**
     * jwt解密
     * iss 签发人
     * exp 过期时间
     * sub 主题
     * aud 受众
     * nbf 生效时间
     * iat 签发时间
     * jti 编号
     * 自定义：使用nbf和exp就ok
     */
    public function decode($jwt, $key = '', $retArr = false)
    {
        if(empty($key))
        {
            $key = $this->publickey($this->publicKey);
        }
        $timestamp = time();
        if (empty($key)) {
            return false;
        }
        //一定是三个下标的数组
        $tks = explode('.', $jwt);
        if (count($tks) != 3) {
            return false;
        }
        list($headb64, $bodyb64, $cryptob64) = $tks;
        if (null === ($header = json_decode($this->DataDecode($headb64),false, 512, JSON_BIGINT_AS_STRING))) {
            return false;
        }
        if (null === $payload = json_decode($this->DataDecode($bodyb64), false, 512, JSON_BIGINT_AS_STRING)) {
            return false;
        }
        if (false === ($sig = $this->DataDecode($cryptob64))) {
            return false;
        }
        if (!$this->verify("$headb64.$bodyb64", $sig, $key, $header->alg)) {
            return false;
        }
        //验证nbf
        if (isset($payload->nbf)) {
            if($payload->nbf > ($timestamp + $this->leeway))
            {
                return false;
            }
        }
        //验证iat
        if (isset($payload->iat)) {
            if($payload->iat > ($timestamp + $this->leeway))
            {
                return false;
            }
        }
        //验证exp
        if (isset($payload->exp)) {
            if(($timestamp - $this->leeway) >= $payload->exp)
            {
                return false;
            }
        }else{
            //一定要加过期时间,exp必填项
            return false;
        }
        if($retArr)
        {
            $payload = (array)$payload;
        }
        return $payload;
    }
    /**
     * 验证加密数据
     */
    private static function verify($msg, $signature, $key)
    {
        $success = openssl_verify($msg, $signature, $key,"SHA256");
        if ($success === 1) {
            return true;
        } elseif ($success === 0) {
            return false;
        }
    }
    /**
     * 加密方式，使用openssl sha256即可
     */
    public function sign($msg, $key)
    {
        $signature = '';
        $success = openssl_sign($msg, $signature, $key, "SHA256");
        if (!$success) {
            return false;
        } else {
            return $signature;
        }
    }

    /**
     * base64安全加密
     */
    public function DataEncode($input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    /**
     * base64安全解密
     */
    public function DataDecode($input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }
    /**
     * 获取openssl私钥
     */
    public function privatekey($privateKey)
    {
        $privateKey = chunk_split($privateKey,64,"\n");
        $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n".$privateKey."-----END RSA PRIVATE KEY-----\n";
        $privateKey = openssl_get_privatekey($privateKey);
        return $privateKey;
    }
    /**
     * 获取openssl公钥
     */
    public function publickey($publicKey)
    {
        $publicKey = chunk_split($publicKey,64,"\n");
        $publicKey = "-----BEGIN PUBLIC KEY-----\n".$publicKey."-----END PUBLIC KEY-----\n";
        $publicKey = openssl_get_publickey($publicKey);
        return $publicKey;
    }

}