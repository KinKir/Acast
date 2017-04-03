<?php

namespace Acast;
use Workerman\Protocols\Http;
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
     * 绑定的计数器
     * @var Controller
     */
    protected $_controller = null;
    /**
     * 临时返回数据
     * @var mixed
     */
    protected $_temp = null;
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
            if (!Server::$memcache->set('mem_'.$name, $data))
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
            $this->_temp = Server::$memcache->get('mem_'.$name);
        else
            $this->_temp = self::$_templates[$name];
        return $this;
    }
    /**
     * 置HTTP状态码
     *
     * @param int $code
     * @param string|null $msg
     * @return string|null
     */
    static function http(int $code, ?string $msg = null) {
        Http::header('HTTP', true, $code);
        return $msg;
    }
    /**
     * 格式化为JSON
     *
     * @param array $data
     * @param int $err
     * @return string
     */
    static function json(array $data, int $err = 0) {
        Http::header('Content-Type: application/json');
        return json_encode(['err' => $err] + $data);
    }
    /**
     * 将视图回传给控制器
     */
    function show() {
        if (!isset($this->_temp))
            $this->_controller->retMsg = View::http(500, 'Server failed to give any response.');
        else
            $this->_controller->retMsg = $this->_temp;
    }
}