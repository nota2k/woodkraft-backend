<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'price' => $this->faker->randomFloat(2, 100, 2000),
            'description' => $this->faker->paragraph,
            'reference' => 'WK-' . Str::upper(Str::random(6)),
            'length' => round($this->faker->randomFloat(1, 20, 200), 1),
            'width' => round($this->faker->randomFloat(1, 10, 100), 1),
            'depth' => round($this->faker->randomFloat(1, 5, 50), 1),
            'quantity' => $this->faker->numberBetween(0, 50),
            'material_id' => null,
        ];
    }
}
