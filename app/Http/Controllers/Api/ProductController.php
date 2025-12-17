<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Variation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    /**
     * Liste des produits
     */
    #[OA\Get(
        path: '/api/v1/products',
        tags: ['Products'],
        summary: 'Liste des produits',
        parameters: [
            new OA\Parameter(name: 'category_id', in: 'query', description: 'ID de la catégorie', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', description: 'Recherche par titre ou description', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Nombre d\'éléments par page', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste des produits')
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['images', 'categories', 'variations']);

        // Filtrage par catégorie
        if ($request->has('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        // Recherche par titre ou description
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $products = $query->paginate($request->get('per_page', 15));

        return response()->json($products);
    }

    /**
     * Créer un nouveau produit
     */
    #[OA\Post(
        path: '/api/v1/products',
        tags: ['Products'],
        summary: 'Créer un nouveau produit',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Table en chêne'),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 899.99),
                    new OA\Property(property: 'description', type: 'string', example: 'Magnifique table en chêne massif'),
                    new OA\Property(property: 'reference', type: 'string', example: 'TAB-001'),
                    new OA\Property(property: 'materials', type: 'string', nullable: true, example: 'Chêne massif'),
                    new OA\Property(property: 'dimensions', type: 'string', nullable: true, example: '200x100x75 cm'),
                    new OA\Property(property: 'quantity', type: 'integer', example: 5),
                    new OA\Property(property: 'category_ids', type: 'array', items: new OA\Items(type: 'integer')),
                    new OA\Property(property: 'images', type: 'array', items: new OA\Items(type: 'string')),
                    new OA\Property(property: 'variations', type: 'array', items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'variation_id', type: 'integer'),
                            new OA\Property(property: 'quantity', type: 'integer'),
                        ]
                    )),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Produit créé avec succès'),
            new OA\Response(response: 422, description: 'Erreur de validation')
        ]
    )]
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
    #[OA\Get(
        path: '/api/v1/products/{id}',
        tags: ['Products'],
        summary: 'Afficher un produit',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Détails du produit'),
            new OA\Response(response: 404, description: 'Produit non trouvé')
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $product = Product::with(['images', 'categories', 'variations', 'suggestedProducts.images'])
            ->findOrFail($id);

        return response()->json($product);
    }

    /**
     * Mettre à jour un produit
     */
    #[OA\Put(
        path: '/api/v1/products/{id}',
        tags: ['Products'],
        summary: 'Mettre à jour un produit',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'price', type: 'number', format: 'float'),
                    new OA\Property(property: 'description', type: 'string'),
                    new OA\Property(property: 'reference', type: 'string'),
                    new OA\Property(property: 'materials', type: 'string', nullable: true),
                    new OA\Property(property: 'dimensions', type: 'string', nullable: true),
                    new OA\Property(property: 'quantity', type: 'integer'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Produit mis à jour'),
            new OA\Response(response: 404, description: 'Produit non trouvé')
        ]
    )]
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
    #[OA\Delete(
        path: '/api/v1/products/{id}',
        tags: ['Products'],
        summary: 'Supprimer un produit',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Produit supprimé'),
            new OA\Response(response: 404, description: 'Produit non trouvé')
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Produit supprimé avec succès'], 200);
    }
}
