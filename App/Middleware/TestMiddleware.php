<?php
namespace App\Middleware;
use Closure;
class TestMiddleware
{
    public function handle($request, Closure $next)
    {
        $request->test1 = 'ok';
        echo '中间件test start'.lr;
        // return '执行失败';
        $response = $next($request);
        echo '中间件test end'.lr;
        return $response;
    }
}