<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer l'utilisateur admin
        $admin = User::where('email', 'admin')->first();
        
        if (!$admin) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin',
                'password' => Hash::make('root'),
                'email_verified_at' => now(),
            ]);
            $this->command->info('✅ Utilisateur admin créé avec succès !');
            $this->command->info('   Email: admin');
            $this->command->info('   Mot de passe: root');
        } else {
            // Mettre à jour le mot de passe si l'utilisateur existe déjà
            $admin->password = Hash::make('root');
            $admin->save();
            $this->command->info('✅ Mot de passe de l\'utilisateur admin mis à jour !');
        }

        // Créer l'utilisateur webmaster permanent
        $webmaster = User::where('email', 'webmaster.babillon@gmail.com')->first();
        
        if (!$webmaster) {
            User::create([
                'name' => 'Webmaster',
                'email' => 'webmaster.babillon@gmail.com',
                'password' => Hash::make('root'),
                'email_verified_at' => now(),
            ]);
            $this->command->info('✅ Utilisateur webmaster créé avec succès !');
            $this->command->info('   Email: webmaster.babillon@gmail.com');
            $this->command->info('   Mot de passe: root');
        } else {
            // Mettre à jour le mot de passe si l'utilisateur existe déjà
            $webmaster->password = Hash::make('root');
            $webmaster->save();
            $this->command->info('✅ Mot de passe de l\'utilisateur webmaster mis à jour !');
        }
    }
}
