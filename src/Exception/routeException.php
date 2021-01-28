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
namespace SilangPHP\Exception;
use SilangPHP\SilangPHP;

/**
 * 路由异常
 * Class routeException
 * @package SilangPHP\Exception
 */
Class routeException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null) {

        \SilangPHP\Facade\Log::error(json_encode(SilangPHP::$app->request).$message);
    }
}