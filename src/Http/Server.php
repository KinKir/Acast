<?php

namespace Acast\Http;

use Acast\ {
    Console
};
use Workerman\ {
    Worker,
    Connection\TcpConnection,
    Protocols\Http,
    Protocols\HttpCache
};

class Server extends \Acast\Server {
    /**
     * @var Router
     */
    protected $_router;
    /**
     * 构造函数
     *
     * @param string $name
     * @param string $protocol
     * @param int|null $port
     * @param array $ssl
     */
    protected function __construct(string $name, string $protocol, ?int $port, array $ssl = null) {
        parent::__construct($name, $protocol, $port, $ssl);
        $this->workerConfig(DEFAULT_WORKER_CONFIG);
    }
    /**
     * 注册服务
     *
     * @param string $app
     * @param string $listen
     * @param array $ssl
     */
    static function create(string $app, ?string $listen = null, ?array $ssl = null) {
        parent::create($app);
        self::$_apps[$app] = new self($app, $listen, 'http', $ssl);
    }
    /**
     * 收到请求回调
     *
     * @param TcpConnection $connection
     * @param string $data
     */
    function onMessage(TcpConnection $connection, $data) {
        parent::onMessage($connection, $data);
        $connection->forward = false;
        $path = explode('/', substr(explode('?', $_SERVER['REQUEST_URI'], 2)[0], 1));
        if (empty($path[0]) && count($path) == 1)
            $path = [];
        if (!$this->_router->locate($path, $_SERVER['REQUEST_METHOD']))
            Http::header('HTTP', 404);
        if (ENABLE_SESSION)
            Http::sessionWriteClose();
        if (($connection->forward ?? false) == true)
            return;
        $connection->send($this->_router->retMsg ?? '');
    }
    /**
     * 服务启动回调
     *
     * @param Worker $worker
     */
    function onServerStart(Worker $worker){
        parent::onServerStart($worker);
        if (!isset($this->_router) && $this->_listen)
            Console::warning("No router bound to server \"$this->_name\".");
    }
    /**
     * 启动所有服务
     *
     * @param callable|null $callback
     */
    static function start(?callable $callback = null) {
        parent::start(function () use ($callback) {
            if (is_callable($callback))
                $callback();
            if (ENABLE_SESSION)
                HttpCache::init();
        });
    }
}