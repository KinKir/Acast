<?php

namespace Acast\Socket;

use Workerman\ {
    Worker,
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
        if (!is_null($callback = $connection->lock)) {
            is_callable($callback) && $callback($connection, $data);
            return;
        }
        $path = $method = null;
        if (is_callable($callback = $this->_on_message))
            $this->_router->requestData = $callback($connection, $data, $path, $method);
        if (!$this->_router->locate($path ?? [], $method ?? Router::DEFAULT_METHOD))
            $connection->close('Bad request.');
    }
    /**
     * 服务停止回调
     *
     * @param Worker $worker
     */
    function onServerStop(Worker $worker) {
        parent::onServerStop($worker);
        foreach ($worker->connections as $connection)
            $connection->close();
    }
    /**
     * 构造函数
     *
     * @param string $name
     * @param null|string $listen
     * @param array|null $ssl
     */
    function __construct(string $name, ?string $listen, ?array $ssl = null) {
        parent::__construct($name, $listen, $ssl);
        $this->workerConfig(DEFAULT_WORKER_CONFIG);
    }
    /**
     * 创建服务
     *
     * @param string $app
     * @param null|string $listen
     * @param array|null $ssl
     * @return Server
     */
    static function create(string $app, ?string $listen = null, ?array $ssl = null) {
        parent::create($app);
        return self::$_apps[$app] = new self($app, $listen, $ssl);
    }
}