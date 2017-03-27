## 其他

[返回主页](../Readme.md)

### 控制台I/O

由于Acast是基于PHP-CLI的，因此调试时难免会有控制台输出。Acast\\Console封装了一些控制台I/O的方法，参见src/Console.php。

Acast还提供了控制台函数的注册接口，如下：

> static function Console::register(string $name, callable $callback) void

注册后，可以在执行入口文件时带上参数，调用相对应的函数。比如，你注册了一个名为clear的函数，你可以以如下方式调用：

```bash
php main.php clear user room
```

则你注册的clear函数会被调用，而user和room会以数组的形式作为参数传递给回调函数。

函数执行后，脚本会立即停止运行，不会启动Workerman服务。

如果函数名没有匹配，则参数会原封不动地交给Workerman处理。

### 响应

在控制器外，你不能使用与之绑定的视图快速地格式化返回数据。Acast\\Respond提供了基本的错误返回和JSON格式化。