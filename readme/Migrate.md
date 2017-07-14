## 迁移(Acast\\Migrate)

[返回主页](../Readme.md)

### 新建Migrate实例

> static function Migrate::create(string $name, array $settings, string $sql_path) void

新建一个Migrate实例。

1. `$name`为实例名称。
2. `$settings`为连接配置（格式与Model相同，但不指定"dbname"）。
3. `$sql_path`为待执行的SQL文件的路径。

### 获取Migrate实例

> static function Migrate::instance(string $name) Migrate

### 执行迁移操作

> function Migrate::execute(array $replace) void

其中，`$replace`为key-value型数组。key为待替换的字段名，value为替换后的字符串。替换后，SQL文件中的语句将被执行。

在SQL文件中，key的格式为“%:=key=:%”。