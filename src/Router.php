<?php

namespace Acast;
use Workerman\ {
    Connection\AsyncTcpConnection,
    Connection\TcpConnection
};
/**
 * 路由
 * @package Acast
 */
class Router
{
    /**
     * 路由常量
     */
    protected const _CALLBACK = '/0';
    protected const _MIDDLEWARE = '/1';
    protected const _CONTROLLER = '/2';
    protected const _NAME = '/3';
    protected const _PARAMETER = '/4';
    protected const _SIBLINGS = '/5';
    protected const _404 = '/404';
    /**
     * 路由列表
     * @var array
     */
    static $routers = [];
    /**
     * 设置别名的路由
     * @var array
     */
    protected $_alias = [];
    /**
     * 路由树
     * @var array
     */
    protected $_tree = [];
    /**
     * 设置指针
     * @var null
     */
    protected $_pSet;
    /**
     * 调用指针
     * @var null
     */
    protected $_pCall;
    /**
     * 中间件是否处于延时状态
     * @var bool
     */
    protected $_delayed = false;
    /**
     * 生成器列表
     * @var array
     */
    private $_generators = [];
    /**
     * GET参数
     * @var array
     */
    public $urlParams = [];
    /**
     * 中间件返回数据
     * @var mixed
     */
    public $mRet;
    /**
     * 返回参数
     * @var mixed
     */
    public $retMsg;
    /**
     * 连接实例
     * @var TcpConnection
     */
    public $connection;
    /**
     * HTTP请求方法
     * @var string
     */
    public $method;
    /**
     * 完整HTTP请求内容
     * @var string
     */
    public $rawRequest;
    /**
     * 构造函数
     */
    protected function __construct() {}
    /**
     * 创建路由实例
     * @param string $name
     */
    static function create(string $name) {
        if (!class_exists('Acast\\RouterWrapper'))
            eval('namespace Acast; class RouterWrapper extends Router{}');
        if (isset(self::$routers[$name]))
            Console::fatal("Router \"$name\" already exists.");
        self::$routers[$name] = new self;
    }
    /**
     * 获取路由实例
     *
     * @param string $name
     * @return Router
     */
    static function instance(string $name) : self {
        if (!isset(self::$routers[$name]))
            Console::fatal("Router \"$name\" do not exist.");
        return self::$routers[$name];
    }
    /**
     * 注册路由
     *
     * @param array|null $path
     * @param $methods
     * @param callable $callback
     * @return Router
     */
    function add(?array $path, $methods, callable $callback) : self {
        unset($this->_pSet);
        if (!($callback instanceof \Closure))
            $callback = \Closure::fromCallable($callback);
        $callback = \Closure::bind($callback, $this, 'Acast\\RouterWrapper');
        if (!is_array($methods))
            $methods = [$methods];
        $siblings = [];
        foreach ($methods as $method) {
            if (!isset($this->_tree[$method]))
                $this->_tree[$method] = [];
            $this->_pSet = &$this->_tree[$method];
            if (!isset($path)) {
                if (!isset($this->_pSet[self::_404]))
                    $this->_pSet[self::_404] = [];
                $this->_pSet = &$this->_pSet[self::_404];
            } else foreach ($path as $value) {
                if (strpos($value, '/') === 0) {
                    if (!isset($this->_pSet[self::_PARAMETER]))
                        $this->_pSet[self::_PARAMETER] = [];
                    $this->_pSet = &$this->_pSet[self::_PARAMETER];
                    if (isset($this->_pSet[self::_NAME]) && $this->_pSet[self::_NAME] != substr($value, 1))
                        Console::fatal("Failed to register route. Conflict in Parameter name \"$value\".");
                    $this->_pSet[self::_NAME] = substr($value, 1);
                } else {
                    if (!isset($this->_pSet[$value]))
                        $this->_pSet[$value] = [];
                    $this->_pSet = &$this->_pSet[$value];
                }
            }
            if (isset($this->_pSet[self::_CALLBACK]))
                Console::fatal("Conflict detected. Failed to register route.");
            $this->_pSet[self::_CALLBACK] = $callback;
            $this->_pSet[self::_MIDDLEWARE] = [];
            $this->_pSet[self::_CONTROLLER] = [];
            $siblings[] = &$this->_pSet;
        }
        foreach ($siblings as &$sibling)
            $sibling[self::_SIBLINGS] = &$siblings;
        return $this;
    }
    /**
     * 定位路由。该方法在收到HTTP请求后被调用
     *
     * @param array $path
     * @param string $method
     * @return bool
     */
    function locate(array $path, string $method) : bool {
        unset($this->urlParams, $this->retMsg, $this->mRet);
        $this->method = $method;
        if (!isset($this->_tree[$method]))
            goto Err;
        $this->_pCall = &$this->_tree[$method];
        $this->_mPush();
        foreach ($path as $value) {
            if (isset($this->_pCall[$value]))
                $this->_pCall = &$this->_pCall[$value];
            elseif (isset($this->_pCall[self::_PARAMETER])) {
                $this->_pCall = &$this->_pCall[self::_PARAMETER];
                $this->urlParams[$this->_pCall[self::_NAME]] = $value;
            } else goto Err;
            $this->_mPush();
        }
        Loop:
        if (isset($this->_pCall[self::_CALLBACK]))
            goto Success;
        if (isset($this->_pCall[self::_PARAMETER])) {
            $this->_pCall = &$this->_pCall[self::_PARAMETER];
            $this->urlParams[$this->_pCall[self::_NAME]] = '';
            $this->_mPush();
            goto Loop;
        }
        Err:
        if (isset($this->_tree[$method][self::_404])) {
            $this->_pCall = &$this->_tree[$method][self::_404];
            $this->_mPush();
            goto Success;
        }
        $this->retMsg = View::http(404, 'Not found.');
        return false;
        Success:
        $this->_call();
        return true;
    }
    /**
     * 路由分发
     *
     * @param $name
     * @return bool
     */
    protected function dispatch($name) : bool {
        if (!is_array($name))
            $name = [$name];
        foreach ($name as $route) {
            $this->_pCall = &$this->_alias[$route];
            if (!$this->_routerCall())
                return false;
        }
        return true;
    }
    /**
     * 路由事件处理，包括中间件和路由回调
     *
     * @return bool
     */
    private function _call() : bool {
        if (!isset($this->_pCall)) {
            Console::warning('Failed to call. Invalid pointer.');
            return false;
        }
        foreach ($this->_generators as $generator)
            $generator->current();
        $ret = $this->_routerCall();
        while ($generator = array_pop($this->_generators)) {
            if ($generator->valid())
                 $generator->next();
        }
        $this->_generators = [];
        return $ret;
    }
    /**
     * 添加生成器
     */
    private function _mPush() {
        $this->_generators[] = $this->_mCall($this->_pCall);
    }
    /**
     * 将中间件回调封装到生成器中待调用
     *
     * @param $pCall
     * @return \Generator
     */
    private function _mCall(&$pCall) {
        if (!isset($pCall[self::_MIDDLEWARE]))
            return;
        foreach ($pCall[self::_MIDDLEWARE] as $callback) {
            if ($this->_delayed) {
                $this->_delayed = false;
                yield;
            }
            if (!($callback() ?? true))
                break;
        }
        $this->_delayed = false;
        yield;
    }
    /**
     * 调用路由回调
     *
     * @return bool
     */
    private function _routerCall() : bool {
        $status = $this->connection->getStatus();
        if ($status === TcpConnection::STATUS_CLOSING || $status === TcpConnection::STATUS_CLOSED)
            return false;
        $callback = $this->_pCall[self::_CALLBACK];
        try {
            return $callback() ?? true;
        } catch (\PDOException $exception) {
            $this->retMsg = View::http(500, $exception->getMessage());
            return false;
        }
    }
    /**
     * 中间件延时
     */
    protected function delay() {
        $this->_delayed = true;
    }
    /**
     * 路由别名，用于实现分发
     *
     * @param mixed $names
     * @return Router
     */
    function alias($names) : self {
        if (!isset($this->_pSet)) {
            Console::warning("No route to alias.");
            return $this;
        }
        if (!is_array($names))
            $names = [$names];
        foreach ($names as $name) {
            if (isset($this->_alias[$name]))
                Console::warning("Overwriting route alias \"$name\".");
            $this->_alias[$name] = &$this->_pSet;
        }
        return $this;
    }
    /**
     * 调用已注册的控制器中的方法
     *
     * @param string|int $name
     * @param mixed $param
     * @return mixed
     */
    protected function invoke($name = 0, $param = null) {
        $controller = $this->_pCall[self::_CONTROLLER][$name] ?? Controller::$globals[$name] ?? null;
        if (!isset($controller)) {
            Console::warning("Invalid controller binding \"$name\".");
            return false;
        }
        $class = $controller[0];
        $method = $controller[1];
        $object = new $class($this);
        $ret = $object->$method($param);
        $this->mRet = $object->mRet;
        $this->retMsg = $object->retMsg;
        return $ret;
    }
    /**
     * 转发HTTP请求
     *
     * @param string $name
     */
    protected function forward(string $name) {
        $this->connection->forward = true;
        if (!isset($this->connection->remotes[$name]))
            $this->connection->remotes[$name] = new AsyncTcpConnection(Config::get('FORWARD_'.$name));
        $remote = $this->connection->remotes[$name];
        $remote->pipe($this->connection);
        $this->connection->onClose = function () use ($remote) {
            $remote->close();
        };
        $remote->connect();
        $remote->send($this->rawRequest);
    }
    /**
     * 绑定控制器及其方法
     *
     * @param array $controllers
     * @return Router
     */
    function bind(array $controllers) : self {
        if (!isset($this->_pSet[self::_SIBLINGS])) {
            Console::warning("No route to bind.");
            return $this;
        }
        if (!is_array($controllers[0]))
            $controllers = [$controllers];
        foreach ($this->_pSet[self::_SIBLINGS] as &$pSet) {
            foreach ($controllers as $controller) {
                $count = count($controller);
                if ($count == 3)
                    [$name, $controller, $method] = $controller;
                elseif ($count == 2) {
                    [$controller, $method] = $controller;
                    $name = count($this->_pSet[self::_CONTROLLER]);
                } else {
                    Console::warning("Invalid controller binding,");
                    continue;
                }
                $controller = Server::$name.'\\Controller\\'.$controller;
                if (!class_exists($controller) || !is_subclass_of($controller, Controller::class)) {
                    Console::warning("Invalid controller \"$controller\".");
                    return $this;
                }
                if (!method_exists($controller, $method)) {
                    Console::warning("Invalid method \"$method\".");
                    return $this;
                }
                if (isset($pSet[self::_CONTROLLER][$name]))
                    Console::warning("Overwriting controller binding \"$name\"");
                $pSet[self::_CONTROLLER][$name] = [$controller, $method];
            }
        }
        return $this;
    }
    /**
     * 绑定中间件
     *
     * @param mixed $names
     * @return Router
     */
    function middleware($names) : self {
        if (!isset($this->_pSet[self::_SIBLINGS])) {
            Console::warning("No route to bind middleware.");
            return $this;
        }
        if (!is_array($names))
            $names = [$names];
        foreach ($this->_pSet[self::_SIBLINGS] as &$pSet) {
            foreach ($names as $name) {
                if ($name[0] == '#') {
                    if (DEBUG_MODE)
                        $name = substr($name, 1);
                    else continue;
                }
                $callback = Middleware::fetch($name);
                if ($callback) {
                    if (!is_callable($callback)) {
                        Console::warning('Failed to bind middleware. Callback function not callable.');
                        continue;
                    }
                    if (!($callback instanceof \Closure))
                        $callback = \Closure::fromCallable($callback);
                    $callback = \Closure::bind($callback, $this, 'Acast\\RouterWrapper');
                    $pSet[self::_MIDDLEWARE][] = $callback;
                }
            }
        }
        return $this;
    }
}