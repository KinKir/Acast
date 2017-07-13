<?php

namespace Acast\Socket;

use Workerman\Connection\TcpConnection;

class Router extends \Acast\Router {
    /**
     * 默认请求方法
     */
    const DEFAULT_METHOD = '.';
    /**
     * 锁定客户端
     *
     * @param callable|null $callback
     */
    protected function lock(callable $callback = null) {
        $this->connection->lock = $callback ?? true;
    }
    /**
     * 解锁客户端
     */
    protected function unlock() {
        unset($this->connection->lock);
    }
    /**
     * 获取当前客户端连接的Session
     *
     * @param $key
     * @return mixed
     */
    protected function getSession($key) {
        return $this->connection->session[$key];
    }
    /**
     * 设置当前客户端连接的Session
     *
     * @param $key
     * @param null $value
     */
    protected function setSession($key, $value = null) {
        if (isset($value))
            $this->connection->session[$key] = $value;
        else unset($this->connection->session[$key]);
    }
    /**
     * {@inheritdoc}
     */
    protected function _routerCall() : bool {
        $status = $this->connection->getStatus();
        if ($status === TcpConnection::STATUS_CLOSING || $status === TcpConnection::STATUS_CLOSED)
            return false;
        return parent::_routerCall();
    }
    /**
     * {@inheritdoc}
     */
    static function create(string $name) {
        parent::create($name);
        return self::$routers[$name] = new self;
    }
}