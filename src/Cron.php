<?php

namespace Acast;

use Workerman\Lib\Timer;
/**
 * 计划任务
 * @package Acast
 */
class Cron {
    /**
     * 计划任务服务实例列表
     * @var array
     */
    protected static $_instances = [];
    /**
     * 计划任务列表
     * @var array
     */
    protected $_tasks = [];
    /**
     * 定时器
     * @var int
     */
    protected $_timer_id;
    /**
     * 服务是否已启用
     * @var bool
     */
    protected $_enabled = true;
    /**
     * 创建一个计划任务服务
     *
     * @param string $name
     * @param int $interval
     */
    static function create(string $name, int $interval) {
        if (isset(self::$_instances[$name]))
            Console::warning("Overwriting cron service \"$name\".");
        self::$_instances[$name] = new self($interval);
    }
    /**
     * 构造函数
     *
     * @param int $interval
     */
    protected function __construct(int $interval) {
        $this->_timer_id = Timer::add($interval, [__CLASS__, 'timerCallback'], [], true);
    }
    /**
     * 析构函数
     */
    function __destruct(){
        Timer::del($this->_timer_id);
    }
    /**
     * 获取计划任务服务实例
     *
     * @param string $name
     * @return Cron|null
     */
    static function instance(string $name) : ?self {
        if (!isset(self::$_instances[$name])) {
            Console::warning("Cron service \"$name\" do not exist.");
            return null;
        }
        return self::$_instances[$name];
    }
    /**
     * 删除计划任务服务
     * @param string $name
     */
    static function destroy(string $name) {
        if (!isset(self::$_instances[$name])) {
            Console::warning("Cron service \"$name\" do not exist.");
            return;
        }
        unset(self::$_instances[$name]);
    }
    /**
     * 启用计划任务服务
     */
    function enable() {
        $this->_enabled = true;
    }
    /**
     * 停用计划任务服务
     */
    function disable() {
        $this->_enabled = false;
    }
    /**
     * 新建计划任务
     *
     * @param string $name
     * @param int $when
     * @param callable $callback
     * @param null $param
     * @param bool $persistent
     */
    function add(string $name, int $when, callable $callback, $param = null, bool $persistent) {
        if (isset($this->_tasks[$name]))
            Console::warning("Overwriting cron task \"$name\".");
        $this->_tasks[$name] = [
            0 => $when,
            1 => $callback,
            2 => $param,
            3 => $persistent
        ];
    }
    /**
     * 删除计划任务
     *
     * @param string $name
     */
    function del(string $name) {
        if (!isset($this->_tasks[$name])) {
            Console::warning("Cron task \"$name\" do not exist.");
            return;
        }
        unset($this->_tasks[$name]);
    }
    /**
     * 定时器回调
     */
    function timerCallback() {
        if (!$this->_enabled)
            return;
        $now = time();
        foreach ($this->_tasks as $name => &$task) {
            if ($task[0] > $now)
                continue;
            call_user_func_array($task[1], [&$task[0], &$task[3], &$task[2]]);
            if (!$task[3])
                unset($this->_tasks[$name]);
        }
    }
}