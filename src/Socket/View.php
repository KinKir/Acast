<?php

namespace Acast\Socket;

abstract class View extends \Acast\View {
    /**
     * @var Controller
     */
    protected $_controller;
    /**
     * {@inheritdoc}
     */
    function show() {
        $this->_controller->send($this->_temp);
    }
}