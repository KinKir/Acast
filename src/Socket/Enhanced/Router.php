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
     * {@inheritdoc}
     */
    protected function forward(string $name) {
        Console::warning('Forwarding not supported in \Acast\Socket\Enhanced.');
    }
    /**
     * {@inheritdoc}
     */
    static function create(string $name) : self {
        parent::create($name);
        return self::$routers[$name] = new self;
    }
}