<?php

namespace Acast;
/**
 * 控制器
 * @package Acast
 */
abstract class Controller {
    /**
     * 中间件返回信息
     * @var mixed
     */
    public $filterMsg;
    /**
     * 返回数据
     * @var mixed
     */
    public $retMsg;
    /**
     * GET参数
     * @var array
     */
    protected $urlParams = [];
    /**
     * 绑定的模型
     * @var Model
     */
    protected $model;
    /**
     * 绑定的视图
     * @var View
     */
    protected $view;
    /**
     * HTTP请求方法
     * @var string
     */
    protected $method;
    /**
     * 构造函数，绑定模型、视图
     *
     * @param Router $route
     */
    function __construct(Router $route) {
        $temp = explode('\\', get_called_class());
        $name = Server::$name.'\\View\\'.end($temp);
        if (class_exists($name))
            $this->view = new $name($this);
        $name = Server::$name.'\\Model\\'.end($temp);
        if (class_exists($name))
            $this->model = new $name($this->view);
        $this->method = $route->method;
        $this->urlParams = $route->urlParams ?? [];
        $this->retMsg = $route->retMsg ?? null;
        $this->filterMsg = $route->filterMsg ?? null;
    }
    /**
     * 调用外部模型
     *
     * @param string $name
     * @return Model|null
     */
    protected function invoke(string $name) : ?Model {
        $class = Server::$name.'\\Model\\'.$name;
        if (!class_exists($class) || !is_subclass_of($class, Model::class)) {
            Console::warning("Invalid Model \"$name\"");
            return null;
        }
        return new $class($this->view);
    }
}