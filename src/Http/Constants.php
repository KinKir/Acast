<?php

namespace Acast\Http;
/**
 * 默认Worker配置
 */
const DEFAULT_WORKER_CONFIG = [
    'count' => 4,
    'name' => 'AcastHttpWorker'
];
/**
 * 是否启用$_SESSION
 */
const ENABLE_SESSION = false;