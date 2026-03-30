<?php

namespace Tests\Feature\Checkout;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ConfirmOrderTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_confirms_order_and_clears_cart_when_payment_simulation_is_success(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create(['price' => 100]);
        $shipping = ShippingMethod::create([
            'name' => 'Standard',
            'code' => 'STANDARD',
            'price' => 10,
            'is_active' => true,
            'position' => 1,
        ]);

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/customer/checkout/confirm', [
            'items' => [
                ['productId' => $product->id, 'quantity' => 2],
            ],
            'shippingMethodId' => $shipping->id,
            'payment_simulation' => 'success',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'payment_status' => 'success',
            ]);

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseCount('cart_items', 0);
    }

    #[Test]
    public function it_keeps_cart_when_payment_simulation_fails(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create(['price' => 50]);
        $shipping = ShippingMethod::create([
            'name' => 'Express',
            'code' => 'EXPRESS',
            'price' => 5,
            'is_active' => true,
            'position' => 1,
        ]);

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/customer/checkout/confirm', [
            'items' => [
                ['productId' => $product->id, 'quantity' => 1],
            ],
            'shippingMethodId' => $shipping->id,
            'payment_simulation' => 'failed',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'payment_status' => 'failed',
            ]);

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseCount('cart_items', 1);
    }
}
