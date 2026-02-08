<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymobController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public book routes (for customers)
Route::get('/books', [BookController::class, 'index']);
Route::get('/books/{id}', [BookController::class, 'show']);

// Protected order routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
});

// Public payment routes
Route::get('/payments/publishable-key', [PaymentController::class, 'getPublishableKey']);
Route::post('/webhooks/stripe', [PaymentController::class, 'handleStripeWebhook']);

// Public Paymob routes
Route::post('/paymob/callback', [PaymobController::class, 'handleCallback']);
Route::get('/paymob/config', [PaymobController::class, 'getConfig']);
Route::get('/paymob/webhook-test', [PaymobController::class, 'webhookTest']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // User profile routes
    Route::put('/user/update', [AuthController::class, 'updateProfile']);
    Route::put('/user/change-password', [AuthController::class, 'changePassword']);
    Route::post('/user/upload-avatar', [AuthController::class, 'uploadAvatar']);
    Route::delete('/user/remove-avatar', [AuthController::class, 'removeAvatar']);
    
    // Admin book routes (CRUD)
    Route::post('/books', [BookController::class, 'store']);
    Route::put('/books/{id}', [BookController::class, 'update']);
    Route::patch('/books/{id}', [BookController::class, 'update']);
    Route::delete('/books/{id}', [BookController::class, 'destroy']);
    Route::get('/books/statistics', [BookController::class, 'statistics']);
    
    // User favorite routes
    Route::get('/favorites', [FavoriteController::class, 'getUserFavorites']);
    Route::post('/favorites', [FavoriteController::class, 'addToFavorites']);
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggleFavorite']);
    Route::get('/favorites/check/{bookId}', [FavoriteController::class, 'isFavorited']);
    Route::delete('/favorites/{bookId}', [FavoriteController::class, 'removeFromFavorites']);
    
    // Protected payment routes (Stripe)
    Route::post('/payments/create-intent', [PaymentController::class, 'createPaymentIntent']);
    Route::post('/payments/confirm', [PaymentController::class, 'confirmPayment']);
    Route::get('/payments/status/{paymentIntentId}', [PaymentController::class, 'getPaymentStatus']);
    
    // Protected Paymob routes
    Route::post('/paymob/initiate', [PaymobController::class, 'initiatePayment']);
    Route::get('/paymob/verify/{orderId}', [PaymobController::class, 'verifyPayment']);
    
    // Admin order routes
    Route::put('/orders/{order}', [OrderController::class, 'update']);
});
