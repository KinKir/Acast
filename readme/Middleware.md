## 中间件(Acast\\Middleware)

[返回主页](../Readme.md)

### 注册中间件

> static function Middleware::register(string $name, callable $callback) void

1. $name为中间件的名称。

2. $callback为回调函数。绑定路由时，该回调也会与路由实例绑定，因此可以通过$this指针访问路由实例的成员。

### 绑定路由

> function Router::middleware(mixed $names) Router

$names为一个数组或字符串，指定的中间件将被依次调用，除非某个回调返回false。

注意，当一个节点被绑定路由后，访问其子节点时，该节点的回调会先被调用。

### 中间件延后

> function Router::delay() void

在中间件回调中调用此方法，则路由回调会被调用，调用完成后再执行其余中间件回调。

### 返回数据

如果一个中间件需要独立地返回数据供中间件或者控制器使用，建议将其保存到$this-\>mRet中。