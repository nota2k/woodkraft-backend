<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShippingMethodController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ShippingMethod::query();

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $methods = $query
            ->orderBy('position')
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json($methods);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:80|unique:shipping_methods,code',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'free_from_amount' => 'nullable|numeric|gt:0',
            'is_active' => 'boolean',
            'position' => 'nullable|integer|min:0',
        ]);

        $method = ShippingMethod::create([
            ...$validated,
            'code' => strtoupper($validated['code']),
            'is_active' => $validated['is_active'] ?? true,
            'position' => $validated['position'] ?? 0,
        ]);

        return response()->json($method, 201);
    }

    public function show(string $id): JsonResponse
    {
        return response()->json(ShippingMethod::findOrFail($id));
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $method = ShippingMethod::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => [
                'sometimes',
                'string',
                'max:80',
                Rule::unique('shipping_methods', 'code')->ignore($method->id),
            ],
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'free_from_amount' => 'nullable|numeric|gt:0',
            'is_active' => 'boolean',
            'position' => 'nullable|integer|min:0',
        ]);

        if (array_key_exists('code', $validated)) {
            $validated['code'] = strtoupper($validated['code']);
        }

        $method->update($validated);

        return response()->json($method->fresh());
    }

    public function destroy(string $id): JsonResponse
    {
        $method = ShippingMethod::findOrFail($id);
        $method->delete();

        return response()->json(['message' => 'Mode de livraison supprimé avec succès.']);
    }
}
