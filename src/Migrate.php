<?php

namespace Acast;
/**
 * Migrate
 * @package Acast
 */
class Migrate {
    /**
     * 实例列表
     * @var array
     */
    protected static $_instances = [];
    /**
     * PDO对象
     * @var \PDO
     */
    protected $_pdo;
    /**
     * 待执行的SQL语句
     * @var string
     */
    protected $_sql;
    /**
     * 新建一个Migrate实例
     *
     * @param string $name
     * @param array $settings
     * @param string $sql_path
     */
    static function create(string $name, array $settings, string $sql_path) {
        if (isset(self::$_instances[$name]))
            Console::warning("Overwriting migration \"$name\".");
        self::$_instances[$name] = new self($settings, $sql_path);
    }
    /**
     * 获取Migrate实例
     *
     * @param string $name
     * @return Migrate|null
     */
    static function instance(string $name) : ?self {
        if (!isset(self::$_instances[$name])) {
            Console::warning("Migration \"$name\" do not exist.");
            return null;
        }
        return self::$_instances[$name];
    }
    /**
     * 构造函数
     *
     * @param $settings
     * @param $sql_path
     */
    protected function __construct($settings, $sql_path) {
        $host = $settings['host'];
        $port = $settings['port'];
        $user = $settings['user'];
        $password = $settings['password'];
        $charset = $settings['charset'];
        $this->_pdo = new \PDO('mysql:host='.$host.';port='.$port, $user, $password, [
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.$charset
        ]);
        $this->_pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->_sql = file_get_contents($sql_path);
        if ($this->_sql === false) {
            Console::warning("Invalid SQL file.");
            return;
        }
    }
    /**
     * 执行Migrate操作
     *
     * @param array $replace
     */
    function execute(array $replace) {
        foreach ($replace as $key => $value)
            $this->_sql = str_replace('%:='.$key.'=:%', $value, $this->_sql);
        $this->_pdo->exec($this->_sql);
    }
}