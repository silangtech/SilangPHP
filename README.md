# SilangPHP
SilangPHP是一款极简http组件，支持Api、Service模式。

# 说明
非标准化http-message, 路由使用fastroute结合

## 项目示例
composer create-project "silangtech/silangweb:dev-master" project1

# 路由
## 添加路由
addRoute(方法, 路由规则, Callable, middleware);
```
\SilangPHP\Route::addRoute('GET', 'silangphp/index', '\\App\\Controller\\IndexController@Index');
```

## 使用路由组
addGroup(前缀，Callable, middleware);
```PHP
\SilangPHP\Route::addGroup('silangphp', function(){
    \SilangPHP\Route::addRoute('GET', 'index', '\\App\\Controller\\IndexController@Index');
});

```
## 全局中件间
```PHP
\SilangPHP\Route::use(funciton($c){
    // 逻辑前
    \SilangPHP\Route::next($c);
    // 逻辑后
});

```
# 控制器示例
```PHP
Class index{
    // 一定要加$c参数，主要返回相关的context
    public function index($c)
    {
        
    }
}
```

# request和response
```PHP
public function index($c)
{
    $c->reqeust->item('test', '');
    $c->reqeust->get('test', '');
    $c->reqeust->post('test', '');
    $c->JSON(200, ['array' => 'test']);
    $c->String(200, '哈哈');
}
```

# 入口
新建好index.php即可
## http
```PHP
// 定义好与vendor同目录即可，加载composer使用
define("PS_ROOT_PATH",       dirname(dirname(__FILE__)));
// 设置你自己的Config路径， 不然读取不了Config
define("PS_CONFIG_PATH",     PS_ROOT_PATH."/Config/");
// 设置你项目的tmp路径
define("PS_RUNTIME_PATH",	 PS_ROOT_PATH."/Runtime/");
//  加载composer
require_once(PS_ROOT_PATH."/vendor/autoload.php");

// 这里处理Route
include 'Route.php'; // 这里自己思考即可
//运行框架
\SilangPHP\SilangPHP::run();
```

## Command与Service
```PHP
// 定义好与vendor同目录即可，加载composer使用
define("PS_ROOT_PATH",       dirname(dirname(__FILE__)));
// 设置你自己的Config路径， 不然读取不了Config
define("PS_CONFIG_PATH",     PS_ROOT_PATH."/Config/");
// 设置你项目的tmp路径
define("PS_RUNTIME_PATH",	 PS_ROOT_PATH."/Runtime/");
require_once(PS_ROOT_PATH."/vendor/autoload.php");

// 输入要运行的命令
\SilangPHP\SilangPHP::runCmd($argv[1] ?? '', $argv[2] ?? '');
```

# 其它小方法
## HTML模板引擎
```PHP
public function index($c)
{
    $params = ['title' => '首页标题', 'body' => '这是一篇文章'];
    $c->HTML(200, '/web/index.php', $params);
}

```

## 临时缓存
```PHP
\SilangPHP\SilangPHP::setCache('key', 'test');
\SilangPHP\SilangPHP::getCache('key');
```
 
## 动态生成路由配置
```PHP
$data = scandir($path);
// scan之后，把controll读取生成Route配置
// 处理control文件略过
echo "Route::addRoute('POST', '/api/{$classname}/{$funcname}{$vars}', 'mg\\\\http\\\\api\\\\controller\\\\{$classname}@{$funcname}');".PHP_EOL;
```

