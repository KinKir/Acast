<?php

namespace Acast;
/**
 * 中间件
 * @package Acast
 */
abstract class Middleware {
    /**
     * 存储中间件回调函数
     * @var array
     */
    protected static $_middleware = [];
    /**
     * 注册中间件
     *
     * @param string $name
     * @param callable $callback
     */
    static function register(string $name, callable $callback) {
        if (isset(self::$_middleware[$name]))
            Console::warning("Overwriting middleware callback \"$name\".");
        self::$_middleware[$name] = $callback;
    }
    /**
     * 获取中间件
     *
     * @param string $name
     * @return callable|null
     */
    static function fetch(string $name) : ?callable {
        if (!isset(self::$_middleware[$name])) {
            Console::warning("Failed to fetch middleware \"$name\". Not exist.");
            return null;
        }
        return self::$_middleware[$name];
    }
}