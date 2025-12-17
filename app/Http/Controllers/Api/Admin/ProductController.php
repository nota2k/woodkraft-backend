<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Liste des produits (admin)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['images', 'categories', 'variations']);

        // Filtrage par catégorie
        if ($request->has('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        // Recherche
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('reference', 'like', '%' . $request->search . '%');
            });
        }

        $products = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($products);
    }

    /**
     * Créer un produit
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'reference' => 'required|string|unique:products,reference',
            'materials' => 'nullable|string',
            'dimensions' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'images' => 'nullable|array',
            'images.*' => 'string',
            'variations' => 'nullable|array',
            'variations.*.variation_id' => 'exists:variations,id',
            'variations.*.quantity' => 'integer|min:0',
        ]);

        $product = Product::create($validated);

        // Attacher les catégories
        if (isset($validated['category_ids'])) {
            $product->categories()->attach($validated['category_ids']);
        }

        // Ajouter les images
        if (isset($validated['images']) && count($validated['images']) > 0) {
            foreach ($validated['images'] as $index => $imagePath) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $imagePath,
                    'is_default' => $index === 0,
                    'order' => $index,
                ]);
            }
        }

        // Ajouter les variations
        if (isset($validated['variations'])) {
            foreach ($validated['variations'] as $variation) {
                $product->variations()->attach($variation['variation_id'], [
                    'quantity' => $variation['quantity'] ?? 0,
                ]);
            }
        }

        $product->load(['images', 'categories', 'variations']);

        return response()->json($product, 201);
    }

    /**
     * Afficher un produit
     */
    public function show(string $id): JsonResponse
    {
        $product = Product::with(['images', 'categories', 'variations', 'suggestedProducts.images'])
            ->findOrFail($id);

        return response()->json($product);
    }

    /**
     * Mettre à jour un produit
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'description' => 'sometimes|string',
            'reference' => 'sometimes|string|unique:products,reference,' . $id,
            'materials' => 'nullable|string',
            'dimensions' => 'nullable|string',
            'quantity' => 'sometimes|integer|min:0',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'images' => 'nullable|array',
            'images.*' => 'string',
            'variations' => 'nullable|array',
            'variations.*.variation_id' => 'exists:variations,id',
            'variations.*.quantity' => 'integer|min:0',
        ]);

        $product->update($validated);

        // Mettre à jour les catégories
        if (isset($validated['category_ids'])) {
            $product->categories()->sync($validated['category_ids']);
        }

        // Mettre à jour les images
        if (isset($validated['images'])) {
            $product->images()->delete();
            foreach ($validated['images'] as $index => $imagePath) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $imagePath,
                    'is_default' => $index === 0,
                    'order' => $index,
                ]);
            }
        }

        // Mettre à jour les variations
        if (isset($validated['variations'])) {
            $product->variations()->detach();
            foreach ($validated['variations'] as $variation) {
                $product->variations()->attach($variation['variation_id'], [
                    'quantity' => $variation['quantity'] ?? 0,
                ]);
            }
        }

        $product->load(['images', 'categories', 'variations']);

        return response()->json($product);
    }

    /**
     * Supprimer un produit
     */
    public function destroy(string $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Produit supprimé avec succès'], 200);
    }
}
