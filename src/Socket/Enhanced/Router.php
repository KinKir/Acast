<?php

namespace Acast\Socket\Enhanced;

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
        $_SESSION['lock'] = $callback;
    }
    /**
     * 解锁客户端
     */
    protected function unlock() {
        unset($_SESSION['lock']);
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