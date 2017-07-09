## 路由(Acast\\Router)

[返回主页](../Readme.md)

### 注册路由

> static function Router::create(string $name) void

根据路由名称创建路由实例。创建的实例可以由以下方法获取：

> static function Router::instance(string $name) Router

### 添加路由

> function Router::add(?array $path, $methods, callable $callback) Router

1. `$path`为Request URI以"/"为分界符分割后的数组。如果是根目录，则为空数组。如果要将数组的某个成员作为参数捕获，则在其之前加"/"。路由匹配成功后，其值将保存到`$this->params`中。例如，`$path`为\['id', '/id', 'name', '/name'\]，且Request URI为"/id/3/name/foo"时，就会得到`$this->params` = \['id => '3', 'name' => 'foo'\]。

2. `$methods`为HTTP请求的方法，包括"POST", "GET", "PUT"等。可以传递一个数组，包含所有需要匹配的方法。若当前不处于HTTP环境，则该参数可以根据情况自定义。

3. `$callback`为回调函数。如果不是闭包，它将会自动被转化为闭包。然后，闭包会与当前路由实例绑定，在回调函数内可以通过`$this`指针调用其方法并访问其成员变量。

`Acast`提供了当所有路由都无法匹配的时候自动匹配的路由。`$path`设为null即可。

### 绑定控制器

> function Router::bind(array $controllers) Router 

1. 一个路由可以绑定多个控制器，`$controller`为数组，可以为单个控制器或多个控制器，格式为\[string $name, string $controller, string $method\]。

2. 数组`$controller`中也可不指定`$name`，如果这样，则设置`$name`为已绑定的控制器数量。

3. `$controller`为绑定的控制器的类名。该类必须继承`Acast\Controller`。

4. `$name`为标识绑定的名称（用于`invoke()`调用），`$method`为控制器的方法。

5. 可以添加全局作用域的控制器绑定，见[控制器](Controller.md)一章。

若一个路由绑定了控制器，则可以在回调函数中用以下方法调用。该类的构造函数会先被调用，然后是指定的方法。

> function Router::invoke(string $name, $param = null) mixed 
  
可以向控制器方法传递一个参数，也可以获取控制器方法的返回值。

### 绑定中间件

参见[中间件](Middleware.md)一章。

### 路由别名

> function Router::alias(mixed $name) Router

`$name`为你将要给该路由设置的别名（以数组的形式可以同时设置多个别名）。给路由设置别名后可以实现路由的分发。

### 路由分发

> function Router::dispatch($name) bool

分发到指定别名的路由。指定的路由的回调函数将被调用。

`$name`支持数组。这种情况下，数组中每个路由的回调函数将被依次调用，直至有一个回调函数返回false。

注意，分发后的路由，其回调函数被调用时不会触发中间件。

### TCP转发

有时候，对于某些路由，我们需要转发到提供服务的其他端口，如`npm`的`serve`，此时，我们可以使用`forward()`方法。

> function Router::forward(string $name) void

$name为要转发到的地址的别名，需要在服务启动前使用Config::set\(\)设置，前缀为"FORWARD\_"。

```php
Server::config([
    'FORWARD_NEW' => 'tcp://127.0.0.1:8080'
]);
//...
Router::instance('demo')->add(['new'], 'GET', function () {
    $this->forward('NEW');
});
```

### 成员变量说明

1. `$this->params`: 数组，保存通过路由匹配到的Request URI中的参数。

2. `$this->mRet`: 建议用该变量保存中间件的返回值以便其他中间件、路由或者控制器使用。

3. `$this->retMsg`: 路由回调结束后返回给用户的数据。\(仅在HTTP环境下有效\)

4. `$this->connection`: 与客户端的连接实例。在HTTP环境下不建议直接使用。

5. `$this->method`: HTTP请求的方法。非HTTP环境可以自定义该参数的获取方式。

6. `$this->requestData`: 客户端发来的请求数据，在`Acast\Http`下，为客户端发来的完整的HTTP请求内容。在`Acast\Socket`下，则由自定义`onMessage`回调的返回值自动设置。

### 示例

```php
Router::create('demo');
Router::instance('demo')->add([], ['GET', 'POST'], function () {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $this->dispatch('user');
    } else {
        $this->retMsg = 'Hello stranger!';
    }
    return false;
});
Router::instance('emo')->add(['user'], 'POST', function () {
    if ($this->mRet) {
        $this->invoke();
    }
})->middleware('auth')->bind(['User', 'showName'])->alias('user');
Server::app('demo')->route('demo');
```