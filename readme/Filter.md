## 中间件(Acast\\Filter)

[返回主页](../Readme.md)

在Acast中，我们把中间件命名为过滤器(Filter)，因为它更加形象。

### 注册中间件

> static function Filter::register(string $name, int $type = IN_FILTER, callable $callback) void

1. $name为中间件的名称。

2. $type可以为Acast\\**IN_FILTER**或者Acast\\**OUT_FILTER**，前者在路由回调之前被调用，后者在路由回调之后被调用。

3. $callback为回调函数。绑定路由时，该回调也会与路由实例绑定，因此可以通过$this指针访问路由实例的成员。

### 绑定路由

> function Router::filter(array $filters) Router

$filter为一个key-value型数组，key为中间件名，value为类型。绑定的中间件将被依次调用，除非某个回调返回false。

### 返回数据

如果一个中间件需要独立地返回数据供中间件或者控制器使用，建议将其保存到$this-\>filterMsg中。