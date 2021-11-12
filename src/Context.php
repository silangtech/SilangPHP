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

    public function __construct($request, $response, $id = ''){
        $this->request = $request;
        $this->response = $response;
        $this->id = $id;
    }
}