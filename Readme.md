## Readme

Acast是一个轻量级的Web框架。

### 简介

#### 特性

1. 支持路由、中间件、视图、模型、控制器等组件。

2. 支持HTTPS、异步处理、定时任务、TCP转发等。

3. 基于Workerman提供服务，高并发，性能高于PHP-FPM。

4. 支持长连接应用。封装了GatewayWorker，可以便捷地实现客户端间通信。

5. 即将加入对[Amp](http://amphp.org/)的支持，可以在项目中使用Amp的高性能异步I/O组件。

#### 依赖

1. Acast依赖[Workerman](http://www.workerman.net/)提供HTTP服务。需要在项目中包含最新的Workerman源码。

2. Acast和Workerman依赖PHP的一些扩展，包括pcntl、posix和memcached。某些特性，如fork，是Linux独有的。这意味着你无法在Windows上使用本框架（如果有需要，可以自行修改Windows版本的Workerman）。

3. 你或许需要考虑安装[event扩展](https://pecl.php.net/package/event)来提高Workerman的性能。

4. Acast框架依赖较新版本的PHP(7.1.0及以上)，这是由于该框架对nullable和`Closure::fromCallable()`等特性的使用。

#### 使用Acast框架

1. 使用composer将Acast添加到项目中。

```bash
composer require cismonx/acast
```

2. 使用Acast优雅地实现你的业务逻辑。

3. 配置Nginx的端口转发、SSL等。

4. 像如下所示，在PHP-CLI中执行你的项目的入口文件。至此，服务已经启动。

```bash
php /Applications/main.php start -d
```

### 使用说明

#### 服务提供者

每一个服务提供者是一个独立的应用实例，它监听一个指定的端口，负责接收并处理客户端的请求。

请求处理完毕后，服务提供者将数据返回给客户端，并断开连接。

有关服务提供者的详细文档，见[这里](readme/Server.md)。

#### 路由

每一个路由与一个服务提供者绑定。根据用户请求的URI，路由决定将由哪个回调函数处理这个请求。请求完毕后，路由将返回数据传递给服务提供者。

可以为路由设置别名，从而实现路由分发。

有关路由的详细文档，见[这里](readme/Router.md)。

#### 中间件

每一个中间件包含一个回调函数，当与之绑定的路由的回调函数即将被调用或调用之后，该回调函数会被调用。

中间件常常用于验证、过滤数据等。合理地使用中间件可以减少项目中的重复代码，使之更易维护。

有关中间件的详细文档，见[这里](readme/Middleware.md)。

#### 控制器

控制器专注于处理业务逻辑。每一个路由可以与一个或多个控制器方法绑定。

每一个控制器也可以与一个模型和一个视图绑定。

有关控制器的详细文档，见[这里](readme/Controller.md)。

#### 模型

模型专注于数据库操作。每个服务中的所有模型与一个数据库绑定，服务中的每一个进程独立地与数据库建立连接。

所有涉及数据库操作的业务逻辑，建议在模型中实现，并由控制器调用。

有关模型的详细文档，见[这里](readme/Model.md)。

#### 视图

视图主要用于格式化输出数据。支持Memcached。

它也可以用于预先将本地的静态文件缓存到内存，在需要时快速地取出。

Acast的视图可以很好地与[Plates](http://platesphp.com/)等模版兼容。

有关视图的详细文档，见[这里](readme/View.md)。

#### 计划任务

使用Workerman的定时器可以实现基本的计划任务。

此外，Acast提供了一个对Workerman定时器的封装，用于对时间精确性要求不高的计划任务。

有关计划任务的详细文档，见[这里](readme/Cron.md)。

#### 迁移

这里迁移指数据库迁移。目前，Acast支持格式化并执行指定SQL模版的方式实现数据库的初始化。

以后可能将加入数据导出功能。

有关数据库迁移的详细文档，见[这里](readme/Migrate.md)。

#### 配置

Acast提供了一个全局变量的简单封装，便于进行项目的相关配置参数的设置和获取。

配置项可以作用于当前进程，也可以多进程间共享。

有关配置项的详细文档，见[这里](readme/Config.md)

#### 其他

有关`Acast\Socket\Enhanced`对GatewayWorker的封装及其使用，见[这里](readme/GatewayWorker.md)。

有关Acast的其他功能及使用时的注意事项，见[这里](readme/Misc.md)。