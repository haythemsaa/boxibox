<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add tenant_id and type_user to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->onDelete('cascade');
            $table->enum('type_user', ['superadmin', 'admin_tenant', 'client_final'])->default('admin_tenant')->after('tenant_id');
            $table->foreignId('client_id')->nullable()->after('type_user')->constrained('clients')->onDelete('cascade');

            $table->index(['tenant_id', 'type_user']);
        });

        // Add tenant_id to all relevant tables
        $tables = [
            'clients',
            'boxes',
            'box_familles',
            'emplacements',
            'contrats',
            'factures',
            'reglements',
            'mandats_sepa',
            'prelevements_sepa',
            'documents',
            'interventions',
            'rappels',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->onDelete('cascade');
                    $table->index('tenant_id');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove from users
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropForeign(['client_id']);
            $table->dropColumn(['tenant_id', 'type_user', 'client_id']);
        });

        // Remove from other tables
        $tables = [
            'clients',
            'boxes',
            'box_familles',
            'emplacements',
            'contrats',
            'factures',
            'reglements',
            'mandats_sepa',
            'prelevements_sepa',
            'documents',
            'interventions',
            'rappels',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropForeign(['tenant_id']);
                    $table->dropColumn('tenant_id');
                });
            }
        }
    }
};
