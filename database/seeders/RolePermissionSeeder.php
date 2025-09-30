<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Permissions
        $permissions = [
            // Dashboard
            'view_dashboard',

            // Prospects
            'view_prospects',
            'create_prospects',
            'edit_prospects',
            'delete_prospects',
            'convert_prospects',

            // Clients
            'view_clients',
            'create_clients',
            'edit_clients',
            'delete_clients',

            // Contrats
            'view_contrats',
            'create_contrats',
            'edit_contrats',
            'delete_contrats',
            'sign_contrats',

            // Factures
            'view_factures',
            'create_factures',
            'edit_factures',
            'delete_factures',
            'send_factures',

            // Règlements
            'view_reglements',
            'create_reglements',
            'edit_reglements',
            'delete_reglements',

            // SEPA
            'view_sepa',
            'create_sepa_mandats',
            'generate_sepa_files',
            'process_sepa_returns',

            // Boxes
            'view_boxes',
            'create_boxes',
            'edit_boxes',
            'delete_boxes',
            'manage_box_plan',

            // Relances
            'view_relances',
            'create_relances',
            'send_relances',

            // Administration
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'view_statistics',
            'manage_settings',

            // Exports
            'export_data',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Rôles
        $adminRole = Role::firstOrCreate(['name' => 'administrateur']);
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $employeeRole = Role::firstOrCreate(['name' => 'employe']);
        $readOnlyRole = Role::firstOrCreate(['name' => 'lecture_seule']);

        // Attribution des permissions

        // Administrateur : Tous les droits
        $adminRole->givePermissionTo(Permission::all());

        // Manager : Tous les droits sauf gestion des utilisateurs et paramètres système
        $managerPermissions = Permission::whereNotIn('name', [
            'create_users',
            'edit_users',
            'delete_users',
            'manage_settings'
        ])->get();
        $managerRole->givePermissionTo($managerPermissions);

        // Employé : Droits opérationnels
        $employeePermissions = [
            'view_dashboard',
            'view_prospects', 'create_prospects', 'edit_prospects',
            'view_clients', 'create_clients', 'edit_clients',
            'view_contrats', 'create_contrats', 'edit_contrats',
            'view_factures', 'create_factures', 'edit_factures', 'send_factures',
            'view_reglements', 'create_reglements', 'edit_reglements',
            'view_boxes', 'edit_boxes',
            'view_relances', 'create_relances', 'send_relances',
        ];
        $employeeRole->givePermissionTo($employeePermissions);

        // Lecture seule : Consultation uniquement
        $readOnlyPermissions = [
            'view_dashboard',
            'view_prospects',
            'view_clients',
            'view_contrats',
            'view_factures',
            'view_reglements',
            'view_boxes',
            'view_relances',
        ];
        $readOnlyRole->givePermissionTo($readOnlyPermissions);

        // Créer un utilisateur administrateur par défaut
        $admin = User::firstOrCreate([
            'email' => 'admin@boxibox.com'
        ], [
            'name' => 'Administrateur Boxibox',
            'password' => bcrypt('admin123'),
            'is_active' => true,
        ]);

        $admin->assignRole('administrateur');
    }
}