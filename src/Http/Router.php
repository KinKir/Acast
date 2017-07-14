<?php

namespace Acast\Http;

use Workerman\{
    Connection\TcpConnection
};

class Router extends \Acast\Router {
    /**
     * 返回参数
     * @var mixed
     */
    public $retMsg;
    /**
     * 控制器实例（临时）
     * @var Controller
     */
    protected $_object;
    /**
     * {@inheritdoc}
     */
    protected function invoke($name = 0, $param = null) {
        $ret = parent::invoke($name, $param);
        $this->retMsg = $this->_object->retMsg;
        return $ret;
    }
    /**
     * 转发HTTP请求
     *
     * @param string $name
     * @param bool $pipe
     */
    protected function forward(string $name, bool $pipe = false) {
        parent::forward($name);
        $remote = $this->connection->remotes[$name];
        $remote->pipe($this->connection);
        if ($pipe)
            $this->connection->pipe($remote);
        else
            $this->connection->onClose = function () use ($remote) {
                $remote->close();
            };
        $remote->connect();
        $remote->send($this->requestData);
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
    static function create(string $name) : self {
        parent::create($name);
        return self::$routers[$name] = new self;
    }
}