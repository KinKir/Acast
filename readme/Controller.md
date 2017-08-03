## 控制器(Acast\\Controller)

[返回主页](../Readme.md)

### 规则

用户自定义的控制器应该继承`Acast\Controller`，命名空间应为`$app\Controller`。

如果存在类型及命名空间正确且与之名称相同的控制器或视图类，则它们会自动被加载。

### 绑定外部模型

> protected function Controller::invoke(string \$name) Model|null

有时，我们可能需要调用非与本控制器绑定的模型中的方法。`invoke()`方法会返回指定模型的一个实例。

`$name`为类名（不包括命名空间）。

### 全局的控制器绑定

除了在路由中为特定的路由节点外，也可使用如下方法添加全局作用域的控制器绑定。

> static function Controller::addGlobal(array \$controllers) void

`$controllers`的格式同`Router::bind()`，但是不可以省略别名。

### 适用于Acast\\Socket的成员函数

> protected function Controller::_lock(?callable \$callback = null) void

锁定客户端。此方法被调用后，用户的所有请求都将传递给指定的回调函数，而非路由。

若`$callback`为null，则用户的所有请求将被忽略。

> protected function Controller::_unlock() void

解锁客户端。

> protected function Controller::_getSession(mixed \$key) mixed

获取指定的`$key`对应的当前客户端连接的session。

> protected function Controller::_setSession(\$key, \$value = null) void

以指定的`$key`和`$value`设置当前客户端连接的session。

> function Controller::send(\$data, bool \$raw = false)

向客户端发送数据。若`$raw`为true，则数据不会被对应协议的`encode()`方法处理。

> function Controller::close(\$data = null, bool \$raw = false)

发送数据后关闭连接。

注：以上部分方法在`Acast\Socket\Router`中也存在功能相同的实现，可以在路由回调中通过`$this`指针调用。

### 成员变量

1. `$this->params`: 拷贝自路由实例。

2. `$this->mRet`: 拷贝自路由实例。

3. `$this->retMsg`: 拷贝自路由实例，调用结束后拷贝回路由。\(仅在HTTP环境下有效\)

4. `$this->model`: 绑定的模型示例。

5. `$this->view`: 绑定的视图示例。

6. `$this->method`: 拷贝自路由实例。