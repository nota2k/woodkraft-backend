<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Get user's cart items
     */
    public function index()
    {
        $items = Auth::user()->cartItems()->with('product.images')->get();
        return response()->json($items);
    }

    /**
     * Sync full cart from frontend
     */
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
    public function clear()
    {
        Auth::user()->cartItems()->delete();
        return response()->json(['message' => 'Cart cleared']);
    }
}
