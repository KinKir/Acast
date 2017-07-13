<?php

namespace Acast\Socket\Enhanced;

use GatewayWorker\ {
    BusinessWorker, Register,
    Gateway as GatewayWorker
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
     * 处理业务逻辑的Worker
     * @var BusinessWorker
     */
    protected $_business_worker;
    /**
     * @var callable
     */
    protected $_on_business_start;
    /**
     * @var callable
     */
    protected $_on_connect;
    /**
     * @var callable
     */
    protected $_on_close;
    /**
     * @var callable
     */
    protected $_on_business_message;
    /**
     * @var callable
     */
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
        if (is_callable($callback = $this->_on_business_message))
            $this->_router->requestData = $callback($client_id, $data, $path, $method);
        $this->_router->client_id = $client_id;
        $this->_router->locate($path ?? [], $method ?? Router::DEFAULT_METHOD);
    }
    /**
     * 建立与BusinessWorker的连接后执行此回调
     *
     * @param $client_id
     */
    function onConnect($client_id) {
        if (is_callable($this->_on_connect))
            call_user_func($this->_on_connect, $client_id);
    }
    /**
     * 断开与BusinessWorker的连接后执行此回调
     *
     * @param $client_id
     */
    function onClose($client_id) {
        if (is_callable($this->_on_close))
            call_user_func($this->_on_close, $client_id);
    }
    /**
     * BusinessWorker启动回调
     *
     * @param Worker $worker
     */
    function onBusinessStart(Worker $worker) {
        self::$name = $this->_name;
        self::$memcached = new \Memcached();
        if (is_callable($this->_on_business_start))
            call_user_func($this->_on_business_start, $worker);
        self::$_status = self::_STATUS_STARTED;
    }
    /**
     * BusinessWorker停止回调
     *
     * @param Worker $worker
     */
    function onBusinessStop(Worker $worker) {
        if (is_callable($this->_on_business_stop))
            call_user_func($this->_on_business_stop, $worker);
    }
    /**
     * {@inheritdoc}
     */
    function onServerStop(Worker $worker) {
        parent::onServerStop($worker);
        foreach ($worker->connections as $connection)
            $connection->close();
    }
    /**
     * {@inheritdoc}
     */
    function __construct(string $name, ?string $listen, ?array $ssl = null) {
        if ($listen) {
            parent::__construct($name, $listen, $ssl, GatewayWorker::class);
            $this->_worker->onWorkerStart = [$this, 'onServerStart'];
            $this->_worker->onWorkerStop = [$this, 'onServerStop'];
            $this->workerConfig(DEFAULT_GATEWAY_WORKER_CONFIG);
        }
    }
    /**
     * 创建服务
     *
     * @param string $app
     * @param null|string $listen
     * @param array|null $ssl
     * @param bool $businessWorker
     * @return Server
     */
    static function create(string $app, ?string $listen = null, ?array $ssl = null, bool $businessWorker = true) {
        parent::create($app);
        self::$_apps[$app] = new self($app, $listen, $ssl);
        if ($businessWorker)
            self::$_apps[$app]->addBusinessWorker();
        return self::$_apps[$app];
    }
    /**
     * 单独添加BusinessWorker
     */
    function addBusinessWorker() {
        $this->_business_worker = new BusinessWorker;
        $this->_business_worker->onWorkerStart = [$this, 'onBusinessStart'];
        $this->_business_worker->onWorkerStop = [$this, 'onBusinessStop'];
        $this->_business_worker->eventHandler = $this;
        $this->businessWorkerConfig(DEFAULT_BUSINESS_WORKER_CONFIG);
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
    /**
     * 获取BusinessWorker属性
     *
     * @param string $name
     * @return bool
     */
    function getBusinessWorkerProperty(string $name) {
        if (!($this->_business_worker instanceof Worker)) {
            Console::warning('Worker not initialized.');
            return false;
        }
        return $this->_business_worker->$name;
    }
    /**
     * 为BusinessWorker注册事件
     *
     * @param string $event
     * @param callable $callback
     */
    function businessEvent(string $event, callable $callback) {
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
     * @param string $name
     * @param string $listen
     */
    static function addRegister(string $name, string $listen) {
        $register = new Register('text://'.$listen);
        $register->name = $name;
    }
}