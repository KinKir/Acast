<?php

namespace Acast\Socket\Enhanced;

use GatewayWorker\Lib\Gateway;

abstract class Controller extends \Acast\Controller {
    /**
     * 当前连接的客户端ID
     * @var string
     */
    protected $_client_id;
    /**
     * 构造函数，绑定模型、视图
     *
     * @param Router $router
     */
    function __construct(Router $router) {
        parent::__construct($router);
        $this->_client_id = $router->client_id;
    }
    /**
     * 锁定客户端
     *
     * @param callable|null $callback
     */
    static function lock(?callable $callback = null) {
        $_SESSION['lock'] = $callback ?? true;
    }
    /**
     * 解锁客户端
     */
    static function unlock() {
        unset($_SESSION['lock']);
    }
    /**
     * 向客户端发送数据
     *
     * @param $data
     * @param $client_id
     * @return bool
     */
    static function send($data, $client_id = null) {
        return $client_id ? Gateway::sendToClient($client_id, $data) : Gateway::sendToCurrentClient($data);
    }
    /**
     * 向客户端发送数据并关闭连接
     *
     * @param $data
     * @param $client_id
     * @return bool
     */
    static function close($data = null, $client_id = null) {
        return $client_id ? Gateway::closeClient($client_id, $data) : Gateway::closeCurrentClient($data);
    }
}