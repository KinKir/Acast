<?php

namespace Acast\Socket;

abstract class View extends \Acast\View {
    /**
     * @var Controller
     */
    protected $_controller;
    /**
     * 将视图回传给控制器
     */
    function show() {
        $this->_controller->send($this->_temp);
    }
}