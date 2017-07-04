## 其他

[返回主页](../Readme.md)

### 控制台I/O

由于`Acast`是基于`PHP-CLI`的，因此调试时难免会有控制台输出。`Acast\Console`封装了一些控制台I/O的方法，参见`src/Console.php`。

Acast还提供了控制台函数的注册接口，如下：

> static function Console::register(string $name, callable $callback) void

注册后，可以在执行入口文件时带上参数，调用相对应的函数。比如，你注册了一个名为clear的函数，你可以以如下方式调用：

```bash
php main.php clear user room
```

则你注册的clear函数会被调用，而user和room会以数组的形式作为参数传递给回调函数。

函数执行后，脚本会立即停止运行，不会启动`Workerman`服务。

如果函数名没有匹配，则参数会交给`Workerman`处理。如start，stop等。因此，不要注册和`Workerman`冲突的参数。

### Memcached使用

服务启动时，`Acast`会自动创建`Memcached`客户端实例`Server::$memcached`，需要在服务器上预先配置`Memcached`服务：

```bash
sudo service memcached start
```

并在`Workerman`的start事件中连接Memcached server，方可使用。

### $_SESSION

`Workerman`提供了对`$_SESSION`的支持，使用前需要调用`Http::sessionStart()`方法。

在`Acast`中，该功能默认被禁用。用户可以修改Config.php中的常量`ENABLE_SESSION`从而启用这一功能。

### Workerman

注意，官方版本的`Workerman`无法在`Acast`中直接使用。

建议使用本项目内附带的`Workerman`，该`Workerman`的分支会随时更新，并不断被修改优化以适应于Acast的Web开发。