<?php

use App\Http\Controllers\RoleController;
use App\Http\Controllers\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/test', function () {
    return response()->json(['message' => 'Hello World!']);
});


//roles routes
Route::get('roles', [RoleController::class, 'index']);
Route::get( 'roles/{id}', [RoleController::class, 'show']);
Route::post('roles', [RoleController::class, 'store']);
Route::delete('roles/{id}', [RoleController::class, 'destroy']);
Route::put('roles/{role}', [RoleController::class, 'update']);

//categories routes
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);
Route::post('categories', [CategoryController::class, 'store']);
Route::put('categories/{category}', [CategoryController::class, 'update']);
Route::delete('categories/{category}', [CategoryController::class, 'destroy']);