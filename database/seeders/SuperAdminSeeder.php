<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer le SuperAdmin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@boxibox.com'],
            [
                'name' => 'Super Administrateur',
                'password' => Hash::make('password'),
                'type_user' => 'superadmin',
                'is_active' => true,
                'tenant_id' => null,
                'client_id' => null,
            ]
        );

        $this->command->info('✓ SuperAdmin créé avec succès');
        $this->command->info('  Email: superadmin@boxibox.com');
        $this->command->info('  Password: password');

        // Créer un tenant de démonstration
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'demo-entreprise'],
            [
                'nom_entreprise' => 'Entreprise Démo',
                'email' => 'contact@demo-entreprise.com',
                'telephone' => '0123456789',
                'adresse' => '123 Rue de la Démo',
                'code_postal' => '75001',
                'ville' => 'Paris',
                'pays' => 'France',
                'siret' => '12345678901234',
                'plan' => 'business',
                'prix_mensuel' => 99.00,
                'max_boxes' => 50,
                'max_users' => 5,
                'date_debut_abonnement' => now(),
                'date_fin_abonnement' => now()->addYear(),
                'statut_abonnement' => 'actif',
                'is_active' => true,
            ]
        );

        $this->command->info('✓ Tenant de démonstration créé');
        $this->command->info('  Entreprise: ' . $tenant->nom_entreprise);
        $this->command->info('  Slug: ' . $tenant->slug);

        // Créer un admin pour ce tenant
        $adminTenant = User::firstOrCreate(
            ['email' => 'admin@demo-entreprise.com'],
            [
                'name' => 'Administrateur Démo',
                'password' => Hash::make('password'),
                'type_user' => 'admin_tenant',
                'tenant_id' => $tenant->id,
                'is_active' => true,
            ]
        );

        $this->command->info('✓ Administrateur tenant créé');
        $this->command->info('  Email: admin@demo-entreprise.com');
        $this->command->info('  Password: password');

        $this->command->info('');
        $this->command->info('=================================');
        $this->command->info('Seeding terminé avec succès !');
        $this->command->info('=================================');
    }
}