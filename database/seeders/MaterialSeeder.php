<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $materials = [
            'Hêtre',
            'Chêne',
            'Noyer',
            'Composite',
        ];

        foreach ($materials as $material) {
            \App\Models\Material::firstOrCreate([
                'name' => $material,
            ]);
        }
    }
}
