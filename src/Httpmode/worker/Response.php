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
namespace SilangPHP\Httpmode\worker;
use \Workerman\Protocols\Http\Response as WorkResponse;
class Response 
{
    public $connection;
    public function __construct($connection)
    {
        $this->connection = $connection;
    }
    
    public function write($data)
    {
        $res = new WorkResponse(200, [], $data);
        $this->connection->send($res);
    }

    public function send($data)
    {
        $res = new WorkResponse(200, [], $data);
        $this->connection->send($res);
    }

    public function end($data)
    {
        $res = new WorkResponse(200, [], $data);
        $this->connection->send($res);
    }

    /**
     * @param int $status
     * @param array $headers
     * @param string $body
     * @return Response
     */
    function response($body = '', $status = 200, $headers = array())
    {
        return new WorkResponse($status, $headers, $body);
    }

    /**
     * @param $data
     * @param int $options
     * @return Response
     */
    function json($data, $options = JSON_UNESCAPED_UNICODE)
    {
        return new WorkResponse(200, ['Content-Type' => 'application/json'], json_encode($data, $options));
    }

    /**
     * @param $xml
     * @return Response
     */
    function xml($xml)
    {
        if ($xml instanceof \SimpleXMLElement) {
            $xml = $xml->asXML();
        }
        return new WorkResponse(200, ['Content-Type' => 'text/xml'], $xml);
    }
}