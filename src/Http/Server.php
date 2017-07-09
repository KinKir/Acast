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
     * @param string|null $listen
     * @param array|null $ssl
     */
    protected function __construct(string $name, ?string $listen, ?array $ssl) {
        parent::__construct($name, $listen, $ssl);
        $this->_worker->onWorkerStart = [$this, 'onServerStart'];
        $this->_worker->onWorkerStop = [$this, 'onServerStop'];
        $this->_worker->onMessage = [$this, 'onMessage'];
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
        self::$_apps[$app] = new self($app, $listen ? 'http://'.$listen : null, $ssl);
    }
    /**
     * 收到请求回调
     *
     * @param TcpConnection $connection
     * @param string $data
     */
    function onMessage(TcpConnection $connection, $data) {
        $this->_router->connection = $this->_connection = $connection;
        $this->_router->requestData = $data;
        $connection->forward = false;
        $path = explode('/', substr(explode('?', $_SERVER['REQUEST_URI'], 2)[0], 1));
        if (empty($path[0]) && count($path) == 1)
            $path = [];
        if (!$this->_router->locate($path, $_SERVER['REQUEST_METHOD']))
            $this->_router->retMsg = View::http(404, 'Not found.');
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