<?php

namespace Acast;
use Workerman\Lib\Connection;
/**
 * 模型
 * @package Acast
 */
abstract class Model {
    const ALL = 'query';
    const ROW = 'row';
    const SINGLE = 'single';
    const COLUMN = 'column';
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
     * @param string $return
     * @return mixed
     */
     protected function _select($cols, $where = null,
                                ?array $bind = null,
                                ?array $order_by = null,
                                ?array $limit = null, string $return = self::ALL) {
        $query = self::Db()->select($cols)->from($this->_table);
        if (isset($where))
            $query->where($where);
        if (isset($order_by))
            $query->orderByASC($order_by[1], $order_by[0]);
        if (isset($bind))
            $query->bindValues($bind);
        if (isset($limit))
            $query->offset($limit[0])->limit($limit[1]);
        return $query->$return();
    }
    /**
     * MySQL INSERT
     *
     * @param array $cols
     * @param array|null $bind
     * @param bool $ignore
     * @return mixed
     */
    protected function _insert(array $cols, ?array $bind = null, bool $ignore = false) {
        $query = self::Db()->insert($this->_table)->ignore($ignore)->cols($cols);
        if (isset($bind))
            $query->bindValues($bind);
        return $query->query();
    }
    /**
     * MySQL UPDATE
     *
     * @param array $cols
     * @param mixed $where
     * @param array|null $bind
     * @param int|null $limit
     * @return mixed
     */
    protected function _update(array $cols, $where = null, ?array $bind = null, ?int $limit = null) {
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
     * MySQL DELETE
     *
     * @param mixed $where
     * @param array|null $bind
     * @param int|null $limit
     * @return mixed
     */
    protected function _delete($where = null, ?array $bind = null, ?int $limit = null) {
        $query = self::Db()->delete($this->_table);
        if (isset($where))
            $query->where($where);
        if (isset($bind))
            $query->bindValues($bind);
        if (isset($limit))
            $query->limit($limit);
        return $query->query();
    }
    /**
     * 构造WHERE IN语句
     *
     * @param $col
     * @param array $arr
     * @return string
     */
    protected static function _in($col, array $arr) {
        $ret = $col.' IN (';
        if (empty($arr))
            return $ret . 'null)';
        foreach ($arr as $val)
            $ret .= $val.', ';
        return substr($ret, 0, strrpos($ret, ',')) . ')';
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