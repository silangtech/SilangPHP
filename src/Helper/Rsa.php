<?php

namespace SilangPHP\Helper;

class Rsa
{
    private $pubKey = null;
    private $priKey = null;

    /**
     * 构造函数
     */
    public function __construct()
    {
        // 需要开启openssl扩展
        if (!extension_loaded("openssl")) {
            $this->_error("Please open the openssl extension first.");
        }
    }

    /**
     * 读取公钥和私钥
     * @param string $public_key_file 公钥文件（验签和加密时传入）
     * @param string $private_key_file 私钥文件（签名和解密时传入）
     */
    public function init($public_key_file = '', $private_key_file = '')
    {
        if ($public_key_file) {
            $this->_getPublicKey($public_key_file);
        }

        if ($private_key_file) {
            $this->_getPrivateKey($private_key_file);
        }
    }

    /**
     * 自定义错误处理
     */
    private function _error($msg)
    {
        die('RSA Error:' . $msg); //TODO
    }

    /**
     * 检测填充类型
     * 加密只支持PKCS1_PADDING
     * 解密支持PKCS1_PADDING和NO_PADDING
     *
     * @param int 填充模式
     * @param string 加密en/解密de
     * @return bool
     */
    private function _checkPadding($padding, $type)
    {
        if ($type == 'en') {
            switch ($padding) {
                case OPENSSL_PKCS1_PADDING:
                    $ret = true;
                    break;
                default:
                    $ret = false;
            }
        } else {
            switch ($padding) {
                case OPENSSL_PKCS1_PADDING:
                case OPENSSL_NO_PADDING:
                    $ret = true;
                    break;
                default:
                    $ret = false;
            }
        }
        return $ret;
    }

    private function _encode($data, $code)
    {
        switch (strtolower($code)) {
            case 'base64':
                $data = base64_encode('' . $data);
                break;
            case 'hex':
                $data = bin2hex($data);
                break;
            case 'bin':
            default:
        }
        return $data;
    }

    private function _decode($data, $code)
    {
        switch (strtolower($code)) {
            case 'base64':
                $data = base64_decode($data);
                break;
            case 'hex':
                $data = $this->_hex2bin($data);
                break;
            case 'bin':
            default:
        }
        return $data;
    }

    private function _getPublicKey($file)
    {
        $key_content = $this->_readFile($file);
        if ($key_content) {
            $this->pubKey = openssl_get_publickey($key_content);
        }else{
            if(!empty($file))
            {
                $key_content = $this->stringTopublicKey($file);
                $this->pubKey = openssl_get_publickey($key_content);
            }
        }
    }

    private function _getPrivateKey($file)
    {
        $key_content = $this->_readFile($file);
        if ($key_content) {
            $this->priKey = openssl_get_privatekey($key_content);
        }else{
            if(!empty($file))
            {
                $key_content = $this->stringToprivateKey($file);
                $this->priKey = openssl_get_privatekey($key_content);
            }
        }
    }

    private function _readFile($file)
    {
        $ret = false;
        if (!file_exists($file)) {
            return false;
        } else {
            $ret = file_get_contents($file);
        }
        return $ret;
    }

    private function _hex2bin($hex = false)
    {
        $ret = $hex !== false && preg_match('/^[0-9a-fA-F]+$/i', $hex) ? pack("H*", $hex) : false;
        return $ret;
    }

    /**
     * 生成Rsa公钥和私钥
     * @param int $private_key_bits 建议：[512, 1024, 2048, 4096]
     * @return array
     */
    public function generate(int $private_key_bits = 1024)
    {
        $rsa = [
            "private_key" => "",
            "public_key" => ""
        ];

        $config = [
            "digest_alg" => "sha512",
            "private_key_bits" => $private_key_bits, #此处必须为int类型
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];

        //创建公钥和私钥
        $res = openssl_pkey_new($config);

        //提取私钥
        openssl_pkey_export($res, $rsa['private_key']);

        //生成公钥
        $rsa['public_key'] = openssl_pkey_get_details($res)["key"];
        /*Array
        (
            [bits] => 512
            [key] =>
            [rsa] =>
            [type] => 0
        )*/
        return $rsa;
    }

