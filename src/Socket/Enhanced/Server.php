<?php

namespace Acast\Socket\Enhanced;

use GatewayWorker\{
    BusinessWorker,
    Gateway as GatewayWorker,
    Lib\Gateway, Register
};
use Workerman\ {
    Worker
};

class Server extends \Acast\Server {
    /**
     * 路由实例
     * @var Router
     */
    protected $_router;
    /**
     * @var BusinessWorker
     */
    protected $_business_worker;

    protected $_on_business_start;

    protected $_on_connect;

    protected $_on_close;

    protected $_on_business_message;

    protected $_on_business_stop;
    /**
     * 收到请求回调
     *
     * @param string $client_id
     * @param string $data
     */
    function onMessage($client_id, $data) {
        if (!is_null($callback = $_SESSION['lock'])) {
            is_callable($callback) && $callback($client_id, $data);
            return;
        }
        $path = $method = null;
        if (is_callable($callback = $this->_on_message))
            $this->_router->requestData = $callback($client_id, $data, $path, $method);
        if (!$this->_router->locate($path ?? [], $method ?? Router::DEFAULT_METHOD))
            Gateway::closeCurrentClient('Bad request.');
    }
    function onConnect($client_id) {
        if (is_callable($this->_on_connect))
            call_user_func($this->_on_connect, $client_id);
    }
    function onClose($client_id) {
        if (is_callable($this->_on_close))
            call_user_func($this->_on_close, $client_id);
    }
    function onBusinessStart(Worker $worker) {
        self::$name = $this->_name;
        self::$memcached = new \Memcached();
        if (is_callable($this->_on_business_start))
            call_user_func($this->_on_business_start, $worker);
        self::$_status = self::_STATUS_STARTED;
    }
    function onBusinessStop(Worker $worker) {
        if (is_callable($this->_on_business_stop))
            call_user_func($this->_on_business_stop, $worker);
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
        parent::__construct($name, $listen, $ssl, GatewayWorker::class);
        $this->_worker->onWorkerStart = [$this, 'onServerStart'];
        $this->_worker->onWorkerStop = [$this, 'onServerStop'];
        $this->workerConfig(DEFAULT_BUSINESS_WORKER_CONFIG);

        $this->_business_worker = new BusinessWorker;
        $this->_business_worker->onWorkerStart = [$this, 'onBusinessStart'];
        $this->_business_worker->onWorkerStop = [$this, 'onBusinessStop'];
        $this->_business_worker->eventHandler = $this;
        $this->businessWorkerConfig(DEFAULT_GATEWAY_WORKER_CONFIG);
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
    /**
     * 配置BusinessWorker
     *
     * @param array $config
     */
    function businessWorkerConfig(array $config) {
        foreach ($config as $key => $value)
            $this->_business_worker->$key = $value;
    }
    function getBusinessWorkerProperty(string $name) {
        if (!($this->_business_worker instanceof Worker)) {
            Console::warning('Worker not initialized.');
            return false;
        }
        return $this->_business_worker->$name;
    }

    function businessWorkerEvent(string $event, callable $callback) {
        if (self::$_status > self::_STATUS_INITIAL) {
            Console::warning('Cannot set event callback once the service is started.');
            return;
        }
        if (!is_callable($callback)) {
            Console::warning('Failed to set event callback. Not callable.');
            return;
        }
        switch ($event) {
            case 'WorkerStart':
                $this->_on_business_start = $callback;
                break;
            case 'WorkerStop':
                $this->_on_business_stop = $callback;
                break;
            case 'Message':
                $this->_on_business_message = $callback;
                break;
            case 'Connect':
                $this->_on_connect = $callback;
                break;
            case 'Close':
                $this->_on_close = $callback;
                break;
            default:
                $this->_business_worker->{'on'.$event} = $callback;
        }
    }
    /**
     * 添加注册服务器
     *
     * @param $name
     * @param $listen
     */
    static function addRegister($name, $listen) {
        $register = new Register('text://'.$listen);
        $register->name = $name;
    }
}