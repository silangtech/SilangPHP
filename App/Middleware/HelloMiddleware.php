<?php
namespace App\Middleware;
use Closure;
class HelloMiddleware
{
    public function handle($request, Closure $next)
    {
        echo '中间件hello start'.lr;
        $response = $next($request);
        echo '中间件hello end'.lr;
        return $response;
    }
}