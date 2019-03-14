tp5仿阿里巴巴小说站，链接：http://www.shuqi.com/

前端pc仿阿里巴巴小说站，wap仿言情小说吧，后台前端使用x-admin框架，基于tp5.0编写，实现了小说的自动采集！

环境要求：php5.5，需开启伪静态

安装：直接放到php环境根目录后访问public目录的index.php文件，

后端地址：网站地址后加admin.php(如：http://www.tkbooks.club/admin.php)
默认帐号密码是：admin,admin

安装完成后需配置：畅言帐号（如无配置，网站将无法评论） 邮件服务（如无配置，网站将无法注册）

安装完成后，点击后面采集管理中的采集小说，即可采集小说，已配置好采集规则，自动采集起点小说站的小说，如果是linux服务器，请配置好public\static\images\books_img下文件的读写权限

数据库文件：根目录下的new_books.sql文件

本站只是个人学习所作，若有不足请指出(qq：1258598558)！

根据此源码个人搭起的小说站：http://www.tkbooks.club/

博文链接：https://blog.csdn.net/u012095440/article/details/86648452 

详细开发文档参考 [ThinkPHP5完全开发手册](http://www.kancloud.cn/manual/thinkphp5)

## 目录结构

初始的目录结构如下：

~~~
www  WEB部署目录（或者子目录）
├─application           应用目录
│  ├─common             公共模块目录（可以更改）
│  ├─module_name        模块目录
│  │  ├─config.php      模块配置文件
│  │  ├─common.php      模块函数文件
│  │  ├─controller      控制器目录
│  │  ├─model           模型目录
│  │  ├─view            视图目录
│  │  └─ ...            更多类库目录
│  │
│  ├─command.php        命令行工具配置文件
│  ├─common.php         公共函数文件
│  ├─config.php         公共配置文件
│  ├─route.php          路由配置文件
│  ├─tags.php           应用行为扩展定义文件
│  └─database.php       数据库配置文件
│
├─public                WEB目录（对外访问目录）
│  ├─index.php          入口文件
│  ├─router.php         快速测试文件
│  └─.htaccess          用于apache的重写
│
├─thinkphp              框架系统目录
│  ├─lang               语言文件目录
│  ├─library            框架类库目录
│  │  ├─think           Think类库包目录
│  │  └─traits          系统Trait目录
│  │
│  ├─tpl                系统模板目录
│  ├─base.php           基础定义文件
│  ├─console.php        控制台入口文件
│  ├─convention.php     框架惯例配置文件
│  ├─helper.php         助手函数文件
│  ├─phpunit.xml        phpunit配置文件
│  └─start.php          框架入口文件
│
├─extend                扩展类库目录
├─runtime               应用的运行时目录（可写，可定制）
├─vendor                第三方类库目录（Composer依赖库）
├─build.php             自动生成定义文件（参考）
├─composer.json         composer 定义文件
├─LICENSE.txt           授权说明文件
├─README.md             README 文件
├─think                 命令行入口文件
~~~


