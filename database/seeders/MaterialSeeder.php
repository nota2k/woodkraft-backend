<?php

namespace Database\Seeders;

use App\Models\Material;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $materials = ["Hêtre", "Noyer", "Composite", "Chêne"];

        foreach ($materials as $name) {
            Material::firstOrCreate(['name' => $name]);
        }
    }
}
