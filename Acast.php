<?php
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