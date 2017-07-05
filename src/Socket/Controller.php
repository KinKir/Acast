<?php

namespace Acast\Socket;


abstract class Controller extends \Acast\Controller {
    /**
     * 当前绑定的路由实例
     * @var Router
     */
    private $_router;
    /**
     * 构造函数，绑定模型、视图
     *
     * @param Router $router
     */
    function __construct(Router $router) {
        parent::__construct($router);
        $this->_router = $router;
    }
    protected function lock(callable $callback) {
        $this->_router->connection->lock = $callback;
    }
    protected function unlock() {
        unset($this->_router->connection->lock);
    }
    protected function getSession($key) {
        return $this->_router->connection->session[$key];
    }
    protected function setSession($key, $value = null) {
        if (isset($value))
            $this->_router->connection->session[$key] = $value;
        else unset($this->_router->connection->session[$key]);
    }
    function send($data, bool $raw = false) {
        return $this->_router->connection->send($data, $raw);
    }
    function close($data = null, bool $raw = false) {
        $this->_router->connection->close($data, $raw);
    }
}