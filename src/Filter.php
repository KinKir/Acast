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
    const _IN_ = 0;
    /**
     * 输出过滤
     */
    const _OUT_ = 1;
    /**
     * 前驱中间件，将在路由回调之前被调用。
     * @var array
     */
    protected static $_inFilters = [];
    /**
     * 后继中间件，将在路由回调之后被调用。
     * @var array
     */
    protected static $_outFilters = [];
    /**
     * 注册中间件。
     *
     * @param string $name
     * @param int $type
     * @param callable $callback
     */
    static function register(string $name, int $type = self::_IN_, callable $callback) {
        if ($type == self::_IN_ ? isset(self::$_inFilters[$name]) : isset(self::$_outFilters[$name]))
            Console::Notice("Overwriting filter callback \"$name\".");
        if ($type == self::_IN_)
            self::$_inFilters[$name] = $callback;
        else
            self::$_outFilters[$name] = $callback;
    }
    /**
     * 获取中间件。
     *
     * @param string $name
     * @param int $type
     * @return callable|null
     */
    static function fetch(string $name, int $type = self::_IN_) : ?callable {
        if ($type == self::_IN_ ? !isset(self::$_inFilters[$name]) : !isset(self::$_outFilters[$name])) {
            Console::Warning("Failed to fetch filter \"$name\". Not exist.");
            return null;
        }
        return $type == self::_IN_ ? self::$_inFilters[$name] : self::$_outFilters[$name];
    }
}