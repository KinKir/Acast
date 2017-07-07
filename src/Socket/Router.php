<?php

namespace Acast\Socket;

class Router extends \Acast\Router {
    /**
     * 请求内容
     * @var mixed
     */
    public $requestData;
    /**
     * 默认请求方法
     */
    const DEFAULT_METHOD = '.';
    /**
     * 锁定客户端
     *
     * @param callable|null $callback
     */
    protected function lock(?callable $callback = null) {
        $this->connection->lock = $callback;
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
     * 创建路由实例
     * @param string $name
     */
    static function create(string $name) {
        parent::create($name);
        self::$routers[$name] = new self;
    }
}