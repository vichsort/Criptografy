<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| A aplicação é uma SPA renderizada via Inertia. Toda a interação real
| (criptografar, descriptografar, hash, encode/decode) acontece através
| dos endpoints definidos em routes/api.php.
|
*/

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');
