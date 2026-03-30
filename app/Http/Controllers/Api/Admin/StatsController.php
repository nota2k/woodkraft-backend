<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

final class StatsController extends Controller
{
    private const LOW_STOCK_THRESHOLD = 5;

    private const NEW_PRODUCTS_DAYS = 30;

    public function __invoke(): JsonResponse
    {
        // Même contrat que les autres routes `api/v1/admin/*` : middleware `auth` uniquement.
        return response()->json([
            'total_products' => Product::query()->count(),
            'total_categories' => Category::query()->count(),
            'low_stock' => Product::query()
                ->where('quantity', '<', self::LOW_STOCK_THRESHOLD)
                ->count(),
            'new_products' => Product::query()
                ->where('created_at', '>=', now()->subDays(self::NEW_PRODUCTS_DAYS))
                ->count(),
        ]);
    }
}
