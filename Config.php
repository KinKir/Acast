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
    'reusePort' => true
];
/**
 * 是否在视图中使用共享内存
 */
const USE_SHM = false;
/**
 * 最大共享内存使用量
 */
const SHM_SIZE = 10000000;