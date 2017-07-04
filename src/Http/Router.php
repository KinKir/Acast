<?php

namespace Acast\Http;

use Acast\ {
    Config
};
use Workerman\ {
    Connection\AsyncTcpConnection
};

class Router extends \Acast\Router {
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
        $remote->send($this->rawRequest);
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