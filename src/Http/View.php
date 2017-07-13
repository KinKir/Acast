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
     * {@inheritdoc}
     */
    static function json(array $data, int $err = 0) {
        Http::header('Content-Type: application/json');
        return parent::json($data, $err);
    }
    /**
     * {@inheritdoc}
     */
    function show() {
        $this->_controller->retMsg = $this->_temp;
    }
}