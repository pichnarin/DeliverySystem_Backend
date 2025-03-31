<?php

use App\Http\Middleware\AdminMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FoodController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\DriverMiddleware;
use App\Http\Middleware\CustomerMiddleware;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/test', function () {
    return response()->json(['message' => 'Hello World!']);
});


//Register and login (admin - customer - driver) - admin's API
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

//Google login for customer
Route::post('/google-login', [AuthController::class, 'googleLogin']);

//user routes
Route::get('/users/fetch-all-driver', [UserController::class, 'getAllDrivers']);
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{user}', [UserController::class, 'show']);
Route::get('/users/get-users-by-role-name/{role}', [UserController::class, 'getUserByRole']);




Route::middleware([CustomerMiddleware::class])->group(function () {
    Route::prefix('orders')->group(function () {
        Route::post('/place-orders', [OrderController::class, 'placeOrder']);
        Route::get('/fetch-my-orders', [OrderController::class, 'fetchCustomerOrders']);
        Route::get('/fetch-current-order-details', [OrderController::class, 'fetchCurrentCustomerOrder']);
        Route::get('/fetch-order-history', [OrderController::class, 'fetchOrderHistory']);
        // Route::get('/fetch-delivering-order-details', [OrderController::class, 'fetchDriveingOrderDetails']);
    });

    Route::prefix('addresses')->group(function () {
        Route::get('/', [AddressController::class, 'index']);
        Route::get('/fetch-addresses', [AddressController::class, 'fetchCustomerAddresses']);
        Route::post('/create-address', [AddressController::class, 'createAddress']);
        Route::put('/update-address/{address}', [AddressController::class, 'update']);
        Route::delete('/delete-address/{id}', [AddressController::class, 'destroy']);
        Route::get('/get-address-by-user-id/{id}', [AddressController::class, 'getAddressByUserId']);
    });

    Route::prefix('foods')->group(function () {
        Route::get('/', [FoodController::class, 'index']);
        Route::get('/find-food/{id}', [FoodController::class, 'searchFood']);
        Route::get('/fetch-by-category/{category}', [FoodController::class, 'fetchFoodsByCategory']);
    });
});

Route::middleware([DriverMiddleware::class])->group(function () {
    Route::prefix('orders')->group(function () {
        Route::get('/driver-order-details', [OrderController::class, 'fetchDriveingOrderDetails']);
        Route::get('/fetch-assigning-orders', [OrderController::class, 'fetchAssignedOrders']);
        Route::get('/fetch-delivering-order-details/{orderId}', [OrderController::class, 'fetchOrderDetails']);
        Route::put('/accept-delivering/{orderId}', [OrderController::class, 'DeliveringOrder']);
        Route::put('/complete/{orderId}', [OrderController::class, 'CompletedOrder']);
        // Route::get('/fetch-delivery-history', [OrderController::class, 'fetchDeliveringHistory']);
    });
});

Route::middleware([AdminMiddleware::class])->group(function () {
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::patch('/accept-or-declined/{orderId}', [OrderController::class, 'updateOrderStatus']);
        Route::put('/assign-a-driver/{orderId}', [OrderController::class, 'assignDriver']);
        Route::get('/fetch-order-details', [OrderController::class, 'fetchOrderDetails']);
        Route::get('/fetch-pending-orders', [OrderController::class, 'fetchPendingOrders']);
        Route::get('/fetch-accepted-orders', [OrderController::class, 'fetchAcceptedOrders']);
        Route::get('/fetch-delivering-orders', [OrderController::class, 'fetchDeliveringOrders']);
        Route::get('/fetch-completed-orders', [OrderController::class, 'fetchCompletedOrders']);
        Route::get('/fetch-assigned-order-details', [OrderController::class, 'fetchDriveingOrderDetails']);
        Route::get('/fetch-order-detail-by-id/{orderId}', [OrderController::class, 'fetchOrderDetailById']);
    });

    Route::prefix('foods')->group(function () {
        Route::get('/fetchAllFoods', [FoodController::class, 'index']);  
        Route::post('/create', [FoodController::class, 'createFood']);
        Route::post('/update/{id}', [FoodController::class, 'updateFood']);
        Route::delete('/delete/{id}', [FoodController::class, 'deleteFood']);
        Route::get('/search/{id}', [FoodController::class, 'searchFood']);
        Route::get('/fetch-by-cate/{category}', [FoodController::class, 'fetchFoodsByCategory']);
    });
});


//roles routes
Route::prefix('roles')->group(function () {
    Route::get('/', [RoleController::class, 'index']);
    Route::get('/{role}', [RoleController::class, 'show']);
    Route::post('', [RoleController::class, 'store']);
    Route::delete('/{id}', [RoleController::class, 'destroy']);
    Route::put('/{role}', [RoleController::class, 'update']);
});

//categories routes
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/{category}', [CategoryController::class, 'show']);
    Route::post('/', [CategoryController::class, 'store']);
    Route::put('/{category}', [CategoryController::class, 'update']);
    Route::delete('/{category}', [CategoryController::class, 'destroy']);
    Route::get('/get-cat-by-name/{$name}', [CategoryController::class, 'getCategoryByName']);
});






