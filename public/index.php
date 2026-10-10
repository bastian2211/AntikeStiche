<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Maintenance Mode
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Composer Autoloader
require __DIR__.'/../vendor/autoload.php';

// Laravel Bootstrap
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
