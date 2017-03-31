<?php

namespace Acast;
use Workerman\Connection\TcpConnection;

/**
 * 路由
 * @package Acast
 */
class Router {
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
     * 注册路由。
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
            if (!isset($this->_tree['/404']))
                $this->_tree['/404'] = [];
            $this->_pSet = $this->_tree['/404'];
            if (isset($this->_pSet['/func']))
                Console::fatal("Conflict detected. Failed to register route.");
            $this->_pSet['/func'] = $callback;
            $this->_pSet['/in'] = [];
            $this->_pSet['/out'] = [];
            $this->_pSet['/ctrl'] = null;
        }
        foreach ($methods as $method) {
            if (!isset($this->_tree[$method]))
                $this->_tree[$method] = [];
            $this->_pSet = &$this->_tree[$method];
            foreach ($path as $value) {
                if (strpos($value, '/') === 0) {
                    if (!isset($this->_pSet->{'/var'}))
                        $this->_pSet['/var'] = [];
                    $this->_pSet = &$this->_pSet['/var'];
                    if (isset($this->_pSet['/name']))
                        Console::fatal("Failed to register route. Conflict in Parameter name \"$value\".");
                    $this->_pSet['/name'] = substr($value, 1);
                } else {
                    if (!isset($this->_pSet[$value]))
                        $this->_pSet[$value] = [];
                    $this->_pSet = &$this->_pSet[$value];
                }
            }
            if (isset($this->_pSet['/func']))
                Console::fatal("Conflict detected. Failed to register route.");
            $this->_pSet['/func'] = $callback;
            $this->_pSet['/in'] = [];
            $this->_pSet['/out'] = [];
            $this->_pSet['/ctrl'] = null;
        }
        return $this;
    }
    /**
     * 定位路由。该方法在收到HTTP请求后被调用。
     *
     * @param array $path
     * @param string $method
     */
    function locate(array $path, string $method) {
        unset($this->_pCall, $this->urlParams, $this->retMsg, $this->filterMsg);
        if (!isset($this->_tree[$method])) {
            $this->retMsg = Respond::err(400, 'Invalid method.');
            return;
        }
        $this->_pCall = &$this->_tree[$method];
        foreach ($path as $value) {
            if (isset($this->_pCall[$value]))
                $this->_pCall = &$this->_pCall[$value];
            elseif (isset($this->_pCall['/var'])) {
                $this->_pCall = &$this->_pCall['/var'];
                $this->urlParams[$this->_pCall['/name']] = $value;
            } else goto Err;
        }
        Loop:
        if (isset($this->_pCall['/func'])) {
            $this->call();
            return;
        }
        if (isset($this->_pCall['/var'])) {
            $this->_pCall = &$this->_pCall['/var'];
            $this->urlParams[$this->_pCall['/name']] = '';
        } else goto Err;
        goto Loop;
        Err:
        if (isset($this->_tree['/404']['/func'])) {
            $this->_pCall = $this->_tree['/404'];
            $this->call();
        } else
            $this->retMsg = Respond::err(404, 'Not found.');
    }
    /**
     * 路由分发。
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
     * 路由事件处理，包括中间件和路由回调。
     *
     * @return bool
     */
    protected function call() : bool {
        if (!isset($this->_pCall)) {
            Console::warning('Failed to call. Invalid pointer.');
            return false;
        }
        foreach ($this->_pCall['/in'] as $in_filter) {
            if (!($in_filter() ?? true))
                break;
        }
        $status = $this->connection->getStatus();
        if ($status === TcpConnection::STATUS_CLOSING || $status === TcpConnection::STATUS_CLOSED)
            return false;
        $callback = $this->_pCall['/func'];
        $ret = $callback() ?? true;
        foreach ($this->_pCall['/out'] as $out_filter) {
            if (!($out_filter() ?? true))
                break;
        }
        return $ret;
    }
    /**
     * 路由别名，用于实现分发。
     *
     * @param mixed $names
     * @return Router
     */
    function alias($names) : self {
        if (!isset($this->_pSet)) {
            Console::warning("No route to alias..");
            return $this;
        }
        if (!is_array($names))
            $names = [$names];
        foreach ($names as $name) {
            if (isset($this->_alias[$name]))
                Console::notice("Overwriting route alias \"$name\".");
            $this->_alias[$name] = [
                '/func' => $this->_pSet['/func'],
                '/in' => $this->_pSet['/in'],
                '/out' => $this->_pSet['/out'],
                '/ctrl' => $this->_pSet['/ctrl']
            ];
        }
        return $this;
    }
    /**
     * 调用已注册的控制器中的方法。
     *
     * @param string $name
     * @param mixed $param
     * @return mixed
     */
    function invoke(string $name, $param = null) {
        if (!isset($this->_pCall['/ctrl'][$name])) {
            Console::warning("Invalid controller binding.\"$name\"");
            return false;
        }
        $class = $this->_pCall['/ctrl'][$name][0];
        $method = $this->_pCall['/ctrl'][$name][1];
        $object = new $class(Server::$name, $this);
        $ret = $object->$method($param);
        $this->retMsg = $object->retMsg;
        return $ret;
    }
    /**
     * 绑定控制器及其方法。
     *
     * @param array $controllers
     * @return Router
     */
    function bind(array $controllers) : self {
        if (!is_array($controllers[0]))
            $controllers = [$controllers];
        foreach ($controllers as $controller) {
            if (count($controller) != 3) {
                Console::warning("Invalid controller binding,");
                continue;
            }
            [$name, $controller, $method] = $controller;
            if (!isset($this->_pSet)) {
                Console::warning("No route to bind.");
                return $this;
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
            if (isset($this->_pSet['/ctrl'][$name]))
                Console::warning("Overwriting controller binding \"$name\"");
            $this->_pSet['/ctrl'][$name] = [$controller, $method];
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
                $this->_pSet[$type == Filter::_IN_ ? '/in' : '/out'][] = $callback;
            }
        }
        return $this;
    }
}