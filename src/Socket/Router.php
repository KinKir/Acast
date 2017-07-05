<?php

namespace Acast\Socket;

class Router extends \Acast\Router {
    /**
     * 请求内容
     * @var mixed
     */
    public $requestData;
    const DEFAULT_METHOD = '.';
}