    public function generateStr(int $private_key_bits = 1024)
    {
        $rsa = $this->generate($private_key_bits);
        $rsa['private_key'] = $this->privateKeyToString($rsa['private_key']);
        $rsa['public_key'] = $this->publicKeyToString($rsa['public_key']);
        return $rsa;
    }

    /**
     * 私钥字符串
     * @param $private_key
     * @return string|string[]
     */
    public function privateKeyToString($private_key)
    {
        //-----BEGIN PRIVATE KEY-----  -----BEGIN RSA PRIVATE KEY-----
        $private_key = str_replace("-----BEGIN PRIVATE KEY-----", "", $private_key);
        $private_key = str_replace("-----END PRIVATE KEY-----", "", $private_key);
        $private_key = str_replace("\n","",$private_key);
        return $private_key;
    }

    /**
     * 公钥字符串
     * @param $public_key
     * @return string|string[]
     */
    public function publicKeyToString($public_key)
    {
        $public_key = str_replace("-----BEGIN PUBLIC KEY-----", "", $public_key);
        $public_key = str_replace("-----END PUBLIC KEY-----", "", $public_key);
        $public_key = str_replace("\n","",$public_key);
        return $public_key;
    }

    public function stringTopublicKey($pubKey)
    {
        $pubKey='-----BEGIN PUBLIC KEY-----'.PHP_EOL
            .chunk_split($pubKey, 64, PHP_EOL)
            .'-----END PUBLIC KEY-----'.PHP_EOL;
        return $pubKey;
    }

    public function stringToprivateKey($private_key)
    {
        $private_key='-----BEGIN PRIVATE KEY-----'.PHP_EOL
            .chunk_split($private_key, 64, PHP_EOL)
            .'-----END PRIVATE KEY-----'.PHP_EOL;
        return $private_key;
    }

    /**
     * 生成签名
     *
     * @param string 签名材料
     * @param string 签名编码（base64/hex/bin）
     * @return bool|string 签名值
     */
    public function sign($data, $code = 'base64')
    {
        $ret = false;
        if (openssl_sign($data, $ret, $this->priKey)) {
            $ret = $this->_encode($ret, $code);
        }
        return $ret;
    }

    /**
     * 验证签名
     *
     * @param string 签名材料
     * @param string 签名值
     * @param string 签名编码（base64/hex/bin）
     * @return bool
     */
    public function verify($data, $sign, $code = 'base64')
    {
        $ret = false;
        $sign = $this->_decode($sign, $code);
        if ($sign !== false) {
            switch (openssl_verify($data, $sign, $this->pubKey)) {
                case 1:
                    $ret = true;
                    break;
                case 0:
                case -1:
                default:
                    $ret = false;
            }
        }
        return $ret;
    }

    /**
     * 加密
     *
     * @param string 明文
     * @param string 密文编码（base64/hex/bin）
     * @param int 填充方式（貌似php有bug，所以目前仅支持OPENSSL_PKCS1_PADDING）
     * @return string 密文
     */
    public function encrypt($data, $code = 'base64', $padding = OPENSSL_PKCS1_PADDING)
    {
        $ret = false;
        if (!$this->_checkPadding($padding, 'en')) $this->_error('padding error');
        if (openssl_public_encrypt($data, $result, $this->pubKey, $padding)) {
            $ret = $this->_encode($result, $code);
        }
        return $ret;
    }

    /**
     * 解密
     *
     * @param string 密文
     * @param string 密文编码（base64/hex/bin）
     * @param int 填充方式（OPENSSL_PKCS1_PADDING / OPENSSL_NO_PADDING）
     * @param bool 是否翻转明文（When passing Microsoft CryptoAPI-generated RSA cyphertext, revert the bytes in the block）
     * @return string 明文
     */
    public function decrypt($data, $code = 'base64', $padding = OPENSSL_PKCS1_PADDING, $rev = false)
    {
        $ret = false;
        $data = $this->_decode($data, $code);
        if (!$this->_checkPadding($padding, 'de')) $this->_error('padding error');
        if ($data !== false) {
            if (openssl_private_decrypt($data, $result, $this->priKey, $padding)) {
                $ret = $rev ? rtrim(strrev($result), "\0") : '' . $result;
            }
        }
        return $ret;
    }
}