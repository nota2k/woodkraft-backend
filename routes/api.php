<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Admin\ImageController as AdminImageController;
use App\Http\Controllers\Api\Admin\PromoCodeController as AdminPromoCodeController;
use App\Http\Controllers\Api\Admin\ShippingMethodController as AdminShippingMethodController;
use App\Http\Controllers\Api\Admin\StatsController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\CheckoutController;

Route::prefix('v1')->group(function () {
    // Routes pour les produits
    Route::apiResource('products', ProductController::class);
    
    // Routes pour les catégories
    Route::apiResource('categories', CategoryController::class);
    
    // Route pour obtenir les produits suggérés
    Route::get('products/{id}/suggested', [ProductController::class, 'show']);

    // Routes d'authentification (avec sessions via middleware web)
    Route::middleware('web')->group(function () {
        Route::post('auth/register', [\App\Http\Controllers\Api\Auth\RegisterController::class, 'register']);
        Route::post('auth/login', [LoginController::class, 'login']);
        Route::post('auth/logout', [LoginController::class, 'logout'])->middleware('auth');
        // Pas de middleware auth : invité = 200 { user: null } (évite un 401 bruyant dans la console du navigateur)
        Route::get('auth/user', [LoginController::class, 'user']);
        
        // Matériaux
        Route::get('materials', [\App\Http\Controllers\Api\MaterialController::class, 'index']);
    });

    // Routes Client (Compte)
    Route::prefix('customer')->middleware(['web', 'auth'])->group(function () {
        Route::get('profile', [\App\Http\Controllers\Api\Customer\ProfileController::class, 'show']);
        Route::put('profile', [\App\Http\Controllers\Api\Customer\ProfileController::class, 'update']);
        Route::delete('profile', [\App\Http\Controllers\Api\Customer\ProfileController::class, 'destroy']);
        
        Route::get('orders', [\App\Http\Controllers\Api\Customer\OrderController::class, 'index']);
        Route::get('orders/{id}', [\App\Http\Controllers\Api\Customer\OrderController::class, 'show']);
        Route::post('checkout/confirm', [CheckoutController::class, 'confirm']);
        // Panier persistant
    Route::get('/cart', [App\Http\Controllers\Api\CartController::class, 'index']);
    Route::post('/cart/sync', [App\Http\Controllers\Api\CartController::class, 'sync']);
    Route::delete('/cart', [App\Http\Controllers\Api\CartController::class, 'clear']);
});

    // Checkout (public)
    Route::get('checkout/shipping-methods', [CheckoutController::class, 'shippingMethods']);
    Route::post('checkout/pricing', [CheckoutController::class, 'pricing']);

    // Routes Admin (protégées par authentification avec sessions)
    Route::prefix('admin')->middleware(['web', 'auth'])->group(function () {
        Route::get('stats', StatsController::class);
        Route::apiResource('products', AdminProductController::class);
        Route::apiResource('orders', AdminOrderController::class);
        Route::apiResource('users', AdminUserController::class);
        Route::apiResource('shipping-methods', AdminShippingMethodController::class);
        Route::apiResource('promo-codes', AdminPromoCodeController::class);
        Route::post('images/upload', [AdminImageController::class, 'upload']);
        Route::delete('images/delete', [AdminImageController::class, 'delete']);
    });
});

