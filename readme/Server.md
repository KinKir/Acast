## 服务提供者(Acast\\Server)

[返回主页](../Readme.md)

### 新建服务

> static function Server::create(string \$app, int \$listen, ?array \$ssl) void

事实上，每一个服务提供者是对一个Workerman的Worker实例的封装。和Acast框架的所有其他组件一样，它位于`\Acast`命名空间下。

`$listen`为服务监听的端口。根据服务类型的不同，服务所使用的协议也不同。`Acast\Http`主要提供HTTP协议，而`Acast\Socket`提供各种基于TCP长连接的协议，包括WebSocket。

如果需要提供HTTPS或WSS服务，`$ssl`格式应满足以下示例中格式（详见[这里](http://php.net/manual/en/context.ssl.php)），否则为空：

```php
Server::create('demo', 443, [
    'local_cert' => '/path/to/cert',
    'local_pk' => '/path/to/private/key',
    'verify_peer' => false
]);
```

注意，如果使用了SSL，PHP必须安装有`openssl`扩展。

### 获取服务

> static function Server::app(string \$app) Server

我们可以用静态方法`app()`来获取到已注册的服务，从而为其注册事件、路由等。

返回值为对应服务提供者实例。

### 添加路由

> function Server::route(string \$name) void

成员函数`route()`可以用来为当前服务绑定路由实例。

路由的使用方法参见[路由](Router.md)这一章。

### 注册事件

> function Server::event(string \$event, callable \$callback) void

Acast服务提供者的事件是对Workerman事件的一个封装，要求用户传递事件类型及回调函数，并交由Workerman处理。调用回调函数时会传递对应Worker实例。

其中，onWorkerStart回调会在当前服务的每个进程启动时被调用，同理，onWorkerStop回调是在每个进程正常终止时被调用。

在Acast\Socket下，支持onStart，onStop和onMessage回调，其中onWorkerStart和onWorkerStop回调分别在连接建立和连接关闭时被调用，而onMessage回调用于确定请求方法和路由。其示例如下：

```php
Server::app('Demo')->event('Message', function ($connection, $data, &$path, &$method) {
    $path = $data['path'];
    $method = $data['method'];
    return $data['data'];
});
```

### Worker配置

> function Server::workerConfig(array \$config) void

你可以方便地在服务提供者中配置Worker。如名称、进程数等。如下所示：

```php
Server::app('Demo')->workerConfig([
    'name' => 'Demo',
    'count' => 4
]);
```

也可以获取Worker实例的属性

> function Server::getWorkerProperty(string \$name) mixed

详细配置项见Workerman文档。

### 启动服务

> static function Server::start(?callable \$callback = null) void

在全局的初始化工作后调用此静态方法，用于启动、停止、重启服务等。

命令行参数会被处理，如果与`Acast\Console`中注册的函数相匹配，则调用对应函数，然后结束运行。

`$callback`回调将会在Worker即将被启动之前被调用。

否则，调用`Worker::runAll()`方法，交由Workerman处理命令行参数。
