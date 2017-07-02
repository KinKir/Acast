<?php

namespace Acast;
use Workerman\ {
    Worker, Connection\TcpConnection
};
/**
 * 服务
 * @package Acast
 */
class Server {
    /**
     * 服务状态
     */
    protected const _STATUS_INITIAL = 0;
    protected const _STATUS_STARTING = 1;
    protected const _STATUS_STARTED = 2;
    /**
     * 服务列表
     * @var array
     */
    protected static $_apps = [];
    /**
     * Workerman Worker实例
     * @var Worker
     */
    protected $_worker;
    /**
     * 绑定的路由
     * @var Router|null
     */
    protected $_router;
    /**
     * 客户端连接实例
     * @var TcpConnection
     */
    protected $_connection;
    /**
     * 服务启动的回调
     * @var callable
     */
    protected $_on_start;
    /**
     * 服务停止的回调
     * @var callable
     */
    protected $_on_stop;
    /**
     * 服务名
     * @var string
     */
    protected $_name;
    /**
     * 是否监听端口
     * @var bool
     */
    protected $_listen;
    /**
     * 服务启动状态
     * @var int
     */
    protected static $_status = self::_STATUS_INITIAL;
    /**
     * Memcached实例
     * @var \Memcached
     */
    static $memcached;
    /**
     * 服务名（进程空间）
     * @var string
     */
    static $name;
    /**
     * 是否转发
     * @var bool
     */
    static $forward;
    /**
     * 构造函数
     *
     * @param string $name
     * @param int|null $listen
     * @param array $ssl
     */
    protected function __construct(string $name, ?int $listen, array $ssl = null) {
        $this->_name = $name;
        $this->_listen = isset($listen);
        $this->_worker = new Worker(
            $this->_listen ? 'http://[::]:'.$listen : '',
            $ssl ? ['ssl' => $ssl] : []
        );
        if ($ssl)
            $this->_worker->transport = 'ssl';
        $this->_worker->onWorkerStart = [$this, 'onServerStart'];
        $this->_worker->onWorkerStop = [$this, 'onServerStop'];
        $this->_worker->onMessage = [$this, 'onMessage'];
        $this->workerConfig(DEFAULT_WORKER_CONFIG);
    }
    /**
     * 配置Workerman
     *
     * @param array $config
     */
    function workerConfig(array $config) {
        foreach ($config as $key => $value)
            $this->_worker->$key = $value;
    }
    /**
     * 选择服务
     *
     * @param string $app
     * @return Server
     */
    static function app(string $app) : self {
        if (!isset(self::$_apps[$app]))
            Console::fatal("Failed to fetch app. App \"$app\" not exist!");
        return self::$_apps[$app];
    }
    /**
     * 注册服务
     *
     * @param string $app
     * @param string $listen
     * @param array $ssl
     */
    static function create(string $app, ?string $listen = null, ?array $ssl = null) {
        if (self::$_status > self::_STATUS_INITIAL) {
            Console::warning('Cannot create application once the service is started.');
            return;
        }
        if (isset(self::$_apps[$app]))
            Console::fatal("Failed to create app. App \"$app\" exists!");
        self::$_apps[$app] = new self($app, $listen, $ssl);
    }
    /**
     * 收到请求回调
     *
     * @param TcpConnection $connection
     * @param string $data
     */
    function onMessage(TcpConnection $connection, $data) {
        $this->_router->connection = $this->_connection = $connection;
        $connection->forward = false;
        $path = explode('/', substr(explode('?', $_SERVER['REQUEST_URI'], 2)[0], 1));
        if (empty($path[0]) && count($path) == 1)
            $path = [];
        $this->_router->rawRequest = $data;
        $this->_router->locate($path, $_SERVER['REQUEST_METHOD']);
        if (($connection->forward ?? false) == true)
            return;
        $connection->send($this->_router->retMsg ?? '');
    }
    /**
     * 绑定事件回调
     *
     * @param string $event
     * @param callable $callback
     */
    function event(string $event, callable $callback) {
        if (self::$_status > self::_STATUS_INITIAL) {
            Console::warning('Cannot set event callback once the service is started.');
            return;
        }
        if (!is_callable($callback)) {
            Console::warning('Failed to set event callback. Not callable.');
            return;
        }
        switch ($event) {
            case 'start':
                $this->_on_start = $callback;
                break;
            case 'stop':
                $this->_on_stop = $callback;
                break;
            case 'bufferFull':
                $this->_worker->onBufferFull = $callback;
                break;
            case 'bufferDrain':
                $this->_worker->onBufferDrain = $callback;
                break;
            default:
                Console::warning("Unsupported event \"$event\".");
        }
    }
    /**
     * 绑定路由实例
     *
     * @param string $name
     */
    function route(string $name) {
        if (self::$_status > self::_STATUS_STARTING) {
            Console::warning('Cannot bind route once the service is started.');
            return;
        }
        if (isset($this->_router))
            Console::warning("Overwriting router binding for app \"$this->_name\"");
        $this->_router = Router::instance($name);
    }
    /**
     * 服务启动回调
     *
     * @param Worker $worker
     */
    function onServerStart(Worker $worker) {
        self::$name = $this->_name;
        self::$memcached = new \Memcached();
        pcntl_signal(SIGCHLD, SIG_IGN); //将子进程转交给内核，防止僵尸进程。
        if (is_callable($this->_on_start))
            call_user_func($this->_on_start, $worker);
        if (!isset($this->_router) && $this->_listen)
            Console::warning("No router bound to server \"$this->_name\".");
        self::$_status = self::_STATUS_STARTED;
    }
    /**
     * 服务停止回调
     *
     * @param Worker $worker
     */
    function onServerStop(Worker $worker) {
        if (is_callable($this->_on_stop))
            call_user_func($this->_on_stop, $worker);
    }
    /**
     * 启动所有服务
     *
     * @param callable|null $callback
     */
    static function start(?callable $callback = null) {
        if (count($_SERVER['argv']) > 1) {
            $name = $_SERVER['argv'][1];
            if (in_array($name, array_keys(Console::$callbacks))) {
                Console::call($name, array_slice($_SERVER['argv'], 2));
                exit(0);
            }
        }
        self::$_status = self::_STATUS_STARTING;
        if (isset($callback)) {
            if (!is_callable($callback))
                Console::fatal('Invalid onStart callback.');
            $callback();
        }
        Worker::runAll();
    }
    /**
     * 在当前位置创建一个子进程，并执行回调。
     *
     * @param callable $callback
     * @param mixed $params
     * @return int;
     */
    static function async(callable $callback, $params = null) {
        if (!is_callable($callback)) {
            Console::warning('Callback function not callable.');
            return 0;
        }
        $pid = pcntl_fork();
        if ($pid == 0) {
            if (!is_array($params))
                $params = [$params];
            call_user_func_array($callback, $params);
            Worker::$status = Worker::STATUS_SHUTDOWN;
            exit(0);
        }
        return $pid;
    }
    /**
     * 获取运行状态
     *
     * @return int
     */
    static function getStatus() {
        return self::$_status;
    }
    /**
     * 获取Worker配置参数
     *
     * @param $name
     * @return mixed
     */
    function getWorkerProperty(string $name) {
        if (!($this->_worker instanceof Worker)) {
            Console::warning('Worker not initialized.');
            return false;
        }
        return $this->_worker->$name;
    }
}