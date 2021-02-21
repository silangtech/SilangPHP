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

/**
 * jwt中间件
 */
class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        if(isset($_SERVER['HTTP_AUTHORIZATION']))
        {
            $authorization = $_SERVER['HTTP_AUTHORIZATION'];
        }elseif(isset($_SERVER['HTTP_X_TOKEN']))
        {
            $authorization = $_SERVER['HTTP_X_TOKEN'];
        }
        if(!empty($authorization))
        {
            $jwt = new \SilangPHP\Jwt\Jwt();
            //验证authorization
            $auth_data = $jwt->decode($authorization);
        }
        if(empty($auth_data))
        {
            // 调试先关闭
            return \SilangPHP\SilangPHP::$app->response->json(-1,'token校验异常');
        }
        $request->auth_data = $auth_data;
        $response = $next($request);
        
        return $response;
    }
}