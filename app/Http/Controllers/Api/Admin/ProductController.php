<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Variation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Liste des produits (admin)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['categories', 'images', 'defaultImage', 'variations', 'material']);

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
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'depth' => 'nullable|numeric|min:0',
            'material_id' => 'nullable|exists:materials,id',
            'quantity' => 'required|integer|min:0',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:20480',
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
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('products', $filename, 'public');

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => Storage::disk('public')->url($path),
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
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'depth' => 'nullable|numeric|min:0',
            'material_id' => 'nullable|exists:materials,id',
            'quantity' => 'sometimes|integer|min:0',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:20480',
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'exists:product_images,id',
            'default_image_id' => 'nullable|integer|exists:product_images,id',
            'variations' => 'nullable|array',
            'variations.*.variation_id' => 'exists:variations,id',
            'variations.*.quantity' => 'integer|min:0',
        ]);

        $product->update($validated);

        // Mettre à jour les catégories
        if (isset($validated['category_ids'])) {
            $product->categories()->sync($validated['category_ids']);
        }

        // Supprimer les images marquées
        if ($request->has('delete_images')) {
            $imagesToDelete = ProductImage::whereIn('id', $request->delete_images)->get();
            foreach ($imagesToDelete as $image) {
                $image->delete();
            }
        }

        // Ajouter de nouvelles images (append)
        if ($request->hasFile('images')) {
            $lastOrder = $product->images()->max('order') ?? -1;
            foreach ($request->file('images') as $index => $file) {
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('products', $filename, 'public');

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => Storage::disk('public')->url($path),
                    'is_default' => false,
                    'order' => $lastOrder + $index + 1,
                ]);
            }
        }

        // Mettre à jour l'image par défaut
        if ($request->has('default_image_id')) {
            $product->images()->update(['is_default' => false]);
            ProductImage::where('id', $request->default_image_id)
                ->update(['is_default' => true]);
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
