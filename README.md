# SilangPHP
四狼PHP框架
一款快速、稳定的轻量级PHP框架。
不少人纠结用哪种框架，其实没必要纠结那么多，因为我们是学习语言不是学习框架，框架复杂的模式可以再根据业务加上，为了方便内部开发，我们决定重复造轮子。


# 安装
composer require "phpsl/SilangPHP:dev-master"

# 新建项目
暂时没有脚手架支持创建项目文件，需手动新建
```
├── App
│   ├── Config
│   │   ├── Db.php   数据库相关
│   │   └── default.php  项目相关
│   ├── Controller
│   │   └── Index.php  控制类
│   ├── Model
│   │   └── Index.php  数据模型
│   ├── Service   对外接口，针对非App内部的调用
│   └── View      模板目录
├── Public
│   └── index.php  www入口
├── Runtime    运行日志
│   └── Log    日志
```