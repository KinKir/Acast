<?php

namespace Acast;

abstract class Config {
    /**
     * 存储配置项
     * @var array
     */
    protected static $_data = [];
    /**
     * 设置配置项
     * @param string $key
     * @param $value
     */
    static function set(string $key, $value) {
        self::$_data[$key] = $value;
    }
    /**
     * 批量设置配置项
     * @param array $config
     */
    static function setArray(array $config) {
        foreach ($config as $key => $value)
            self::set($key, $value);
    }
    /**
     * 获取配置项
     * @param string $key
     * @return mixed
     */
    static function get(string $key) {
        return self::$_data[$key] ?? null;
    }
    /**
     * 设置配置项，全局范围有效
     * @param string $key
     * @param $value
     * @return bool
     */
    static function setGlobal(string $key, $value) {
        return Server::$memcached->set('Acast_'.$key, $value);
    }
    /**
     * 批量设置全局配置项
     * @param array $config
     */
    static function setGlobal_array(array $config) {
        foreach ($config as $key => $value)
            self::setGlobal($key, $value);
    }
    /**
     * 获取全局配置项
     * @param string $key
     * @return mixed
     */
    static function getGlobal(string $key) {
        return Server::$memcached->get('Acast_'.$key);
    }
}