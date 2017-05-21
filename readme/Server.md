## 服务提供者(Acast\\Server)

[返回主页](../Readme.md)

### 新建服务

> static function Server::create(string $app, int $listen, ?array $ssl) void

事实上，每一个服务提供者是对一个Workerman的Worker示例的封装。和Acast框架的所有其他组件一样，它位于Acast命名空间下。

$listen为服务监听的端口。

如果需要提供HTTPS服务，$ssl格式应满足以下示例中格式，否则为空：

```php
Server::create('demo', 443, [
    'local_cert' => '/path/to/cert',
    'local_pk' => '/path/to/private/key',
    'verify_peer' => false
]);
```

注意，如果使用了HTTPS，PHP必须安装有openssl扩展。

### 获取服务

> static function Server::app(string $app) Server

我们可以用静态方法app来获取到已注册的服务，从而为其注册事件、路由等。

返回值为对应服务提供者实例。

### 添加路由

> function Server::route(string $name) void

成员函数route可以用来为当前服务绑定路由实例。

路由的使用方法参见[路由](Router.md)这一章。

### 注册事件

> function Server::event(string $event, callable $callback) void

Acast服务提供者的事件是对Workerman事件的一个封装，要求用户传递事件类型及回调函数，并交由Workerman处理。调用回调函数时会传递对应Worker实例。

当前支持的事件有："start", "stop", "bufferFull", "bufferDrain"。

其中，start回调会在当前服务的每个进程启动时被调用，同理，stop回调是在每个进程正常终止时被调用。

### Worker配置

> function Server::workerConfig(array $config) void

你可以方便地在服务提供者中配置Worker。如名称、进程数等。如下所示：

```php
Server::app('Demo')->config([
    'name' => 'Demo',
    'count' => 4
]);
```

也可以获取Worker实例的属性

> function Server::getWorkerProperty(string $name) mixed

详细配置项见Workerman文档

### 启动服务

> static function Server::start() void

在全局的初始化工作后调用此静态方法，用于启动、停止、重启服务等。

命令行参数会被处理，如果与Console类中注册的函数相匹配，则调用对应函数，然后结束运行。

否则，调用Worker::runAll()方法，交由Workerman处理命令行参数。

## 异步操作

> static function Server::async(callable $callback, mixed $param = null) void

创建一个子进程，并在子进程中执行回调函数，执行完成后，子进程会立即退出。

\$param为回调函数的参数。如果有多个参数，以数组形式传入。

该静态方法在调用后会立即返回，返回值为子进程pid。

Workerman\\Timer也可用于异步处理。注意，不要将高耗时的任务交由提供Web服务的进程执行。