<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckoutConfigTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_create_shipping_method(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/shipping-methods', [
            'name' => 'Express',
            'code' => 'EXPRESS',
            'price' => 19.9,
            'is_active' => true,
            'position' => 1,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('shipping_methods', [
            'code' => 'EXPRESS',
            'name' => 'Express',
        ]);
    }

    #[Test]
    public function admin_can_create_promo_code(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/promo-codes', [
            'code' => 'WELCOME15',
            'name' => 'Bienvenue',
            'discount_type' => 'percent',
            'discount_value' => 15,
            'is_active' => true,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('promo_codes', [
            'code' => 'WELCOME15',
            'name' => 'Bienvenue',
        ]);
    }
}
