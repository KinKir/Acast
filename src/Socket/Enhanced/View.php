<?php

namespace Acast\Socket\Enhanced;

abstract class View extends \Acast\View {
    /**
     * @var Controller
     */
    protected $_controller;
    /**
     * {@inheritdoc}
     */
    function show() {
        return $this->_temp;
    }
}