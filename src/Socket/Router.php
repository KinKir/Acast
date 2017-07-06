<?php

namespace Acast\Socket;

class Router extends \Acast\Router {
    /**
     * 请求内容
     * @var mixed
     */
    public $requestData;
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