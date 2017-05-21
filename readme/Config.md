## 配置(Acast\\Config)

[返回主页](../Readme.md)

### 设置配置项

> function Config::set(string $key, mixed $value) void

以上方法可以用于设置作用域为当前进程的全局变量。

> function Config::setGlobal(string $key, mixed $value) bool

也可以设置可以在多进程间共享的全局变量（需要配置memcached）。

> function Config::setArray(array $config) void

> function Config::setGlobal_array(array $config) void

可以通过一个key-value型数组批量设置。

### 获取配置项

> function Config::get(string $key) mixed

> function Config::getGlobal(string $key) mixed.