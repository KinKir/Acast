<?php

namespace Acast\Socket\Enhanced;

class Router extends \Acast\Router {
    /**
     * 当前客户端连接的ID
     * @var string
     */
    public $client_id;
    /**
     * 默认请求方法
     */
    const DEFAULT_METHOD = '.';
    /**
     * 创建路由实例
     * @param string $name
     */
    static function create(string $name) {
        parent::create($name);
        self::$routers[$name] = new self;
    }
}