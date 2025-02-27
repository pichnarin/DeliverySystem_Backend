<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FoodController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderDetailController;
use App\Http\Controllers\DriverTrackingController;



Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/test', function () {
    return response()->json(['message' => 'Hello World!']);
});


//roles routes
Route::get('roles', [RoleController::class, 'index']);
Route::get( 'roles/{role}', [RoleController::class, 'show']);
Route::post('roles', [RoleController::class, 'store']);
Route::delete('roles/{id}', [RoleController::class, 'destroy']);
Route::put('roles/{role}', [RoleController::class, 'update']);

//categories routes
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);
Route::post('categories', [CategoryController::class, 'store']);
Route::put('categories/{category}', [CategoryController::class, 'update']);
Route::delete('categories/{category}', [CategoryController::class, 'destroy']);

//addresses routes
Route::get('addresses', [AddressController::class, 'index']);
Route::get('addresses/{address}', [AddressController::class, 'show']);
Route::post('addresses', [AddressController::class, 'store']);
Route::put('addresses/{address}', [AddressController::class, 'update']);
Route::delete('addresses/{id}', [AddressController::class, 'destroy']);
Route::get('addresses/{id}', [AddressController::class, 'getAddressByUserId']);

//orders routes
Route::get('orders', [OrderController::class, 'index']);
Route::get('orders/{order}', [OrderController::class, 'show']);
Route::post('orders', [OrderController::class, 'store']);
Route::put('orders/{order}', [OrderController::class, 'update']);
Route::delete('orders/{id}', [OrderController::class, 'destroy']);
Route::get('orderByUserId/{id}', [OrderController::class, 'getOrderByUserId']);
Route::get('orderByDriverId/{id}', [OrderController::class, 'getOrderByDriverId']);
Route::get('orderByStatus/{status}', [OrderController::class, 'getOrderByStatus']);
Route::get('orderByAddressId/{id}', [OrderController::class, 'getOrderByAddressId']); 
Route::get('orderTotalAmount', [OrderController::class, 'getTotalAmount']);
Route::get('orderByPaymentMethod/{payment_method}', [OrderController::class, 'getOrderByPaymentMethod']);

//order details routes
Route::get('orderDetails', [OrderDetailController::class, 'index']);
Route::get('orderDetails/{orderDetail}', [OrderDetailController::class, 'show']);
Route::post('orderDetails', [OrderDetailController::class, 'store']);
Route::put('orderDetails/{orderDetail}', [OrderDetailController::class, 'update']);
Route::delete('orderDetails/{id}', [OrderDetailController::class, 'destroy']);
Route::get('orderDetailsByOrderId/{id}', [OrderDetailController::class, 'getOrderDetailsByOrderId']);

//driver routes
Route::get('drivers', [DriverTrackingController::class, 'index']);
Route::get('drivers/{driver}', [DriverTrackingController::class, 'show']);
Route::post('drivers', [DriverTrackingController::class, 'store']);
Route::put('drivers/{driver}', [DriverTrackingController::class, 'update']);
Route::delete('drivers/{id}', [DriverTrackingController::class, 'destroy']);

//Food routes
Route::prefix('foods')->group(function () {
   
    Route::post('/create', [FoodController::class, 'createFood']);
    Route::post('/update/{id}', [FoodController::class, 'updateFood']);
    Route::delete('/delete/{id}', [FoodController::class, 'deleteFood']);
    Route::get('/search/{id}', [FoodController::class, 'searchFood']);
    Route::get('/getAllFoods', [FoodController::class, 'getAllFoods']);

});