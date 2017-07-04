<?php

namespace Acast;

if (ENABLE_HTTP) {
    foreach (glob(__DIR__.'/Http/*.php') as $require_file)
        require_once $require_file;
}