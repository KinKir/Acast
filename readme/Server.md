## 服务提供者(Acast\\Server)

[返回主页](../Readme.md)

### 新建服务

> static function Server::create(string $app, int $listen) void

事实上，每一个服务提供者是对一个Workerman的Worker示例的封装。和Acast框架的所有其他组件一样，它位于Acast命名空间下。

如下所示，调用静态方法create，创建一个名为Demo的服务，监听本地8080端口。

```php
use Acast\Server;
Server::create('Demo', 8080);
```

注意，此时，服务只是被注册，并没有启动。

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

### 配置项

> function Server::config(mixed $config) mixed

在服务尚未启动时，该方法用于配置全局常量。$config为key-value型数组。多次调用该方法会merge新值与旧值。

在服务已经启动时，该方法用于获取配置项的值。$config为配置项的键。返回配置项的值。

### 启动服务

> static function Server::start() void

执行服务启动前的初始化操作，并调用Worker::runAll()方法，启动所有Worker。

## 异步操作

> static function Server::async(callable $callback, mixed $param = null) void

创建一个子进程，并在子进程中执行回调函数，执行完成后，子进程会立即退出。

该静态方法在调用后会立即返回。