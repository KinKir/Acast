## 计划任务(Acast\\Cron)

[返回主页](../Readme.md)

### 创建/删除服务

> static function Cron::create(string $name, int $interval) void

在当前进程创建一个计划任务服务。`$name`为服务名称，`$interval`为计划任务的执行间隔。

> static function Cron::destroy(string $name) void

删除指定的计划任务服务，与其绑定的计划任务会全部销毁。

### 启用/禁用服务

当一个服务被创建，它默认为启用状态。也可以调用下列方法修改计划任务服务的启动状态。

> function Cron::enable() void

> function Corn::disable() void

### 获取服务实例

> static function Cron::instance(string $name) Cron

根据服务名获取实例。

### 添加/删除计划任务

> function Cron::add(string $name, int $when, callable $callback, $param = null, bool $persistent) void

1. `$name`为计划任务名称

2. `$when`为时间戳，当计划任务服务执行时，若该时间戳不迟于当前时间，则对应计划任务的回调函数将被调用

3. `$callback`为回调函数，在下一节中会对函数格式作出说明。

4. `$param`为传递给回调函数的额外参数。

5. `$persistent`为计划任务的持久性，若为`false`，则其回调函数执行完毕后，该任务会被移除。

注意：避免在用于网络IO的进程中添加高耗时的计划任务，否则会导致阻塞。

> function Cron::del(string $name) void

以上方法用于删除计划任务。

### 计划任务回调

回调函数的格式如下：

```php
$callback = function(int &$when, bool &$persistent, &$param) {
    //do something...
};
```

可以看到，在回调函数的上下文中，可以修改对应计划任务的相关参数。