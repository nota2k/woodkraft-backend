<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;

Route::prefix('v1')->group(function () {
    // Routes pour les produits
    Route::apiResource('products', ProductController::class);
    
    // Routes pour les catégories
    Route::apiResource('categories', CategoryController::class);
    
    // Route pour obtenir les produits suggérés
    Route::get('products/{id}/suggested', [ProductController::class, 'show']);
});

