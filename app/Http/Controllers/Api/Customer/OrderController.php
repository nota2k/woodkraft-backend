<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Liste les commandes du client actuel
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $orders = Order::with(['items.product.images'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10));

        return response()->json($orders);
    }

    /**
     * Détail d'une commande
     */
    public function show($id)
    {
        $user = Auth::user();

        $order = Order::with(['items.product.images', 'items.product.categories'])
            ->where('user_id', $user->id)
            ->findOrFail($id);

        return response()->json($order);
    }
}
