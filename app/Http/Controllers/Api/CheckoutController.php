<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\ShippingMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        return response()->json($this->calculatePricing($validated));
    }

    public function confirm(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.productId' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'promoCode' => 'nullable|string|max:60',
            'shippingMethodId' => 'required|integer|exists:shipping_methods,id',
            'payment_simulation' => 'nullable|in:success,failed,requires_action',
        ]);

        $pricing = $this->calculatePricing($validated);
        if (
            !empty($validated['promoCode'])
            && ($pricing['promo_validation']['is_valid'] ?? false) !== true
        ) {
            return response()->json([
                'message' => $pricing['promo_validation']['message'] ?? 'Code promo invalide.',
            ], 422);
        }

        $user = Auth::user();
        /** @var Client|null $client */
        $client = Client::query()->where('user_id', $user->id)->first();
        $shippingAddress = $this->buildAddress(
            $client?->shipping_address,
            $client?->shipping_zip_code,
            $client?->shipping_city,
            $client?->shipping_country,
            'Adresse de livraison non renseignée'
        );
        $billingAddress = $this->buildAddress(
            $client?->billing_address,
            $client?->billing_zip_code,
            $client?->billing_city,
            $client?->billing_country,
            'Adresse de facturation non renseignée'
        );

        $paymentSimulation = $validated['payment_simulation'] ?? 'success';

        $order = DB::transaction(function () use ($validated, $pricing, $user, $client, $shippingAddress, $billingAddress, $paymentSimulation) {
            $status = $paymentSimulation === 'success' ? 'processing' : 'pending';
            $notePrefix = match ($paymentSimulation) {
                'failed' => '[PAIEMENT SIMULÉ: ECHEC]',
                'requires_action' => '[PAIEMENT SIMULÉ: ACTION REQUISE]',
                default => '[PAIEMENT SIMULÉ: SUCCES]',
            };

            $order = Order::create([
                'user_id' => $user->id,
                'shipping_method_id' => $pricing['shipping_method']['id'] ?? null,
                'promo_code_id' => $pricing['promo']['id'] ?? null,
                'status' => $status,
                'total_amount' => $pricing['total'],
                'subtotal_amount' => $pricing['subtotal'],
                'shipping_amount' => $pricing['shipping_amount'],
                'discount_amount' => $pricing['discount_amount'],
                'promo_code' => $pricing['promo']['code'] ?? null,
                'shipping_method_name' => $pricing['shipping_method']['name'] ?? null,
                'shipping_address' => $shippingAddress,
                'billing_address' => $billingAddress,
                'customer_name' => $client?->name ?? $user->name ?? 'Client',
                'customer_email' => $client?->email ?? $user->email,
                'customer_phone' => $client?->phone,
                'notes' => $notePrefix,
            ]);

            $items = collect($validated['items']);
            $productIds = $items->pluck('productId')->unique()->values();
            $products = Product::query()
                ->whereIn('id', $productIds)
                ->get()
                ->keyBy('id');

            foreach ($items as $item) {
                $product = $products->get((int) $item['productId']);
                if ($product === null) {
                    continue;
                }

                $quantity = (int) $item['quantity'];
                $unitPrice = (float) $product->price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $unitPrice * $quantity,
                ]);
            }

            if (($pricing['promo']['id'] ?? null) !== null) {
                PromoCode::query()
                    ->where('id', $pricing['promo']['id'])
                    ->increment('used_count');
            }

            if ($paymentSimulation === 'success') {
                $user->cartItems()->delete();
            }

            return $order->load(['items.product.images']);
        });

        return response()->json([
            'message' => match ($paymentSimulation) {
                'failed' => 'Paiement simulé refusé.',
                'requires_action' => 'Paiement simulé: action client requise.',
                default => 'Paiement simulé validé, commande confirmée.',
            },
            'payment_status' => $paymentSimulation,
            'order' => $order,
        ], $paymentSimulation === 'success' ? 201 : 200);
    }

    /**
     * @param array<string,mixed> $validated
     * @return array<string,mixed>
     */
    private function calculatePricing(array $validated): array
    {
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
            $promoCodeInput = strtoupper(trim((string) $validated['promoCode']));
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

        return [
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
        ];
    }

    private function buildAddress(
        ?string $address,
        ?string $zipCode,
        ?string $city,
        ?string $country,
        string $fallback
    ): string {
        $parts = array_values(array_filter([
            $address,
            trim(($zipCode ?? '') . ' ' . ($city ?? '')),
            $country,
        ]));

        if (count($parts) === 0) {
            return $fallback;
        }

        return implode(', ', $parts);
    }
}
