<?php

namespace Acast;
use Workerman\Connection\TcpConnection;
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
    protected const _IN_FILTER = '/1';
    protected const _OUT_FILTER = '/2';
    protected const _CONTROLLER= '/3';
    protected const _NAME = '/4';
    protected const _PARAMETER = '/5';
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
    protected $_pSet = null;
    /**
     * 调用指针
     * @var null
     */
    protected $_pCall = null;
    /**
     * GET参数
     * @var array
     */
    public $urlParams = [];
    /**
     * 中间件返回数据
     * @var mixed
     */
    public $filterMsg = null;
    /**
     * 返回参数
     * @var mixed
     */
    public $retMsg = null;
    /**
     * 连接实例
     * @var TcpConnection
     */
    public $connection = null;
    /**
     * HTTP请求方法
     * @var string
     */
    public $method = null;
    /**
     * 构造函数
     */
    protected function __construct() {}
    /**
     * 创建路由实例
     * @param string $name
     */
    static function create(string $name) {
        if (isset(self::$routers[$name]))
            Console::fatal("Router \"$name\" already exists.");
        self::$routers[$name] = new self();
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
        $callback = \Closure::bind($callback, $this, __CLASS__);
        if (!is_array($methods))
            $methods = [$methods];
        if (is_null($path)) {
            if (!isset($this->_tree[self::_404]))
                $this->_tree[self::_404] = [];
            $this->_pSet = $this->_tree[self::_404];
            if (isset($this->_pSet[self::_CALLBACK]))
                Console::fatal("Conflict detected. Failed to register route.");
            $this->_pSet[self::_CALLBACK] = $callback;
            $this->_pSet[self::_IN_FILTER] = [];
            $this->_pSet[self::_OUT_FILTER] = [];
            $this->_pSet[self::_CONTROLLER] = [];
        }
        foreach ($methods as $method) {
            if (!isset($this->_tree[$method]))
                $this->_tree[$method] = [];
            $this->_pSet = &$this->_tree[$method];
            foreach ($path as $value) {
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
            $this->_pSet[self::_IN_FILTER] = [];
            $this->_pSet[self::_OUT_FILTER] = [];
            $this->_pSet[self::_CONTROLLER] = [];
        }
        return $this;
    }
    /**
     * 定位路由。该方法在收到HTTP请求后被调用
     *
     * @param array $path
     * @param string $method
     */
    function locate(array $path, string $method) {
        unset($this->_pCall, $this->urlParams, $this->retMsg, $this->filterMsg);
        $this->method = $method;
        if (!isset($this->_tree[$method])) {
            $this->retMsg = View::http(400, 'Invalid method.');
            return;
        }
        $this->_pCall = &$this->_tree[$method];
        foreach ($path as $value) {
            if (isset($this->_pCall[$value]))
                $this->_pCall = &$this->_pCall[$value];
            elseif (isset($this->_pCall[self::_PARAMETER])) {
                $this->_pCall = &$this->_pCall[self::_PARAMETER];
                $this->urlParams[$this->_pCall[self::_NAME]] = $value;
            } else goto Err;
        }
        Loop:
        if (isset($this->_pCall[self::_CALLBACK])) {
            $this->call();
            return;
        }
        if (isset($this->_pCall[self::_PARAMETER])) {
            $this->_pCall = &$this->_pCall[self::_PARAMETER];
            $this->urlParams[$this->_pCall[self::_NAME]] = '';
        } else goto Err;
        goto Loop;
        Err:
        if (isset($this->_tree[self::_404][self::_CALLBACK])) {
            $this->_pCall = &$this->_tree[self::_404];
            $this->call();
        } else
            $this->retMsg = View::http(404, 'Not found.');
    }
    /**
     * 路由分发
     *
     * @param $name
     * @return bool
     */
    function dispatch($name) : bool {
        if (is_array($name)) {
            foreach ($name as $route) {
                $this->_pCall = &$this->_alias[$route];
                if (!$this->call())
                    return false;
            }
            return true;
        } else {
            $this->_pCall = &$this->_alias[$name];
            return $this->call();
        }
    }
    /**
     * 路由事件处理，包括中间件和路由回调
     *
     * @return bool
     */
    protected function call() : bool {
        if (!isset($this->_pCall)) {
            Console::warning('Failed to call. Invalid pointer.');
            return false;
        }
        foreach ($this->_pCall[self::_IN_FILTER] as $in_filter) {
            if (!($in_filter($this->method) ?? true))
                break;
        }
        $status = $this->connection->getStatus();
        if ($status === TcpConnection::STATUS_CLOSING || $status === TcpConnection::STATUS_CLOSED)
            return false;
        $callback = $this->_pCall[self::_CALLBACK];
        $ret = $callback($this->method) ?? true;
        foreach ($this->_pCall[self::_OUT_FILTER] as $out_filter) {
            if (!($out_filter($this->method) ?? true))
                break;
        }
        return $ret;
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
                Console::notice("Overwriting route alias \"$name\".");
            $this->_alias[$name] = [
                self::_CALLBACK => &$this->_pSet[self::_CALLBACK],
                self::_IN_FILTER => &$this->_pSet[self::_IN_FILTER],
                self::_OUT_FILTER => &$this->_pSet[self::_OUT_FILTER],
                self::_CONTROLLER => &$this->_pSet[self::_CONTROLLER],
            ];
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
    function invoke($name = 0, $param = null) {
        if (!isset($this->_pCall[self::_CONTROLLER][$name])) {
            Console::warning("Invalid controller binding \"$name\".");
            return false;
        }
        $class = $this->_pCall[self::_CONTROLLER][$name][0];
        $method = $this->_pCall[self::_CONTROLLER][$name][1];
        $object = new $class($this);
        $ret = $object->$method($param);
        $this->retMsg = $object->retMsg;
        return $ret;
    }
    /**
     * 绑定控制器及其方法
     *
     * @param array $controllers
     * @return Router
     */
    function bind(array $controllers) : self {
        if (!isset($this->_pSet)) {
            Console::warning("No route to bind.");
            return $this;
        }
        if (!is_array($controllers[0]))
            $controllers = [$controllers];
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
            if (isset($this->_pSet[self::_CONTROLLER][$name]))
                Console::warning("Overwriting controller binding \"$name\"");
            $this->_pSet[self::_CONTROLLER][$name] = [$controller, $method];
        }
        return $this;
    }
    /**
     * 绑定中间件
     *
     * @param $filters
     * @return Router
     */
    function filter(array $filters) : self {
        if (!isset($this->_pSet)) {
            Console::warning("No route to filter.");
            return $this;
        }
        foreach ($filters as $filter => $type) {
            $callback = Filter::fetch($filter, $type);
            if ($callback) {
                if (!is_callable($callback)) {
                    Console::warning('Failed to bind filter. Callback function not callable.');
                    continue;
                }
                if (!($callback instanceof \Closure))
                    $callback = \Closure::fromCallable($callback);
                $callback = \Closure::bind($callback, $this, __CLASS__);
                $this->_pSet[$type == Filter::IN ? self::_IN_FILTER : self::_OUT_FILTER][] = $callback;
            }
        }
        return $this;
    }
}