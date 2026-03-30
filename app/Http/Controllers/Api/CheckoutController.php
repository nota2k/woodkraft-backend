<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\ShippingMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function shippingMethods(): JsonResponse
    {
        $methods = ShippingMethod::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        return response()->json($methods);
    }

    public function pricing(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.productId' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'promoCode' => 'nullable|string|max:60',
            'shippingMethodId' => 'nullable|integer|exists:shipping_methods,id',
        ]);

        $items = collect($validated['items']);
        $productIds = $items->pluck('productId')->unique()->values();

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $subtotal = $items->reduce(function (float $carry, array $item) use ($products): float {
            $product = $products->get((int) $item['productId']);
            if ($product === null) {
                return $carry;
            }

            return $carry + ((float) $product->price * (int) $item['quantity']);
        }, 0.0);

        $discountAmount = 0.0;
        $promoPayload = null;
        $promoValidation = null;

        if (!empty($validated['promoCode'])) {
            $promoCodeInput = strtoupper(trim($validated['promoCode']));
            $promoCode = PromoCode::query()
                ->whereRaw('UPPER(code) = ?', [$promoCodeInput])
                ->first();

            if ($promoCode === null) {
                $promoValidation = [
                    'is_valid' => false,
                    'message' => 'Code promo introuvable.',
                ];
            } elseif (!$promoCode->isCurrentlyValid()) {
                $promoValidation = [
                    'is_valid' => false,
                    'message' => 'Code promo inactif ou expiré.',
                ];
            } elseif ($promoCode->minimum_amount !== null && $subtotal < (float) $promoCode->minimum_amount) {
                $promoValidation = [
                    'is_valid' => false,
                    'message' => 'Montant minimum non atteint pour ce code promo.',
                ];
            } else {
                if ($promoCode->discount_type === 'fixed') {
                    $discountAmount = (float) $promoCode->discount_value;
                } else {
                    $discountAmount = $subtotal * ((float) $promoCode->discount_value / 100);
                }

                if ($promoCode->maximum_discount_amount !== null) {
                    $discountAmount = min($discountAmount, (float) $promoCode->maximum_discount_amount);
                }

                $discountAmount = min($discountAmount, $subtotal);

                $promoPayload = $promoCode;
                $promoValidation = [
                    'is_valid' => true,
                    'message' => 'Code promo appliqué.',
                ];
            }
        }

        $shippingAmount = 0.0;
        $shippingPayload = null;
        if (!empty($validated['shippingMethodId'])) {
            $shippingMethod = ShippingMethod::query()
                ->where('id', $validated['shippingMethodId'])
                ->where('is_active', true)
                ->first();

            if ($shippingMethod !== null) {
                if (
                    $shippingMethod->free_from_amount !== null
                    && $subtotal >= (float) $shippingMethod->free_from_amount
                ) {
                    $shippingAmount = 0.0;
                } else {
                    $shippingAmount = (float) $shippingMethod->price;
                }

                $shippingPayload = [
                    'id' => $shippingMethod->id,
                    'name' => $shippingMethod->name,
                    'code' => $shippingMethod->code,
                    'base_price' => (float) $shippingMethod->price,
                    'shipping_amount' => $shippingAmount,
                    'free_from_amount' => $shippingMethod->free_from_amount !== null
                        ? (float) $shippingMethod->free_from_amount
                        : null,
                ];
            }
        }

        $total = max(0, $subtotal - $discountAmount + $shippingAmount);

        return response()->json([
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($discountAmount, 2),
            'shipping_amount' => round($shippingAmount, 2),
            'total' => round($total, 2),
            'promo' => $promoPayload ? [
                'id' => $promoPayload->id,
                'code' => $promoPayload->code,
                'name' => $promoPayload->name,
                'discount_type' => $promoPayload->discount_type,
                'discount_value' => (float) $promoPayload->discount_value,
            ] : null,
            'promo_validation' => $promoValidation,
            'shipping_method' => $shippingPayload,
        ]);
    }
}
