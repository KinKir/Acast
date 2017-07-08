<?php

namespace Acast\Socket\Enhanced;

use GatewayWorker\Lib\Gateway;

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
        $_SESSION['lock'] = $callback;
    }
    /**
     * 解锁客户端
     */
    protected function _unlock() {
        unset($_SESSION['lock']);
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
     * @param $client_id
     * @return bool
     */
    static function send($data, $client_id = null) {
        return $client_id ? Gateway::sendToClient($client_id, $data) : Gateway::sendToCurrentClient($data);
    }
    /**
     * 向客户端发送数据并关闭连接
     *
     * @param $data
     * @param $client_id
     * @return bool
     */
    static function close($data = null, $client_id = null) {
        return $client_id ? Gateway::closeClient($client_id, $data) : Gateway::closeCurrentClient($data);
    }
}