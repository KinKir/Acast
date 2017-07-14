## 在Acast中应用GatewayWorker

[返回主页](../Readme.md)

### 概述

Acast提供了对Workerman的GatewayWorker组件的封装，用于便捷地实现与服务器建立长连接的客户端与客户端之间的通讯。

该服务位于命名空间`Acast\Socket\Enhanced`。其使用与`Acast\Socket`近似，但有几点仍有必要做出说明。

### 提供服务

GatewayWorker类库由三类Worker构成提供服务：Gateway，BusinessWorker与Register。

> static function Server::create(string $app, ?string $listen = null, ?array $ssl = null, bool $businessWorker = true) Server

1. `$listen`为监听的地址。若为null，则不会创建`Gateway`。可以自定义协议，这一点和`Acast\Socket`相同。

2. `$businessWorker`为是否要创建`BusinessWorker`。

在分布式部署时，同一个服务中`Gateway`和`BusinessWorker`不一定需要同时运行，此时需要根据情况选择是否创建相关服务。

> function Server::addBusinessWorker() void

以上方法可以单独创建BusinessWorker。

#### Register

`GatewayWorker\Register`用于建立Gateway与BusinessWorker之间的连接，使用基于TCP的text协议（换行符分包）。

> static function Server::addRegister(string $name, string $listen) void

1. `$name`为Worker的名称。

2. `$listen`为监听的地址和端口，如"\[::\]:3000"。协议不可自定义。

#### Gateway

`GatewayWorker\Gateway`主要用于网络I/O，收到客户端请求后将数据发送给BusinessWorker处理，反之，BusinessWorker要向用户发送数据，也要交由Gateway。

`Server::workerConfig()`方法用于配置`Gateway`的相关参数。注意`registerAddress`的设置。

`Server::event()`方法用于向Gateway注册事件。由于Gateway不用于处理业务逻辑，所以onMessage事件不可用。同时，尽量避免在onConnect和onStop事件中加入过多业务逻辑。

`Server::getWorkerProperty()`方法用于获取当前Gateway实例的属性。

#### BusinessWorker

`GatewayWorker\BusinessWorker`用于处理业务逻辑。

`Server::businessWorkerConfig()`方法用于配置BusinessWorker的相关参数。注意`$worker->registerAddress`的设置。

`Server::businessEvent()`方法用于向BusinessWorker注册事件。其中，onMessage回调用于格式化用户输入并确定路由等参数，其功能与`Acast\Socket`中的相同。

注意，由于BusinessWorker与网络I/O层是分离的，故onConnect，onMessage，onClose回调都不会传递连接实例作为第一个参数，而是当前客户端的client_id。

`Server::getBusinessWorkerProperty()`方法用于获取当前BusinessWorker实例的属性。

### 与客户端通信

通过`GatewayWorker\Lib\Gateway`类中提供的静态方法可以实现与客户端通信。

Acast还封装了少量的方法，下面会有说明。此外，开发者可以根据实际需要，自行在控制器中封装相应方法。

> static function Controller::send($data, $client_id = null) bool

向目标客户端发送消息，如果`$client_id`为null，则发送给当前用户。

> static function Controller::close($data = null, $client_id = null) bool

发送消息并关闭客户端连接。

> static function Controller::lock(?callable $callback = null) void

锁定客户端，之后用户发送的请求不会通过onMessage回调以及路由，而是仅执行提供的回调`$callback`。若`$callback`为null，则不会执行任何回调。

由于该方法是通过设置`$_SESSION`实现的，所以只在当前tick有效（事实上`Gateway`中所有不需要提供client_id默认为当前用户的方法都仅在当前tick有效），所以如果需要在Timer中执行lock或者unlock操作，需要调用`Gateway::updateSession`并传入client_id。

其次，由于`$_SESSION`会在当前tick结束时被`serialize()`，而`Closure`不可被`serialize()`，所以`$callback`参数不能为`Closure`。

> static function Controller::unlock() void

解锁客户端。

### 其他

关于更多有关GatewayWorker的使用方法，详见[官方文档](http://www.workerman.net/gatewaydoc/)