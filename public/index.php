<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Modo de manutenção, se ativo...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Autoloader do Composer...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap da aplicação e tratamento da requisição...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
