<?php

namespace Database\Seeders;

use App\Models\Variation;
use Illuminate\Database\Seeder;

class VariationSeeder extends Seeder
{
    public function run(): void
    {
        // Variations de couleur
        $colors = [
            'Chêne naturel',
            'Chêne foncé',
            'Noyer',
            'Hêtre',
            'Pin',
            'Châtaignier',
            'Teck',
        ];

        foreach ($colors as $color) {
            Variation::create([
                'type' => 'color',
                'value' => $color,
            ]);
        }

        // Variations de taille
        $sizes = [
            'Petit (80x40cm)',
            'Moyen (120x60cm)',
            'Grand (160x80cm)',
            'Très grand (200x100cm)',
        ];

        foreach ($sizes as $size) {
            Variation::create([
                'type' => 'size',
                'value' => $size,
            ]);
        }
    }
}
