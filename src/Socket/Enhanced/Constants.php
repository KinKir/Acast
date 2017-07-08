<?php

namespace Acast\Socket\Enhanced;
/**
 * 默认Worker配置
 */
const DEFAULT_GATEWAY_WORKER_CONFIG = [
    'count' => 4,
    'name' => 'AcastGatewayWorker',
];
const DEFAULT_BUSINESS_WORKER_CONFIG = [
    'count' => 4,
    'name' => 'AcastBusinessWorker',
];