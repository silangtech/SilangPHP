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
namespace SilangPHP\Middleware;
use Closure;
class CorsMiddleware
{
    public function handle($request, Closure $next)
    {
        $url = $_SERVER['HTTP_REFERER'];
        $domainName = parse_url($url, PHP_URL_HOST);
        \SilangPHP\SilangPHP::$app->response->setCors($domainName);
        $response = $next($request);
        return $response;
    }
}