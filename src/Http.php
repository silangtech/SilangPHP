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
use GuzzleHttp\Client;
/**
 * 基于guzzle请求网络
 * Class Http
 * @package SilangPHP
 */
Class Http{
    public static $timeout = 3;

    /**
     * http Get
     * @param $url
     * @param array $query
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function get($url,$query = [])
    {
        $client = new Client();
        $response = $client->get($url, [
            'query' => $query,
            'timeout' => self::$timeout
        ]);
        $body = $response->getBody();
        $bodyStr = (string)$body;
        return $bodyStr;
    }

    /**
     * http Post
     * @param $url
     * @param $query
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function post($url,$query)
    {
        $client = new Client();
        $response = $client->post($url, [
            'form_params' => $query
        ]);
        $body = $response->getBody();
        $bodyStr = (string)$body;
        return $bodyStr;
    }

    /**
     * http PostJson
     * @param $url
     * @param $query
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function postjson($url,$query)
    {
        $client = new Client();
        $response = $client->post($url, [
            'json' => $query
        ]);
        $body = $response->getBody();
        $bodyStr = (string)$body;
        return $bodyStr;
    }
}