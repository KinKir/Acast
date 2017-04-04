<?php

namespace Acast;
/**
 * 中间件
 * @package Acast
 */
abstract class Filter {
    /**
     * 输入过滤
     */
    const IN = 0;
    /**
     * 输出过滤
     */
    const OUT = 1;
    /**
     * 前驱中间件，将在路由回调之前被调用
     * @var array
     */
    protected static $_inFilters = [];
    /**
     * 后继中间件，将在路由回调之后被调用
     * @var array
     */
    protected static $_outFilters = [];
    /**
     * 注册中间件
     *
     * @param string $name
     * @param int $type
     * @param callable $callback
     */
    static function register(string $name, int $type = self::IN, callable $callback) {
        if ($type == self::IN ? isset(self::$_inFilters[$name]) : isset(self::$_outFilters[$name]))
            Console::notice("Overwriting filter callback \"$name\".");
        if ($type == self::IN)
            self::$_inFilters[$name] = $callback;
        else
            self::$_outFilters[$name] = $callback;
    }
    /**
     * 获取中间件
     *
     * @param string $name
     * @param int $type
     * @return callable|null
     */
    static function fetch(string $name, int $type = self::IN) : ?callable {
        if ($type == self::IN ? !isset(self::$_inFilters[$name]) : !isset(self::$_outFilters[$name])) {
            Console::warning("Failed to fetch filter \"$name\". Not exist.");
            return null;
        }
        return $type == self::IN ? self::$_inFilters[$name] : self::$_outFilters[$name];
    }
}