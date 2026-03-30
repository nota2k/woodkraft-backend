<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crée les utilisateurs admin (admin: root ; Webmaster: admin123)';

    /**
     * Execute the console command.
     */
    public function handle()
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
            $this->info('✅ Utilisateur admin créé avec succès !');
            $this->info('   Email: admin');
            $this->info('   Mot de passe: root');
        } else {
            $admin->password = Hash::make('root');
            $admin->save();
            $this->info('✅ Mot de passe de l\'utilisateur admin mis à jour !');
        }

        // Créer l'utilisateur webmaster permanent
        $webmaster = User::where('email', 'webmaster.babillon@gmail.com')->first();
        
        if (!$webmaster) {
            User::create([
                'name' => 'Webmaster',
                'email' => 'webmaster.babillon@gmail.com',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]);
            $this->info('✅ Utilisateur webmaster créé avec succès !');
            $this->info('   Email: webmaster.babillon@gmail.com');
            $this->info('   Mot de passe: admin123');
        } else {
            $webmaster->password = Hash::make('admin123');
            $webmaster->save();
            $this->info('✅ Mot de passe de l\'utilisateur webmaster mis à jour !');
        }
    }
}
