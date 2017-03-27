## 模型(Acast\\Model)

[返回主页](../Readme.md)

### 规则

用户自定义的控制器应该继承Acast\\Model，命名空间应为$app\\Model。

模型会在控制器的构造函数被调用时被自动加载。

### 配置数据库

> static function Model::config(array $config) void

$config为MySQL配置数据，格式为\[$host, $port, $user, $password, $db_name\]。

一般，数据库配置和初始化工作在在start回调中进行。

### 操作数据库

> static function Model::Db() Connection

获取当前数据库连接实例，从而进行数据库操作。参见[Auraphp.SqlQuery](https://github.com/auraphp/Aura.SqlQuery)。

### 便捷操作

Acast\\Model提供了一些便捷的方法，方便进行一些基本的CURD操作。

> function View::select($cols, array $where, ?array $bind = null, ?array $order_by = null, ?array $limit = null) mixed

> function insert(array $cols, ?array $bind = null) mixed

> function update($cols, array $where, ?array $bind = null, ?int $limit = null) mixed

以上操作需要通过View::table()方法绑定数据表。一般来说，一个控制器不止操作一个数据表，因此，这个方法不是十分实用。

由于数据库连接的实例是静态成员，因此，可以将某些可能被多个控制器调用的方法定义为静态方法，方便被其他模型调用。