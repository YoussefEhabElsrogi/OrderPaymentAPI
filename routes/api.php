<?php

use App\Http\Controllers\
{
    AuthController,
    OrderController,
    PaymentController
};
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

################################################## AUTH ROUTES & PUBLIC ROUTES ##################################################
Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
});

################################################## PROTECTED ROUTES ##################################################
Route::middleware('auth:api')->group(function () {
    ################################################## AUTH ROUTES ##################################################
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    ################################################## ORDER ROUTES ##################################################
    Route::apiResource('orders', OrderController::class);
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);

    ################################################## PAYMENT ROUTES ##################################################
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index']);
        Route::get('methods', [PaymentController::class, 'getAvailablePaymentMethods']);
        Route::get('{payment}', [PaymentController::class, 'show']);
        Route::patch('{payment}/status', [PaymentController::class, 'updateStatus']);
    });

    ################################################## ORDER PAYMENT ROUTES ##################################################
    Route::prefix('orders/{order}')->group(function () {
        Route::post('payments', [PaymentController::class, 'processPayment']);
        Route::get('payments', [PaymentController::class, 'getOrderPayments']);
    });
    Route::get('get-available-payment-methods', [PaymentController::class, 'getAvailablePaymentMethods']);
});
