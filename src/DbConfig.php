<?php

namespace Acast;
/**
 * 数据库配置
 * @package Acast
 */
abstract class DbConfig {
    /**
     * 数据库配置项列表
     * @var array
     */
    protected static $_config = [];
    /**
     * 绑定数据库配置
     *
     * @param string $app
     * @param array $config
     */
    static function bind(string $app, array $config) {
        if (isset(self::$_config[$app]))
            Console::Warning("Overwriting database configuration for app \"$app\".");
        self::$_config[$app] = $config;
    }
    /**
     * 获取数据库配置
     *
     * @param string $app
     * @return array
     */
    static function get(string $app) : array {
        if (!isset(self::$_config[$app]))
            Console::Fatal("Database configuration for app \"$app\" not exist.");
        return self::$_config[$app];
    }
}