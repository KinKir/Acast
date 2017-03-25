<?php

namespace Acast;
use Workerman\MySQL\Connection;
/**
 * 模型
 * @package Acast
 */
abstract class Model {
    /**
     * 应用名称
     * @var string
     */
    protected static $_app = null;
    /**
     * 绑定的数据表
     * @var string|null
     */
    protected $_table = null;
    /**
     * 数据库连接实例
     * @var Connection
     */
    protected static $_connection = null;
    /**
     * 初始化
     *
     * @param string $app
     */
    static function init(string $app) {
        self::$_app = $app;
    }
    /**
     * MySQL SELECT
     *
     * @param $cols
     * @param array $where
     * @param array|null $bind
     * @param array|null $order_by
     * @param array|null $limit
     * @return mixed
     */
    function select($cols, array $where, ?array $bind = null, ?array $order_by = null, ?array $limit = null) {
        $query = self::Db()->select($cols)->from($this->_table)->where($where);
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
     * @param array $where
     * @param array|null $bind
     * @param int|null $limit
     * @return mixed
     */
    function update($cols, array $where, ?array $bind = null, ?int $limit = null) {
        $query = self::Db()->update($this->_table)->cols($cols)->where($where);
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
            [$host, $port, $user, $password, $db_name] = DbConfig::get(self::$_app);
            self::$_connection = new Connection($host, $port, $user, $password, $db_name);
        }
        return self::$_connection;
    }
}