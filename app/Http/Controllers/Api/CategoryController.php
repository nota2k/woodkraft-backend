<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class CategoryController extends Controller
{
    /**
     * Liste des catégories
     */
    #[OA\Get(
        path: '/api/v1/categories',
        tags: ['Categories'],
        summary: 'Liste des catégories',
        responses: [
            new OA\Response(response: 200, description: 'Liste des catégories')
        ]
    )]
    public function index(): JsonResponse
    {
        $categories = Category::withCount('products')->get();

        return response()->json($categories);
    }

    /**
     * Créer une nouvelle catégorie
     */
    #[OA\Post(
        path: '/api/v1/categories',
        tags: ['Categories'],
        summary: 'Créer une nouvelle catégorie',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Tables'),
                    new OA\Property(property: 'slug', type: 'string', nullable: true, example: 'tables'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Tables en bois massif'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Catégorie créée avec succès'),
            new OA\Response(response: 422, description: 'Erreur de validation')
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:categories,slug',
            'description' => 'nullable|string',
        ]);

        if (!isset($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category = Category::create($validated);

        return response()->json($category, 201);
    }

    /**
     * Afficher une catégorie
     */
    #[OA\Get(
        path: '/api/v1/categories/{id}',
        tags: ['Categories'],
        summary: 'Afficher une catégorie',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Détails de la catégorie'),
            new OA\Response(response: 404, description: 'Catégorie non trouvée')
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $category = Category::with('products.images')->findOrFail($id);

        return response()->json($category);
    }

    /**
     * Mettre à jour une catégorie
     */
    #[OA\Put(
        path: '/api/v1/categories/{id}',
        tags: ['Categories'],
        summary: 'Mettre à jour une catégorie',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'slug', type: 'string', nullable: true),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Catégorie mise à jour'),
            new OA\Response(response: 404, description: 'Catégorie non trouvée')
        ]
    )]
    public function update(Request $request, string $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|unique:categories,slug,' . $id,
            'description' => 'nullable|string',
        ]);

        if (isset($validated['name']) && !isset($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

        return response()->json($category);
    }

    /**
     * Supprimer une catégorie
     */
    #[OA\Delete(
        path: '/api/v1/categories/{id}',
        tags: ['Categories'],
        summary: 'Supprimer une catégorie',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Catégorie supprimée'),
            new OA\Response(response: 404, description: 'Catégorie non trouvée')
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json(['message' => 'Catégorie supprimée avec succès'], 200);
    }
}
