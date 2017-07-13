<?php

namespace Acast;
/**
 * 控制器
 * @package Acast
 */
abstract class Controller {
    /**
     * 全局控制器绑定
     * @var array
     */
    public static $globals = [];
    /**
     * 中间件返回信息
     * @var mixed
     */
    public $mRet;
    /**
     * 路由参数
     * @var array
     */
    protected $params = [];
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
     * @param Router $router
     */
    function __construct(Router $router) {
        $temp = explode('\\', get_called_class());
        $name = Server::$name.'\\View\\'.end($temp);
        if (class_exists($name))
            $this->view = new $name($this);
        $name = Server::$name.'\\Model\\'.end($temp);
        if (class_exists($name))
            $this->model = new $name;
        $this->method = $router->method;
        $this->params = $router->params ?? [];
        $this->mRet = $router->mRet ?? null;
    }
    /**
     * 调用外部模型
     *
     * @param string $name
     * @return Model|null
     */
    protected function invoke(string $name) : ?Model {
        $class = Server::$name.'\\Model\\'.$name;
        return new $class;
    }
    /**
     * 添加全局控制器绑定
     *
     * @param array $controllers
     */
    static function addGlobal(array $controllers) {
        if (!is_array($controllers[0]))
            $controllers = [$controllers];
        foreach ($controllers as $controller) {
            [$name, $controller, $method] = $controller;
            if (isset(self::$globals[$name]))
                Console::warning("Overwriting global controller \"$name\".");
            $controller = Server::$name.'\\Controller\\'.$controller;
            if (!class_exists($controller) || !is_subclass_of($controller, Controller::class)) {
                Console::warning("Invalid controller \"$controller\".");
                return;
            }
            if (!method_exists($controller, $method)) {
                Console::warning("Invalid method \"$method\".");
                return;
            }
            self::$globals[$name] = [$controller, $method];
        }
    }
}