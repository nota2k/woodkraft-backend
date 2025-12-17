<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    /**
     * Liste des commandes
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['user', 'items.product.images']);

        // Filtrage par statut
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Recherche par numéro de commande ou email client
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_email', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $request->search . '%');
            });
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($orders);
    }

    /**
     * Afficher une commande
     */
    public function show(string $id): JsonResponse
    {
        $order = Order::with(['user', 'items.product.images', 'items.product.categories'])
            ->findOrFail($id);

        return response()->json($order);
    }

    /**
     * Mettre à jour le statut d'une commande
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status' => 'sometimes|in:pending,processing,shipped,delivered,cancelled',
            'notes' => 'nullable|string',
        ]);

        $order->update($validated);

        $order->load(['user', 'items.product.images']);

        return response()->json($order);
    }

    /**
     * Supprimer une commande
     */
    public function destroy(string $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'Commande supprimée avec succès'], 200);
    }
}
