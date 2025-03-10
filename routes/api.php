<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FoodController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderDetailController;
use App\Http\Controllers\DriverTrackingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use Google\Client as GoogleClient;


Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/test', function () {
    return response()->json(['message' => 'Hello World!']);
});

//Register and login
Route::post('/register',[AuthController::class, 'register']);
Route::post('/login',[AuthController::class, 'login']);
Route::post('/logout',[AuthController::class,'logout']);

//user routes
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{user}', [UserController::class, 'show']);

//roles routes
Route::get('/roles', [RoleController::class, 'index']);
Route::get( '/roles/{role}', [RoleController::class, 'show']);
Route::post('/roles', [RoleController::class, 'store']);
Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
Route::put('/roles/{role}', [RoleController::class, 'update']);

//categories routes
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::post('/categories', [CategoryController::class, 'store']);
Route::put('/categories/{category}', [CategoryController::class, 'update']);
Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

//addresses routes
Route::get('/addresses', [AddressController::class, 'index']);
Route::get('/addresses/{address}', [AddressController::class, 'show']);
Route::post('/addresses', [AddressController::class, 'store']);
Route::put('/addresses/{address}', [AddressController::class, 'update']);
Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);
Route::get('addresses/{id}', [AddressController::class, 'getAddressByUserId']);

//orders routes
Route::get('/orders', [OrderController::class, 'index']);
Route::get('/rders/{order}', [OrderController::class, 'show']);
Route::post('/orders', [OrderController::class, 'placeOrder']);
// Route::put('/orders/{order}', [OrderController::class, 'update']);
Route::put('/orders/update-status/{order}', [OrderController::class, 'updateOrderStatus']);
Route::delete('/orders/{id}', [OrderController::class, 'destroy']);
Route::get('/orderByUserId/{id}', [OrderController::class, 'getOrderByUserId']);
Route::get('/orderByDriverId/{id}', [OrderController::class, 'getOrderByDriverId']);
Route::get('/orderByStatus/{status}', [OrderController::class, 'getOrderByStatus']);
Route::get('/orderByAddressId/{id}', [OrderController::class, 'getOrderByAddressId']); 
Route::get('/orderTotalAmount', [OrderController::class, 'getTotalAmount']);
Route::get('/orderByPaymentMethod/{payment_method}', [OrderController::class, 'getOrderByPaymentMethod']);

//order details routes
Route::get('/orderDetails', [OrderDetailController::class, 'index']);
Route::get('/orderDetails/{orderDetail}', [OrderDetailController::class, 'show']);
// Route::post('orderDetails', [OrderDetailController::class, 'store']);
Route::put('/orderDetails/{orderDetail}', [OrderDetailController::class, 'update']);
Route::delete('/orderDetails/{id}', [OrderDetailController::class, 'destroy']);
Route::get('/orderDetailsByOrderId/{id}', [OrderDetailController::class, 'getOrderDetailsByOrderId']);

//driver routes
Route::get('/drivers', [DriverTrackingController::class, 'index']);
Route::get('/drivers/{driver}', [DriverTrackingController::class, 'show']);
Route::post('/drivers', [DriverTrackingController::class, 'store']);
Route::put('/drivers/{driver}', [DriverTrackingController::class, 'update']);
Route::delete('/drivers/{id}', [DriverTrackingController::class, 'destroy']);

//Food routes
Route::prefix('foods')->middleware(['auth:api','is_admin'])->group(function () {
    Route::post('/create', [FoodController::class, 'createFood']);
    Route::post('/update/{id}', [FoodController::class, 'updateFood']);
    Route::delete('/delete/{id}', [FoodController::class, 'deleteFood']);
    Route::get('/search/{id}', [FoodController::class, 'searchFood']);
    Route::get('/getAllFoods', [FoodController::class, 'getAllFoods']);
});

//test notification 
Route::post('/receivefcmtoken', [NotificationController::class, 'receiveFcmToken']);


Route::get('/testnotification', function () {

    $fcm = "fcmToken";
    $title = "Test Notification";
    $body = "This is a test notification";

    $credentialFilePath = public_path("json/pizzanotification-2bcd3-firebase-adminsdk-fbsvc-4461cd06e8.json");

    $client = new GoogleClient();

    $client->setAuthConfig($credentialFilePath);
    $client->setScopes(['https://www.googleapis.com/auth/firebase.messaging']);
    $client->refreshTokenWithAssertion();
    $token = $client->getAccessToken();
   
    $accessToken = $token['access_token'];

    $header = [
        'Authorization' => 'Bearer ' . $accessToken,
        'Content-Type' => 'application/json; UTF-8',
    ];

    $data = [
        'message' => [
            'token' => $fcm,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
        ],
    ];

    $payload = json_encode($data);

    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/pizzanotification-2bcd3/messages:send');
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        return response()->json([
            'message' => 'Curl Error: ' . $err
        ], 500);
    } else {
        return response()->json([
            'message' => 'Notification has been sent',
            'response' => json_decode($response, true)
        ]);
    }

})->name('testfcmnotification');


Route::post('/google-login', [AuthController::class, 'googleLogin']);
