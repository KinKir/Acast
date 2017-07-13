<?php

namespace Acast\Http;

abstract class Controller extends \Acast\Controller {
    /**
     * 返回数据
     * @var mixed
     */
    public $retMsg;
    /**
     * {@inheritdoc}
     */
    function __construct(Router $router) {
        parent::__construct($router);
        $this->retMsg = $router->retMsg ?? null;
    }
}