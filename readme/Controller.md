## 控制器(Acast\\Controller)

[返回主页](../Readme.md)

### 规则

用户自定义的控制器应该继承Acast\\Controller，命名空间应为$app\\Controller。

如果存在类型及命名空间正确且与之名称相同的控制器或视图类，则它们会自动被加载。

### 成员变量

1. $this-\>urlParams: 拷贝自路由实例。

2. $this-\>filterMsg: 拷贝自路由实例。

3. $this-\>retMsg: 拷贝自路由实例，调用结束后拷贝回路由。

4. $this-\>model: 绑定的模型示例。

5. $this-\>view: 绑定的视图示例。