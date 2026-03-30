<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use OpenApi\Attributes as OA;

class CartController extends Controller
{
    /**
     * Get user's cart items
     */
    #[OA\Get(
        path: '/api/customer/cart',
        tags: ['Cart'],
        summary: 'Récupérer le panier du client connecté',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Articles du panier')
        ]
    )]
    public function index()
    {
        $items = Auth::user()->cartItems()->with('product.images')->get();
        return response()->json($items);
    }

    /**
     * Sync full cart from frontend
     */
    #[OA\Post(
        path: '/api/customer/cart/sync',
        tags: ['Cart'],
        summary: 'Synchroniser le panier complet',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'items', type: 'array', items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'productId', type: 'integer'),
                            new OA\Property(property: 'quantity', type: 'integer'),
                            new OA\Property(property: 'selectedColor', type: 'object', nullable: true),
                        ]
                    ))
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Panier synchronisé')
        ]
    )]
    public function sync(Request $request)
    {
        $user = Auth::user();
        $frontendItems = $request->input('items', []);

        // Clear current cart
        $user->cartItems()->delete();

        // Re-insert from frontend
        foreach ($frontendItems as $item) {
            $user->cartItems()->create([
                'product_id' => $item['productId'],
                'quantity'   => $item['quantity'],
                'options'    => $item['selectedColor'] ?? null,
            ]);
        }

        return response()->json(['message' => 'Cart synced successfully']);
    }

    /**
     * Clear cart
     */
    #[OA\Delete(
        path: '/api/customer/cart',
        tags: ['Cart'],
        summary: 'Vider le panier',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Panier vidé')
        ]
    )]
    public function clear()
    {
        Auth::user()->cartItems()->delete();
        return response()->json(['message' => 'Cart cleared']);
    }
}
