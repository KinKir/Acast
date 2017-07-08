<?php

namespace Acast\Socket\Enhanced;

abstract class View extends \Acast\View {
    /**
     * @var Controller
     */
    protected $_controller;
    /**
     * 将视图回传给控制器
     */
    function show() {
        return $this->_temp;
    }
}