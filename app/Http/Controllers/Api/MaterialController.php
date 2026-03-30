<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\JsonResponse;

use OpenApi\Attributes as OA;

class MaterialController extends Controller
{
    /**
     * Liste des matériaux
     */
    #[OA\Get(
        path: '/api/v1/materials',
        tags: ['Materials'],
        summary: 'Liste des matériaux',
        responses: [
            new OA\Response(response: 200, description: 'Liste des matériaux')
        ]
    )]
    public function index(): JsonResponse
    {
        return response()->json(Material::orderBy('name')->get());
    }
}
