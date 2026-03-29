<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\JsonResponse;

class MaterialController extends Controller
{
    /**
     * Liste des matériaux
     */
    public function index(): JsonResponse
    {
        return response()->json(Material::orderBy('name')->get());
    }
}
