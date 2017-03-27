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
     * 服务列表
     * @var array
     */
    protected static $_apps = [];
    /**
     * Workerman Worker实例
     * @var Worker
     */
    protected $_worker = null;
    /**
     * 绑定的路由
     * @var Router|null
     */
    protected $_route = null;
    /**
     * 客户端连接实例
     * @var TcpConnection
     */
    protected $_connection = null;
    /**
     * 服务启动的回调
     * @var callable
     */
    protected $_on_start = null;
    /**
     * 服务停止的回调
     * @var callable
     */
    protected $_on_stop = null;
    /**
     * 服务名
     * @var string
     */
    public $name = null;
    /**
     * 构造函数
     * .
     * @param string $name
     * @param int $listen
     */
    protected function __construct(string $name, int $listen) {
        $this->name = $name;
        $this->_worker = new Worker('http://[::]:'.$listen);
        $this->_worker->onWorkerStart = [$this, 'onServerStart'];
        $this->_worker->onWorkerStop = [$this, 'onServerStop'];
        $this->_worker->onMessage = [$this, 'onMessage'];
        $this->_route = new Router($name);
    }
    /**
     * 配置Workerman
     *
     * @param array $config
     */
    function config(array $config) {
        foreach ($config as $key => $value) {
            $this->_worker->$key = $value;
        }
    }
    /**
     * 选择服务
     *
     * @param string $app
     * @return Server
     */
    static function app(string $app) : self {
        if (!isset(self::$_apps[$app]))
            Console::Fatal("Failed to fetch app. App \"$app\" not exist!");
        return self::$_apps[$app];
    }
    /**
     * 注册服务
     *
     * @param string $app
     * @param string $listen
     */
    static function create(string $app, string $listen) {
        if (isset(self::$_apps[$app]))
            Console::Fatal("Failed to create app. App \"$app\" exists!");
        self::$_apps[$app] = new self($app, $listen);
    }
    /**
     * 收到请求回调
     *
     * @param TcpConnection $connection
     */
    function onMessage(TcpConnection $connection) {
        $this->_connection = $connection;
        $path = explode('/', substr($_SERVER['REQUEST_URI'], 1));
        if (empty($path[0]) && count($path) == 1)
            $path = [];
        $this->_route->locate($path, $_SERVER['REQUEST_METHOD']);
        $connection->close($this->_route->retMsg);
    }
    /**
     * 绑定事件回调
     *
     * @param string $event
     * @param callable $callback
     */
    function event(string $event, callable $callback) {
        if (!is_callable($callback)) {
            Console::Warning('Failed to set event callback. Not callable.');
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
                Console::Warning("Unsupported event \"$event\".");
        }
    }
    /**
     * 注册路由
     *
     * @param array $path
     * @param $methods
     * @param callable $callback
     * @return Router
     */
    function route(array $path, $methods, callable $callback) : Router {
        return $this->_route->add($path, $methods, $callback);
    }
    /**
     * 服务启动回调
     *
     * @param Worker $worker
     */
    function onServerStart(Worker $worker) {
        Model::init($this->name);
        View::init($this->name);
        Controller::init($this->name);
        if (is_callable($this->_on_stop))
            call_user_func($this->_on_stop, $worker);
    }
    /**
     * 服务停止回调
     *
     * @param Worker $worker
     */
    function onServerStop(Worker $worker) {
        View::destroy();
        if (is_callable($this->_on_stop))
            call_user_func($this->_on_stop, $worker);
    }
    /**
     * 启动所有服务
     */
    static function start() {
        Worker::runAll();
    }
}