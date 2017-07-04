<?php

namespace Acast;
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
 * 引用依赖
 */
require_once __DIR__ . '/Constants.php';
require_once WORKERMAN_ROOT . '/Autoloader.php';
foreach (glob(__DIR__ . '/src/*.php') as $require_file)
    require_once $require_file;

if (ENABLE_HTTP) {
    foreach (glob(__DIR__.'/src/Http/*.php') as $require_file)
        require_once $require_file;
}