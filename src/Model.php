<?php

namespace Acast;
use Workerman\Lib\Connection;
/**
 * 模型
 * @package Acast
 */
abstract class Model {
    /**
     * 数据库配置项
     * @var array
     */
    protected static $_config;
    /**
     * 绑定的数据表
     * @var string
     */
    protected $_table;
    /**
     * 数据库连接实例
     * @var Connection
     */
    protected static $_connection;
    /**
     * 控制器绑定的视图实例
     * @var View|null
     */
    protected $_view;
    /**
     * 构造函数
     * @param View|null $view
     */
    function __construct(?View $view = null) {
        if ($view)
            $this->_view = $view;
    }
    /**
     * 配置数据库连接
     *
     * @param array $config
     */
    static function config(array $config) {
        if (isset(self::$_config))
            Console::warning('Overwriting database configuration for app \"'.Server::$name.'\".');
        self::$_config = array_values($config);
    }
    /**
     * MySQL SELECT
     *
     * @param $cols
     * @param mixed $where
     * @param array|null $bind
     * @param array|null $order_by
     * @param array|null $limit
     * @return mixed
     */
    function select($cols, $where = null, ?array $bind = null, ?array $order_by = null, ?array $limit = null) {
        $query = self::Db()->select($cols)->from($this->_table);
        if (isset($where))
            $query->where($where);
        if (isset($order_by))
            $query->orderByASC($order_by[1], $order_by[0]);
        if (isset($bind))
            $query->bindValues($bind);
        if (isset($limit))
            $query->offset($limit[0])->limit($limit[1]);
        return $query->query();
    }
    /**
     * MySQL INSERT
     *
     * @param array $cols
     * @param array|null $bind
     * @return mixed
     */
    function insert(array $cols, ?array $bind = null) {
        $query = self::Db()->insert($this->_table)->cols($cols);
        if (isset($bind))
            $query->bindValues($bind);
        return $query->query();
    }
    /**
     * MySQL UPDATE
     *
     * @param $cols
     * @param mixed $where
     * @param array|null $bind
     * @param int|null $limit
     * @return mixed
     */
    function update($cols, $where = null, ?array $bind = null, ?int $limit = null) {
        $query = self::Db()->update($this->_table)->cols($cols);
        if (isset($where))
            $query->where($where);
        if (isset($bind))
            $query->bindValues($bind);
        if (isset($limit))
            $query->limit($limit);
        return $query->query();
    }
    /**
     * 绑定表
     *
     * @param string $table
     */
    function table(string $table) {
        $this->_table = $table;
    }
    /**
     * 获取当前实例的数据库连接
     *
     * @return Connection
     */
    static function Db() : Connection {
        if (!isset(self::$_connection)) {
            [$host, $port, $user, $password, $db_name, $charset] = self::$_config;
            self::$_connection = new Connection($host, $port, $user, $password, $db_name, $charset);
        }
        return self::$_connection;
    }
}