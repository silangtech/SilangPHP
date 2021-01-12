<?php
/**
 * 路由配置
 */

\SilangPHP\Route::get('test',function(){
    echo 'hello world';
});

\SilangPHP\Route::middle(
    'GET',
    ['index.test','index/index'], // 可以多个action
    [\App\Middleware\TestMiddleware::class] // 可以多个middleware
);

return [
    ['GET','index.test','index/index'],
    // ['GET','index.test','index/index',[\App\Middleware\TestMiddleware::class]],
    ['POST','index.oklala','index/index2'],
    ['GET','abc\d{1}\w{1}','index/index3'],
];