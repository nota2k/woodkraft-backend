<?php

namespace Database\Seeders;

use App\Models\PromoCode;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OrderTestEnvironmentSeeder extends Seeder
{
    /**
     * Seed a deterministic checkout/order test environment.
     */
    public function run(): void
    {
        $this->seedTestUsers();
        $this->seedShippingMethods();
        $this->seedPromoCodes();

        $this->command?->info('✅ Environnement de test commande prêt.');
        $this->command?->info('   Client: client@test.local / password');
        $this->command?->info('   Admin:  admin / root');
    }

    private function seedTestUsers(): void
    {
        User::updateOrCreate(
            ['email' => 'client@test.local'],
            [
                'name' => 'Client Test',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'email_verified_at' => now(),
            ]
        );
    }

    private function seedShippingMethods(): void
    {
        $shippingMethods = [
            [
                'code' => 'STANDARD',
                'name' => 'Colissimo',
                'description' => 'Livraison standard à domicile.',
                'price' => 12.00,
                'free_from_amount' => 40.00,
                'is_active' => true,
                'position' => 1,
            ],
            [
                'code' => 'RELAY',
                'name' => 'Mondial Relay',
                'description' => 'Livraison en point relais.',
                'price' => 8.00,
                'free_from_amount' => 40.00,
                'is_active' => true,
                'position' => 2,
            ],
            [
                'code' => 'EXPRESS24',
                'name' => 'Chronopost 24h',
                'description' => 'Livraison express sous 24h.',
                'price' => 19.00,
                'free_from_amount' => null,
                'is_active' => true,
                'position' => 3,
            ],
        ];

        foreach ($shippingMethods as $shippingMethod) {
            ShippingMethod::updateOrCreate(
                ['code' => $shippingMethod['code']],
                $shippingMethod
            );
        }
    }

    private function seedPromoCodes(): void
    {
        $promoCodes = [
            [
                'code' => 'BIENVENUE10',
                'name' => 'Code de bienvenue',
                'description' => '10% de remise dès 40 EUR d\'achat.',
                'discount_type' => 'percent',
                'discount_value' => 10.00,
                'minimum_amount' => 40.00,
                'maximum_discount_amount' => 80.00,
                'usage_limit' => null,
                'used_count' => 0,
                'starts_at' => null,
                'ends_at' => null,
                'is_active' => true,
            ],
            [
                'code' => 'LIVRAISON5',
                'name' => 'Remise fixe',
                'description' => '5 EUR de remise sans minimum.',
                'discount_type' => 'fixed',
                'discount_value' => 5.00,
                'minimum_amount' => null,
                'maximum_discount_amount' => null,
                'usage_limit' => null,
                'used_count' => 0,
                'starts_at' => null,
                'ends_at' => null,
                'is_active' => true,
            ],
            [
                'code' => 'EXPIRE',
                'name' => 'Code expiré',
                'description' => 'Code invalide pour tester le message d\'erreur.',
                'discount_type' => 'percent',
                'discount_value' => 15.00,
                'minimum_amount' => null,
                'maximum_discount_amount' => null,
                'usage_limit' => null,
                'used_count' => 0,
                'starts_at' => now()->subMonths(2),
                'ends_at' => now()->subMonth(),
                'is_active' => true,
            ],
        ];

        foreach ($promoCodes as $promoCode) {
            PromoCode::updateOrCreate(
                ['code' => $promoCode['code']],
                $promoCode
            );
        }
    }
}
