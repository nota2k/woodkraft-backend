<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Admin\ImageController as AdminImageController;
use App\Http\Controllers\Api\Auth\LoginController;

Route::prefix('v1')->group(function () {
    // Routes pour les produits
    Route::apiResource('products', ProductController::class);
    
    // Routes pour les catégories
    Route::apiResource('categories', CategoryController::class);
    
    // Route pour obtenir les produits suggérés
    Route::get('products/{id}/suggested', [ProductController::class, 'show']);

    // Routes d'authentification (avec sessions)
    Route::middleware(['web'])->group(function () {
        Route::post('auth/login', [LoginController::class, 'login']);
        Route::post('auth/logout', [LoginController::class, 'logout'])->middleware('auth');
        Route::get('auth/user', [LoginController::class, 'user'])->middleware('auth');
    });

    // Routes Admin (protégées par authentification avec sessions)
    Route::prefix('admin')->middleware(['web', 'auth'])->group(function () {
        Route::apiResource('products', AdminProductController::class);
        Route::apiResource('orders', AdminOrderController::class);
        Route::apiResource('users', AdminUserController::class);
        Route::post('images/upload', [AdminImageController::class, 'upload']);
        Route::delete('images/delete', [AdminImageController::class, 'delete']);
    });
});

