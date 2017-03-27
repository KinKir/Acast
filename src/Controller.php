<?php

namespace Acast;
/**
 * 控制器
 * @package Acast
 */
abstract class Controller {
    /**
     * 服务名
     * @var string
     */
    protected static $_app = null;
    /**
     * 中间件返回信息
     * @var mixed
     */
    public $filterMsg = null;
    /**
     * 返回数据
     * @var mixed
     */
    public $retMsg = null;
    /**
     * GET参数
     * @var array
     */
    protected $urlParams = [];
    /**
     * 绑定的模型
     * @var Model
     */
    protected $model = null;
    /**
     * 绑定的视图
     * @var View
     */
    protected $view = null;
    /**
     * 构造函数，绑定模型、视图
     *
     * @param Router $route
     */
    function __construct(Router $route) {
        $temp = explode('\\', get_called_class());
        $name = self::$_app.'\\Model\\'.end($temp);
        if (class_exists($name))
            $this->model = new $name;
        $name = self::$_app.'\\View\\'.end($temp);
        if (class_exists($name))
            $this->view = new $name($this);
        $this->urlParams = $route->urlParams ?? [];
        $this->retMsg = $route->retMsg ?? null;
        $this->filterMsg = $route->filterMsg ?? null;
    }
    /**
     * 初始化
     *
     * @param string $app
     */
    static function init(string $app) {
        self::$_app = $app;
    }
}