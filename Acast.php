<?php

require_once __DIR__ . '/Config.php';
require_once Acast\WORKERMAN_ROOT . '/Autoloader.php';
foreach (glob(__DIR__ . '/lib/*.php') as $require_file) {
    require_once $require_file;
}
foreach (glob(__DIR__ . '/src/*.php') as $require_file) {
    require_once $require_file;
}