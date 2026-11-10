<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'Criptografy API',
        'status' => 'healthy',
        'version' => '1.0.0',
    ]);
});
