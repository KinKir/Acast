<?php

namespace Acast;
/**
 * 控制台I/O
 * @package Acast
 */
abstract class Console {
    /**
     * 触发致命错误
     *
     * @param string $msg
     */
    static function Fatal(string $msg) {
        trigger_error($msg, E_USER_ERROR);
    }
    /**
     * 触发警告
     *
     * @param string $msg
     */
    static function Warning(string $msg) {
        trigger_error($msg, E_USER_WARNING);
    }
    /**
     * 触发E_NOTICE
     *
     * @param string $msg
     */
    static function Notice(string $msg) {
        trigger_error($msg, E_USER_NOTICE);
    }
    /**
     * 输出行
     *
     * @param string $msg
     */
    static function Println(string $msg) {
        echo $msg, PHP_EOL;
    }
}