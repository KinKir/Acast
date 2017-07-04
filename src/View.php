<?php

namespace Acast;
/**
 * 视图
 * @package Acast
 */
abstract class View {
    /**
     * 模版列表
     * @var array
     */
    protected static $_templates = [];
    /**
     * 绑定的控制器
     * @var Controller
     */
    protected $_controller;
    /**
     * 临时返回数据
     * @var mixed
     */
    protected $_temp;
    /**
     * 构造函数
     *
     * @param Controller $controller
     */
    function __construct(Controller $controller) {
        $this->_controller = $controller;
    }
    /**
     * 注册视图
     *
     * @param string $name
     * @param $data
     * @param bool $use_memcache
     */
    static function register(string $name, $data, bool $use_memcache = false) {
        if (isset(self::$_templates[$name])) {
            Console::warning("Register view \"$name\" failed. Already exists.");
            return;
        }
        if ($use_memcache) {
            self::$_templates[$name] = false;
            if (!Server::$memcached->set('mem_'.$name, $data))
                Console::warning("Failed to set memcached for view \"$name\".");
        } else
            self::$_templates[$name] = $data;
    }
    /**
     * 获取视图
     *
     * @param string $name
     * @return self
     */
    function fetch(string $name) : self {
        if (!isset(self::$_templates[$name])) {
            Console::warning("View \"$name\" not exist.");
            return $this;
        }
        if (self::$_templates[$name] === false)
            $this->_temp = Server::$memcached->get('mem_'.$name);
        else
            $this->_temp = self::$_templates[$name];
        return $this;
    }
    /**
     * 将视图回传给控制器
     */
    function show() {
        $this->_controller->retMsg = $this->_temp;
    }
}