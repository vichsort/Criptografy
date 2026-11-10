<?php

use App\Http\Controllers\AlgorithmController;
use App\Http\Controllers\DecodeController;
use App\Http\Controllers\DecryptController;
use App\Http\Controllers\EncodeController;
use App\Http\Controllers\EncryptController;
use App\Http\Controllers\HashController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Every cryptographic operation has its own endpoint; GET /algorithms
| lists what is available, with the JSON Schema of each one's options.
|
*/

Route::get('/algorithms', [AlgorithmController::class, 'index']);
Route::post('/encrypt', [EncryptController::class, 'store']);
Route::post('/decrypt', [DecryptController::class, 'store']);
Route::post('/hash', [HashController::class, 'store']);
Route::post('/encode', [EncodeController::class, 'store']);
Route::post('/decode', [DecodeController::class, 'store']);
