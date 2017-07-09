## 视图(Acast\\View)

[返回主页](../Readme.md)

### 规则

用户自定义的控制器应该继承`Acast\View`，命名空间应为`$app\View`。

视图会在控制器的构造函数被调用时被自动加载。

### 注册视图

> static function View::register(string $name, $data, bool $use_memcached = false) void

1. `$name`为视图名。

2. `$data`为视图内容，可以为字符串或者对象。支持[Plates](http://platesphp.com/)等模版。

3. 是否使用`Memcached`。该选项一般用于需要跨进程、跨服务共享的视图模版。

### 取出视图

> function View::fetch(string $name) View

> function View::show() void

`View::fetch()`方法取出的视图将保存到局部变量`$this->_temp`中，可以对其进一步处理。

`View::show()`方法在不同环境下行为不同。如在`Acast\Http`下，该方法会将`$this->_temp`赋值给控制器的`$this->retMsg`，在`Acast\Socket`下，则将其发送给客户端。

如：

```php
$this->view->err('404', 'Page not found!')->show();
```

### 其他

`Acast\Http\View`内置两个成员函数，`View::err()`和`View::json()`，便于返回错误信息或将数组格式化为JSON。