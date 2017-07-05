<?php

namespace Acast\Socket;

use Workerman\ {
    Connection\TcpConnection
};

class Server extends \Acast\Server {
    /**
     * 路由实例
     * @var Router
     */
    protected $_router;
    /**
     * 收到请求回调
     *
     * @param TcpConnection $connection
     * @param string $data
     */
    function onMessage(TcpConnection $connection, $data) {
        parent::onMessage($connection, $data);
        if (is_callable($callback = $connection->lock)) {
            $callback($connection, $data);
            return;
        }
        $path = $method = null;
        if (is_callable($callback = $this->_on_message))
            $this->_router->requestData = $callback($connection, $data, $path, $method);
        if (!$this->_router->locate($path ?? [], $method ?? Router::DEFAULT_METHOD))
            $connection->close('Bad request.');
    }

    function __construct(string $name, ?string $listen, ?array $ssl = null) {
        parent::__construct($name, $listen, $ssl);
        $this->workerConfig(DEFAULT_WORKER_CONFIG);
    }
    static function create(string $app, ?string $listen = null, ?array $ssl = null) {
        parent::create($app);
        return self::$_apps[$app] = new self($app, $listen, $ssl);
    }
}