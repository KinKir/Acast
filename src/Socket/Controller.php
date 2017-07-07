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
    /**
     * 锁定客户端
     *
     * @param callable|null $callback
     */
    protected function _lock(?callable $callback = null) {
        $this->_router->connection->lock = $callback;
    }
    /**
     * 解锁客户端
     */
    protected function _unlock() {
        unset($this->_router->connection->lock);
    }
    /**
     * 获取当前客户端连接的Session
     *
     * @param $key
     * @return mixed
     */
    protected function _getSession($key) {
        return $this->_router->connection->session[$key];
    }
    /**
     * 设置当前客户端连接的Session
     *
     * @param $key
     * @param null $value
     */
    protected function _setSession($key, $value = null) {
        if (isset($value))
            $this->_router->connection->session[$key] = $value;
        else unset($this->_router->connection->session[$key]);
    }
    /**
     * 获取当前客户端连接的实例
     *
     * @return \Workerman\Connection\TcpConnection
     */
    protected function _getConnection() {
        return $this->_router->connection;
    }
    /**
     * 向客户端发送数据
     *
     * @param $data
     * @param bool $raw
     * @return mixed
     */
    function send($data, bool $raw = false) {
        return $this->_router->connection->send($data, $raw);
    }
    /**
     * 向客户端发送数据并关闭连接
     *
     * @param null $data
     * @param bool $raw
     */
    function close($data = null, bool $raw = false) {
        $this->_router->connection->close($data, $raw);
    }
}