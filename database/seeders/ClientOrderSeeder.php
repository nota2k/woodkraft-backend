<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class ClientOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('fr_FR');
        
        // Récupérer tous les produits disponibles
        $products = Product::all();
        
        if ($products->isEmpty()) {
            $this->command->warn('Aucun produit trouvé. Veuillez d\'abord exécuter le ProductSeeder.');
            return;
        }

        // Générer 20 clients
        $this->command->info('Génération de 20 clients...');
        $users = [];
        
        for ($i = 0; $i < 20; $i++) {
            $users[] = User::create([
                'name' => $faker->name(),
                'email' => $faker->unique()->safeEmail(),
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
        }

        $this->command->info('Génération de commandes pour chaque client...');
        
        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        // Pour chaque client, générer 1 à 5 commandes
        foreach ($users as $user) {
            $numberOfOrders = $faker->numberBetween(1, 5);
            
            for ($j = 0; $j < $numberOfOrders; $j++) {
                // Générer une adresse
                $address = $faker->streetAddress() . ', ' . $faker->postcode() . ' ' . $faker->city();
                
                // Créer la commande
                $order = Order::create([
                    'user_id' => $user->id,
                    'order_number' => 'ORD-' . strtoupper($faker->unique()->bothify('####-####')),
                    'status' => $faker->randomElement($statuses),
                    'total_amount' => 0, // Sera calculé après
                    'shipping_address' => $address,
                    'billing_address' => $address,
                    'customer_name' => $user->name,
                    'customer_email' => $user->email,
                    'customer_phone' => $faker->phoneNumber(),
                    'notes' => $faker->optional(0.3)->sentence(),
                    'created_at' => $faker->dateTimeBetween('-6 months', 'now'),
                    'updated_at' => now(),
                ]);

                // Ajouter 1 à 4 produits à la commande
                $numberOfItems = $faker->numberBetween(1, 4);
                $selectedProducts = $products->random(min($numberOfItems, $products->count()));
                $totalAmount = 0;

                foreach ($selectedProducts as $product) {
                    $quantity = $faker->numberBetween(1, 3);
                    $unitPrice = $product->price;
                    $totalPrice = $unitPrice * $quantity;
                    $totalAmount += $totalPrice;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                    ]);
                }

                // Mettre à jour le montant total de la commande
                $order->update(['total_amount' => $totalAmount]);
            }
        }

        $this->command->info('✅ ' . count($users) . ' clients créés avec leurs commandes !');
        $this->command->info('✅ Total de commandes créées : ' . Order::count());
    }
}
