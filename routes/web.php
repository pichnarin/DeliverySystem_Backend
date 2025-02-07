<?php

use App\Http\Controllers\PersonController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/getAllUsers', [PersonController::class, 'getAllUsers']);
Route::get('/api/getUserById/{id}', [PersonController::class, 'getUserById']);
