<?php

namespace Acast;
use Workerman\Protocols\Http;
/**
 * 响应
 * @package Acast
 */
abstract class Respond {
    /**
     * 格式化错误信息
     *
     * @param int $code
     * @param string $msg
     * @return string
     */
    static function Err(int $code, string $msg) {
        Http::header($code);
        return $msg;
    }
    /**
     * 格式化为JSON
     *
     * @param array $arr
     * @param int $code
     * @return string
     */
    static function Json(array $arr, int $code) {
        return json_encode(['code' => $code] + $arr);
    }
}