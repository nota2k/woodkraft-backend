<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use App\Models\Variation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $colorVariations = Variation::where('type', 'color')->get();
        $sizeVariations = Variation::where('type', 'size')->get();

        $products = [
            [
                'title' => 'Table à manger en chêne massif',
                'price' => 899.99,
                'description' => 'Magnifique table à manger en chêne massif, parfaite pour recevoir vos convives. Design moderne et élégant, cette table s\'intègre parfaitement dans tous les intérieurs.',
                'reference' => 'TAB-CHENE-001',
                'materials' => 'Chêne massif, finition huile naturelle',
                'dimensions' => '200 x 100 x 75 cm',
                'quantity' => 5,
                'category' => 'Tables',
                'images' => [
                    'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800',
                    'https://images.unsplash.com/photo-1532372320572-cda25653a26d?w=800',
                ],
                'variations' => ['Chêne naturel', 'Chêne foncé'],
            ],
            [
                'title' => 'Chaise design en noyer',
                'price' => 249.99,
                'description' => 'Chaise élégante en noyer massif avec assise en cuir. Confortable et robuste, cette chaise apportera une touche d\'élégance à votre salle à manger.',
                'reference' => 'CHA-NOYER-001',
                'materials' => 'Noyer massif, cuir véritable',
                'dimensions' => '45 x 50 x 95 cm',
                'quantity' => 12,
                'category' => 'Chaises',
                'images' => [
                    'https://images.unsplash.com/photo-1506439773649-6e0eb8cfb237?w=800',
                ],
                'variations' => ['Noyer'],
            ],
            [
                'title' => 'Armoire 3 portes en pin',
                'price' => 1299.99,
                'description' => 'Grande armoire 3 portes en pin massif avec étagères réglables. Parfaite pour ranger vos vêtements et accessoires.',
                'reference' => 'ARM-PIN-001',
                'materials' => 'Pin massif, poignées en laiton',
                'dimensions' => '180 x 60 x 220 cm',
                'quantity' => 3,
                'category' => 'Armoires',
                'images' => [
                    'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=800',
                ],
                'variations' => ['Pin'],
            ],
            [
                'title' => 'Étagère bibliothèque en hêtre',
                'price' => 449.99,
                'description' => 'Étagère bibliothèque modulaire en hêtre massif. Design épuré et fonctionnel, idéale pour exposer vos livres et objets déco.',
                'reference' => 'ETG-HETRE-001',
                'materials' => 'Hêtre massif',
                'dimensions' => '120 x 30 x 200 cm',
                'quantity' => 8,
                'category' => 'Étagères',
                'images' => [
                    'https://images.unsplash.com/photo-1594620302200-9a762244a181?w=800',
                ],
                'variations' => ['Hêtre'],
            ],
            [
                'title' => 'Lit double en châtaignier',
                'price' => 1599.99,
                'description' => 'Lit double avec tête de lit en châtaignier massif. Design sobre et élégant, garantissant un sommeil réparateur.',
                'reference' => 'LIT-CHATAIGN-001',
                'materials' => 'Châtaignier massif, lattes en hêtre',
                'dimensions' => '160 x 200 cm',
                'quantity' => 4,
                'category' => 'Lits',
                'images' => [
                    'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800',
                ],
                'variations' => ['Châtaignier'],
            ],
            [
                'title' => 'Commode 4 tiroirs en teck',
                'price' => 699.99,
                'description' => 'Commode élégante avec 4 tiroirs spacieux en teck massif. Parfaite pour votre chambre ou votre entrée.',
                'reference' => 'COM-TECK-001',
                'materials' => 'Teck massif, glissières métalliques',
                'dimensions' => '100 x 45 x 90 cm',
                'quantity' => 6,
                'category' => 'Commodes',
                'images' => [
                    'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=800',
                ],
                'variations' => ['Teck'],
            ],
            [
                'title' => 'Table basse en chêne avec variations',
                'price' => 549.99,
                'description' => 'Table basse moderne en chêne massif disponible en plusieurs tailles. Parfaite pour votre salon.',
                'reference' => 'TAB-BASSE-001',
                'materials' => 'Chêne massif',
                'dimensions' => 'Variable selon la taille',
                'quantity' => 0, // Quantité gérée par variations
                'category' => 'Tables',
                'images' => [
                    'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800',
                ],
                'variations' => [
                    ['type' => 'size', 'value' => 'Petit (80x40cm)', 'quantity' => 3],
                    ['type' => 'size', 'value' => 'Moyen (120x60cm)', 'quantity' => 5],
                    ['type' => 'size', 'value' => 'Grand (160x80cm)', 'quantity' => 2],
                ],
            ],
            [
                'title' => 'Chaise scandinave en hêtre',
                'price' => 179.99,
                'description' => 'Chaise scandinave design en hêtre massif. Style épuré et confortable, idéale pour votre cuisine ou salle à manger.',
                'reference' => 'CHA-SCAN-001',
                'materials' => 'Hêtre massif',
                'dimensions' => '42 x 48 x 88 cm',
                'quantity' => 15,
                'category' => 'Chaises',
                'images' => [
                    'https://images.unsplash.com/photo-1506439773649-6e0eb8cfb237?w=800',
                ],
                'variations' => ['Hêtre'],
            ],
        ];

        foreach ($products as $productData) {
            $category = $categories->where('name', $productData['category'])->first();
            
            $product = Product::updateOrCreate(
                ['reference' => $productData['reference']],
                [
                    'title' => $productData['title'],
                    'price' => $productData['price'],
                'description' => $productData['description'],
                'reference' => $productData['reference'],
                'quantity' => $productData['quantity'],
                'length' => 120,
                'width' => 80,
                'depth' => 40,
                'material_id' => \App\Models\Material::inRandomOrder()->first()->id ?? null,
            ]);

            // Attacher la catégorie
            if ($category) {
                $product->categories()->attach($category->id);
            }

            // Ajouter les images
            foreach ($productData['images'] as $index => $imagePath) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $imagePath,
                    'is_default' => $index === 0,
                    'order' => $index,
                ]);
            }

            // Ajouter les variations
            if (isset($productData['variations'])) {
                foreach ($productData['variations'] as $variationData) {
                    if (is_array($variationData)) {
                        // Variation avec quantité spécifique
                        $variation = Variation::where('type', $variationData['type'])
                            ->where('value', $variationData['value'])
                            ->first();
                        if ($variation) {
                            $product->variations()->attach($variation->id, [
                                'quantity' => $variationData['quantity'],
                            ]);
                        }
                    } else {
                        // Simple variation de couleur
                        $variation = $colorVariations->where('value', $variationData)->first();
                        if ($variation) {
                            $product->variations()->attach($variation->id, [
                                'quantity' => rand(2, 10),
                            ]);
                        }
                    }
                }
            }

            // Ajouter des produits suggérés (produits de la même catégorie)
            $suggestedProducts = Product::whereHas('categories', function ($q) use ($category) {
                $q->where('categories.id', $category->id);
            })->where('id', '!=', $product->id)->limit(3)->get();

            foreach ($suggestedProducts as $suggested) {
                $product->suggestedProducts()->attach($suggested->id);
            }
        }
    }
}
