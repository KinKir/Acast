<?php

namespace Acast\Http;

use Workerman\Protocols\Http;

abstract class View extends \Acast\View {
    /**
     * @var Controller
     */
    protected $_controller;
    /**
     * 置HTTP状态码
     *
     * @param int $code
     * @param string|null $msg
     * @return string|null
     */
    static function http(int $code, ?string $msg = null) {
        Http::header('HTTP', $code);
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
        Http::header('Content-Type: application/json');
        return json_encode(['err' => $err] + $data);
    }
    /**
     * 将视图回传给控制器
     */
    function show() {
        $this->_controller->retMsg = $this->_temp;
    }
}