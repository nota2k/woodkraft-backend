# Memory bank — Woodkraft (API Laravel)

> Section auto entre `MEMORY_BANK_AUTO_START` et `MEMORY_BANK_AUTO_END`. Zone manuelle préservée.

<!-- MEMORY_BANK_MANUAL_START -->
_Décisions API, auth, règles métier, URLs de déploiement, contrats avec le front, etc._
<!-- MEMORY_BANK_MANUAL_END -->

<!-- MEMORY_BANK_AUTO_START -->
## Métadonnées

- **Dernière génération** : 2026-03-30T22:27:47.586Z
- **Racine app** : `/Users/nellybabillon/Sites/woodkraft-app/woodkraft-backend` (`woodkraft-backend`)

## Stack (extraits)

- **PHP (composer.lock)** : ^8.2
- php ^8.2
- framework ^12.0
- image-laravel ^4.0
- l5-swagger *
- tailwindcss (npm) ^4.0.0
- vite (npm) ^7.0.7

## Préfixe API

- Fichier principal : `routes/api.php` — groupe `prefix('v1')` → URLs de type `/api/v1/...`

## Inventaire

- Fichiers PHP dans `app/` : **33**
- Contrôleurs (`app/Http/Controllers`) : **17**
- Migrations : **23**

## Lignes de routes `routes/api.php` (aperçu)

- `Route::prefix('v1')->group(function () {`
- `Route::apiResource('products', ProductController::class);`
- `Route::apiResource('categories', CategoryController::class);`
- `Route::get('products/{id}/suggested', [ProductController::class, 'show']);`
- `Route::middleware('web')->group(function () {`
- `Route::post('auth/register', [\App\Http\Controllers\Api\Auth\RegisterController::class, 'register']);`
- `Route::post('auth/login', [LoginController::class, 'login']);`
- `Route::post('auth/logout', [LoginController::class, 'logout'])->middleware('auth');`
- `Route::get('auth/user', [LoginController::class, 'user']);`
- `Route::get('materials', [\App\Http\Controllers\Api\MaterialController::class, 'index']);`
- `Route::prefix('customer')->middleware(['web', 'auth'])->group(function () {`
- `Route::get('profile', [\App\Http\Controllers\Api\Customer\ProfileController::class, 'show']);`
- `Route::put('profile', [\App\Http\Controllers\Api\Customer\ProfileController::class, 'update']);`
- `Route::delete('profile', [\App\Http\Controllers\Api\Customer\ProfileController::class, 'destroy']);`
- `Route::get('orders', [\App\Http\Controllers\Api\Customer\OrderController::class, 'index']);`
- `Route::get('orders/{id}', [\App\Http\Controllers\Api\Customer\OrderController::class, 'show']);`
- `Route::get('/cart', [App\Http\Controllers\Api\CartController::class, 'index']);`
- `Route::post('/cart/sync', [App\Http\Controllers\Api\CartController::class, 'sync']);`
- `Route::delete('/cart', [App\Http\Controllers\Api\CartController::class, 'clear']);`
- `Route::get('checkout/shipping-methods', [CheckoutController::class, 'shippingMethods']);`
- `Route::post('checkout/pricing', [CheckoutController::class, 'pricing']);`
- `Route::prefix('admin')->middleware(['web', 'auth'])->group(function () {`
- `Route::get('stats', StatsController::class);`
- `Route::apiResource('products', AdminProductController::class);`
- `Route::apiResource('orders', AdminOrderController::class);`
- `Route::apiResource('users', AdminUserController::class);`
- `Route::apiResource('shipping-methods', AdminShippingMethodController::class);`
- `Route::apiResource('promo-codes', AdminPromoCodeController::class);`
- `Route::post('images/upload', [AdminImageController::class, 'upload']);`
- `Route::delete('images/delete', [AdminImageController::class, 'delete']);`

<!-- MEMORY_BANK_AUTO_END -->
