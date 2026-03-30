<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PromoCodeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PromoCode::query();

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $promos = $query
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json($promos);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:60|unique:promo_codes,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => ['required', Rule::in(['fixed', 'percent'])],
            'discount_value' => 'required|numeric|gt:0',
            'minimum_amount' => 'nullable|numeric|min:0',
            'maximum_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'used_count' => 'nullable|integer|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
        ]);

        if ($validated['discount_type'] === 'percent' && (float) $validated['discount_value'] > 100) {
            return response()->json([
                'message' => 'Le pourcentage de remise ne peut pas dépasser 100.',
                'errors' => ['discount_value' => ['Le pourcentage de remise ne peut pas dépasser 100.']],
            ], 422);
        }

        $promo = PromoCode::create([
            ...$validated,
            'code' => strtoupper($validated['code']),
            'is_active' => $validated['is_active'] ?? true,
            'used_count' => $validated['used_count'] ?? 0,
        ]);

        return response()->json($promo, 201);
    }

    public function show(string $id): JsonResponse
    {
        return response()->json(PromoCode::findOrFail($id));
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $promo = PromoCode::findOrFail($id);

        $validated = $request->validate([
            'code' => [
                'sometimes',
                'string',
                'max:60',
                Rule::unique('promo_codes', 'code')->ignore($promo->id),
            ],
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => [Rule::in(['fixed', 'percent'])],
            'discount_value' => 'sometimes|numeric|gt:0',
            'minimum_amount' => 'nullable|numeric|min:0',
            'maximum_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'used_count' => 'nullable|integer|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
        ]);

        if (array_key_exists('code', $validated)) {
            $validated['code'] = strtoupper($validated['code']);
        }

        $discountType = $validated['discount_type'] ?? $promo->discount_type;
        $discountValue = (float) ($validated['discount_value'] ?? $promo->discount_value);
        if ($discountType === 'percent' && $discountValue > 100) {
            return response()->json([
                'message' => 'Le pourcentage de remise ne peut pas dépasser 100.',
                'errors' => ['discount_value' => ['Le pourcentage de remise ne peut pas dépasser 100.']],
            ], 422);
        }

        $promo->update($validated);

        return response()->json($promo->fresh());
    }

    public function destroy(string $id): JsonResponse
    {
        $promo = PromoCode::findOrFail($id);
        $promo->delete();

        return response()->json(['message' => 'Code promo supprimé avec succès.']);
    }
}
