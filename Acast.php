<?php
/**
 * 检查当前PHP版本
 */
$php_version = phpversion();
if (version_compare($php_version, '7.1.0', '<')) {
    echo "Your PHP(version $php_version) is not supported by Acast. Please install PHP 7.1.0 or above.\n";
    exit(1);
}
/**
 * 检查PHP运行环境
 */
if (php_sapi_name() != 'cli') {
    echo "Acast can only run in CLI mode.\n";
    exit(11);
}
/**
 * 引用Acast配置文件
 */
require_once __DIR__ . '/Config.php';
/**
 * 引用Workerman自动加载器
 */
require_once Acast\WORKERMAN_ROOT . '/Autoloader.php';
/**
 * 引用Acast框架
 */
foreach (glob(__DIR__ . '/src/*.php') as $require_file)
    require_once $require_file;