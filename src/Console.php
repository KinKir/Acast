<?php

namespace Acast;
/**
 * 控制台I/O
 * @package Acast
 */
abstract class Console {
    /**
     * 标准输入流句柄
     * @var resource
     */
    protected static $_stdin;
    /**
     * 控制台函数列表
     * @var array
     */
    static $callbacks = [];
    /**
     * 触发致命错误
     *
     * @param string $msg
     */
    static function fatal(string $msg) {
        trigger_error($msg, E_USER_ERROR);
    }
    /**
     * 触发警告
     *
     * @param string $msg
     */
    static function warning(string $msg) {
        trigger_error($msg, E_USER_WARNING);
    }
    /**
     * 触发E_NOTICE
     *
     * @param string $msg
     */
    static function notice(string $msg) {
        trigger_error($msg, E_USER_NOTICE);
    }
    /**
     * 输出行
     *
     * @param string $msg
     */
    static function println(string $msg) {
        echo $msg, PHP_EOL;
    }
    /**
     * 从标准输入流读取一行字符串
     * @return string
     */
    static function readln() : string {
        if (isset(self::$_stdin))
            self::$_stdin = fopen('php://stdin', 'r');
        return rtrim(fgets(self::$_stdin));
    }
    /**
     * 注册控制台函数
     *
     * @param string $name
     * @param callable $callback
     */
    static function register(string $name, callable $callback) {
        if (isset(self::$callbacks[$name]))
            self::warning("Overwriting console callback \"$name\".");
        self::$callbacks[$name] = $callback;
    }
    /**
     * 调用控制台函数
     *
     * @param string $name
     * @param array $params
     */
    static function call(string $name, array $params) {
        if (!isset(self::$callbacks[$name]))
            self::warning("Console callback \"$name\" do not exist.");
        $callback = self::$callbacks[$name];
        $callback($params);
    }
}