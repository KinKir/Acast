## 控制器(Acast\\Controller)

[返回主页](../Readme.md)

### 规则

用户自定义的控制器应该继承Acast\\Controller，命名空间应为$app\\Controller。

如果存在类型及命名空间正确且与之名称相同的控制器或视图类，则它们会自动被加载。

### 绑定外部模型

> protected function Controller::invoke(string $name) Model|null

有时，我们可能需要调用非与本控制器绑定的模型中的方法。invoke方法会返回指定模型的一个实例。

$name为类名（不包括命名空间）。

### 成员变量

1. $this-\>urlParams: 拷贝自路由实例。

2. $this-\>mRet: 拷贝自路由实例。

3. $this-\>retMsg: 拷贝自路由实例，调用结束后拷贝回路由。

4. $this-\>model: 绑定的模型示例。

5. $this-\>view: 绑定的视图示例。