<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Material;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_admin_can_create_product_with_all_fields(): void
    {
        // 1. Setup
        $admin = User::factory()->create();
        $material = Material::create(['name' => 'Chêne massif']);
        $category = Category::create(['name' => 'Tables', 'slug' => 'tables']);

        $productData = [
            'title' => 'Table de luxe',
            'price' => 750.00,
            'description' => 'Magnifique table en bois pour votre salon.',
            'reference' => 'TABLE-LUXE-001',
            'length' => 180.5,
            'width' => 90.0,
            'depth' => 75.0,
            'material_id' => $material->id,
            'quantity' => 5,
            'category_ids' => [$category->id],
        ];

        // 2. Act
        $response = $this->actingAs($admin)
            ->postJson('/api/v1/admin/products', $productData);

        // 3. Assert
        $response->assertStatus(201);
        
        $this->assertDatabaseHas('products', [
            'title' => 'Table de luxe',
            'reference' => 'TABLE-LUXE-001',
            'material_id' => $material->id,
            'length' => 180.5,
            'width' => 90,
            'depth' => 75,
        ]);

        // Check relationship
        $product = Product::where('reference', 'TABLE-LUXE-001')->first();
        $this->assertEquals($category->id, $product->categories->first()->id);
        $this->assertEquals($material->id, $product->material_id);
    }

    /** @test */
    public function test_create_product_requires_title_and_price(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)
            ->postJson('/api/v1/admin/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'price', 'description', 'reference', 'quantity']);
    }

    /** @test */
    public function test_admin_can_update_product_dimensions(): void
    {
        // 1. Setup
        $admin = User::factory()->create();
        $product = Product::factory()->create([
            'length' => 100,
            'width' => 50,
            'depth' => 30
        ]);

        $updateData = [
            'title' => $product->title,
            'price' => $product->price,
            'description' => $product->description,
            'reference' => $product->reference,
            'quantity' => $product->quantity,
            'length' => 150.8,
            'width' => 80.2,
            'depth' => 45.0,
        ];

        // 2. Act
        $response = $this->actingAs($admin)
            ->putJson("/api/v1/admin/products/{$product->id}", $updateData);

        // 3. Assert
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'length' => 150.8,
            'width' => 80.2,
            'depth' => 45,
        ]);
    }

    /** @test */
    public function test_create_product_with_invalid_material_id(): void
    {
        $admin = User::factory()->create();

        $productData = [
            'title' => 'Table',
            'price' => 10,
            'description' => 'Test',
            'reference' => 'TEST-002',
            'quantity' => 1,
            'material_id' => 999, // Should not exist
        ];

        $response = $this->actingAs($admin)
            ->postJson('/api/v1/admin/products', $productData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['material_id']);
    }
}
