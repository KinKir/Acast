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
    static function err(int $code, string $msg) {
        Http::header($code);
        return $msg;
    }
    /**
     * 格式化为JSON
     *
     * @param array $data
     * @param int $err
     * @return string
     */
    static function json(array $data, int $err = 0) {
        return json_encode(['err' => $err] + $data);
    }
}