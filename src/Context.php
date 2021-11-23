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
declare(strict_types = 1);
namespace SilangPHP;

Class Context
{
    public $id;
    public $request;
    public $response;
    public $route;
    public $hander;
    public $vars;

    public function __construct(\SilangPHP\Request $request, \SilangPHP\Response $response, $id = ''){
        $this->request = $request;
        $this->response = $response;
        $this->id = $id;
    }

    /**
     * json格式化
     */
    public function JSON($httpcode = 200, $data = [])
    {
        $this->response->withStatus($httpcode, '');
        $data = json_encode($data, \JSON_UNESCAPED_UNICODE);
        $this->response->end($data);
    }

    /**
     * 输出string
     */
    public function String($httpcode = 200, $str = '')
    {
        $this->response->withStatus($httpcode, '');
        $this->response->end($str);
    }

    /**
     * 输出XML
     */
    public function XML($httpcode = 200, $data = [])
    {

    }

    /**
     * 输出yaml
     */
    public function YAML($httpcode = 200, $data = [])
    {

    }


}