<?php

namespace Acast\Http;

use Acast\ {
    Config
};
use Workerman\{
    Connection\AsyncTcpConnection,
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
     * 调用已注册的控制器中的方法
     *
     * @param string|int $name
     * @param mixed $param
     * @return mixed
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
     */
    protected function forward(string $name) {
        $this->connection->forward = true;
        if (!isset($this->connection->remotes[$name]))
            $this->connection->remotes[$name] = new AsyncTcpConnection(Config::get('FORWARD_'.$name));
        $remote = $this->connection->remotes[$name];
        $remote->pipe($this->connection);
        $this->connection->onClose = function () use ($remote) {
            $remote->close();
        };
        $remote->connect();
        $remote->send($this->requestData);
    }
    /**
     * 调用路由回调
     *
     * @return bool
     */
    protected function _routerCall() : bool {
        $status = $this->connection->getStatus();
        if ($status === TcpConnection::STATUS_CLOSING || $status === TcpConnection::STATUS_CLOSED)
            return false;
        return parent::_routerCall();
    }
    /**
     * 创建路由实例
     * @param string $name
     */
    static function create(string $name) {
        parent::create($name);
        self::$routers[$name] = new self;
    }
}