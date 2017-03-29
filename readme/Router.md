## 路由(Acast\\Router)

[返回主页](../Readme.md)

### 注册路由

> function Router::add(?array $path, $methods, callable $callback) Router

1. $path为Request URI以"/"为分界符分割后的数组。如果是根目录，则为空数组。如果要将数组的某个成员作为参数捕获，则在其之前加"/"。路由匹配成功后，其值将保存到$this-\>urlParams中。例如，$path为\['id', '/id', 'name', '/name'\]，且Request URI为"/id/3/name/foo"时，就会得到$this-\>urlParams = \['id => '3', 'name' => 'foo'\]。

2. $methods为HTTP请求的方法，包括"POST", "GET", "PUT"等。可以传递一个数组，包含所有需要匹配的方法。

3. $callback为回调函数。如果不是闭包，它将会自动被转化为闭包。然后，闭包会与当前路由实例绑定，在回调函数内可以通过$this指针调用其方法并访问其成员变量。

Acast提供了当所有路由都无法匹配的时候自动匹配的路由。$path设为null即可。

### 绑定控制器

> function Router::bind(string $name, string $controller, string $method) Router 

1. 一个路由可以绑定多个控制器，$name为绑定名。

2. $controller为绑定的控制器的类名。该类必须继承Acast\\Controller。

3. $method为控制器的方法。

若一个路由绑定了控制器，则可以在回调函数中用以下方法调用。该类的构造函数会先被调用，然后是指定的方法。

> function Router::invoke(string $name, $param = null) mixed 
  
可以向控制器方法传递一个参数，也可以获取控制器方法的返回值。

### 绑定中间件

参见[中间件](Filter.md)一章。

### 路由别名

> function Router::alias(string $name) Router

$name为你将要给该路由设置的别名。给路由设置别名后可以实现路由的分发。

注意，如果你希望别名路由不保留绑定的中间件和控制器，你需要在filter和bind方法之前调用该方法。

### 路由分发

> function Router::dispatch($name) bool

分发到指定别名的路由。指定的路由的回调函数将被调用。

$name支持数组。这种情况下，数组中每个路由的回调函数将被依次调用，直至有一个回调函数返回false。

### 成员变量说明

1. $this-\>urlParams: 数组，保存通过路由匹配到的Request URI中的参数。

2. $this-\>filterMsg: 建议用该变量保存中间件的返回值以便其他中间件、路由或者控制器使用。

3. $this-\>retMsg: 路由回调结束后返回给用户的数据。

4. $this-\>connection: 与客户端的连接实例。一般用于在输入中间件中直接输出错误信息并关闭连接。不建议在路由回调或输出中间件中使用。由于关闭连接的操作是异步的，因此调用$this-\>connection-\>close()时后应直接返回false，否则可能出现不可预料的错误。

### 示例

```php
Server::app('Demo')->route([], ['GET', 'POST'], function () {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $this->dispatch('user');
    } else {
        $this->retMsg = 'Hello stranger!';
    }
    return false;
});
Server::app('Demo')->route(['user'], 'POST', function () {
    if ($this->filterMsg) {
        $this->invoke();
    }
})->filter(['auth' => \Acast\IN_FILTER])->bind('User', 'showName')->alias('user');
```