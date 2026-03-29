<?php

namespace Database\Factories;

use App\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Hêtre',
                'Noyer',
                'Composite',
                'Chêne',
                'Frêne',
                'Érable',
                'Pin',
                'Oseko'
            ]),
        ];
    }
}
