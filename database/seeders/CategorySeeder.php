<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Tables',
                'slug' => 'tables',
                'description' => 'Tables en bois massif pour votre salon, salle à manger ou bureau',
            ],
            [
                'name' => 'Chaises',
                'slug' => 'chaises',
                'description' => 'Chaises et fauteuils en bois artisanaux',
            ],
            [
                'name' => 'Armoires',
                'slug' => 'armoires',
                'description' => 'Armoires et dressings en bois massif',
            ],
            [
                'name' => 'Étagères',
                'slug' => 'etageres',
                'description' => 'Étagères et bibliothèques en bois',
            ],
            [
                'name' => 'Lits',
                'slug' => 'lits',
                'description' => 'Lits et têtes de lit en bois massif',
            ],
            [
                'name' => 'Commodes',
                'slug' => 'commodes',
                'description' => 'Commodes et meubles de rangement',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
