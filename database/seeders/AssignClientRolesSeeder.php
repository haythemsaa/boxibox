<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AssignClientRolesSeeder extends Seeder
{
    public function run(): void
    {
        // Créer le rôle Client s'il n'existe pas
        $clientRole = Role::firstOrCreate(['name' => 'Client', 'guard_name' => 'web']);

        // Créer les permissions pour les clients
        $permissions = [
            'view_own_profile',
            'edit_own_profile',
            'view_own_contracts',
            'view_own_invoices',
            'view_own_payments',
            'view_own_documents',
            'upload_documents',
            'manage_own_sepa',
            'view_own_reminders',
            'view_own_timeline',
        ];

        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
            $clientRole->givePermissionTo($permission);
        }

        // Assigner le rôle à tous les utilisateurs client_final
        $clients = User::where('type_user', 'client_final')->get();

        foreach ($clients as $client) {
            if (!$client->hasRole('Client')) {
                $client->assignRole('Client');
                $this->command->info("✅ Rôle 'Client' assigné à {$client->email}");
            }
        }

        $this->command->info("✅ {$clients->count()} clients ont le rôle 'Client'");
    }
}
