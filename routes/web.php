<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ViaGoogleController;


Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

//google auth routes
Route::get('/auth/google/redirect', [ViaGoogleController::class, 'redirect']);

Route::get('/auth/google/callback', [ViaGoogleController::class, 'callback']);

require __DIR__ . '/auth.php';

