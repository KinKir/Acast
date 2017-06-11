<?php

namespace Acast;
/**
 * Workerman根目录
 */
const WORKERMAN_ROOT = __DIR__ . '/Workerman';
/**
 * 默认Worker配置
 */
const DEFAULT_WORKER_CONFIG = [
    'count' => 4,
    'name' => 'AcastHttpWorker',
];
/**
 * 是否为调试模式
 */
const DEBUG_MODE = false;