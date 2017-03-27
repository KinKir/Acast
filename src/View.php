<?php

namespace Acast;
/**
 * 视图
 * @package Acast
 */
abstract class View {
    /**
     * 共享内存句柄
     * @var resource
     */
    protected static $_shm = null;
    /**
     * 模版列表
     * @var array
     */
    protected static $_templates = [];
    /**
     * 共享内存计数器
     * @var int
     */
    protected static $_shm_id_count = 0;
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
     * 初始化
     */
    static function init() {
        if (USE_SHM)
            self::$_shm = shm_attach(ftok(__FILE__, 'v'), SHM_SIZE);
    }
    /**
     * 销毁共享内存
     */
    static function destroy() {
        if (is_resource(self::$_shm)) {
            shm_remove(self::$_shm);
            shm_detach(self::$_shm);
        }
    }
    /**
     * 注册视图
     *
     * @param string $name
     * @param $data
     * @param bool $use_shm
     */
    static function register(string $name, $data, bool $use_shm = false) {
        if (isset(self::$_templates[$name])) {
            Console::Warning("Register view \"$name\" failed. Already exists.");
            return;
        }
        if (USE_SHM && $use_shm) {
            ++self::$_shm_id_count;
            shm_put_var(self::$_shm, self::$_shm_id_count, $data);
            self::$_templates[$name] = self::$_shm_id_count;
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
            Console::Warning("View \"$name\" not exist.");
            return $this;
        }
        if (USE_SHM && is_integer(self::$_templates[$name]))
            $this->_temp = shm_get_var(self::$_shm, self::$_templates[$name]);
        else
            $this->_temp = self::$_templates[$name];
        return $this;
    }
    /**
     * 生成HTTP错误信息
     *
     * @param int $code
     * @param string $msg
     * @return self
     */
    function err(int $code, string $msg) : self {
        $this->_temp = Respond::Err($code, $msg);
        return $this;
    }
    /**
     * 生成JSON
     *
     * @param array $data
     * @param int $err
     * @return self
     */
    function json(array $data, int $err = 0) : self {
        $this->_temp = Respond::Json($data, $err);
        return $this;
    }
    /**
     * 将视图回传给控制器
     */
    function show() {
        if (!isset($this->_temp))
            $this->_controller->retMsg = Respond::Err(500, 'Server failed to give any response.');
        else
            $this->_controller->retMsg = $this->_temp;
    }
}