## 其他

[返回主页](../Readme.md)

### 控制台I/O

由于Acast是基于PHP-CLI的，因此调试时难免会有控制台输出。Acast\\Console封装了一些控制台I/O的方法，参见src/Console.php。

### 响应

在控制器外，你不能使用与之绑定的视图快速地格式化返回数据。Acast\\Respond提供了基本的错误返回和JSON格式化。