<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Créer un utilisateur admin par défaut
        $admin = User::create([
            'name' => 'Administrateur',
            'email' => 'admin@boxibox.com',
            'password' => Hash::make('password123'),
            'phone' => '+33 1 23 45 67 89',
            'is_active' => true,
            'email_verified_at' => now(),
            'last_login_at' => now(),
        ]);

        $admin->assignRole('Admin');

        // Créer un utilisateur commercial
        $commercial = User::create([
            'name' => 'Jean Dupont',
            'email' => 'commercial@boxibox.com',
            'password' => Hash::make('password123'),
            'phone' => '+33 1 23 45 67 90',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $commercial->assignRole('Commercial');

        // Créer un utilisateur gestionnaire
        $gestionnaire = User::create([
            'name' => 'Marie Martin',
            'email' => 'gestionnaire@boxibox.com',
            'password' => Hash::make('password123'),
            'phone' => '+33 1 23 45 67 91',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $gestionnaire->assignRole('Gestionnaire');

        // Créer un utilisateur technicien
        $technicien = User::create([
            'name' => 'Pierre Durand',
            'email' => 'technicien@boxibox.com',
            'password' => Hash::make('password123'),
            'phone' => '+33 1 23 45 67 92',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $technicien->assignRole('Technicien');

        $this->command->info('Utilisateurs créés avec succès !');
        $this->command->info('Admin: admin@boxibox.com / password123');
        $this->command->info('Commercial: commercial@boxibox.com / password123');
        $this->command->info('Gestionnaire: gestionnaire@boxibox.com / password123');
        $this->command->info('Technicien: technicien@boxibox.com / password123');
    }
}