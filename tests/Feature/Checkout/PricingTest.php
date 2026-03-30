<?php

namespace Tests\Feature\Checkout;

use App\Models\Product;
use App\Models\PromoCode;
use App\Models\ShippingMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PricingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_calculates_checkout_totals_with_promo_and_shipping(): void
    {
        $product = Product::factory()->create(['price' => 100]);

        PromoCode::create([
            'code' => 'PROMO10',
            'name' => 'Promo 10%',
            'discount_type' => 'percent',
            'discount_value' => 10,
            'is_active' => true,
        ]);

        $shippingMethod = ShippingMethod::create([
            'name' => 'Standard',
            'code' => 'STANDARD',
            'price' => 12,
            'is_active' => true,
            'position' => 1,
        ]);

        $response = $this->postJson('/api/v1/checkout/pricing', [
            'items' => [
                ['productId' => $product->id, 'quantity' => 2],
            ],
            'promoCode' => 'PROMO10',
            'shippingMethodId' => $shippingMethod->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'subtotal' => 200,
                'discount_amount' => 20,
                'shipping_amount' => 12,
                'total' => 192,
            ]);
    }
}
