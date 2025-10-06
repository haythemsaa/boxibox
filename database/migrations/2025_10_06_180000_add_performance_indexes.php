<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajoute des indexes pour améliorer les performances des requêtes fréquentes
     */
    public function up(): void
    {
        // Index sur la table clients
        Schema::table('clients', function (Blueprint $table) {
            // Index pour recherches par email
            if (!$this->indexExists('clients', 'clients_email_index')) {
                $table->index('email');
            }
            // Index pour filtres par statut
            if (!$this->indexExists('clients', 'clients_is_active_index')) {
                $table->index('is_active');
            }
            // Index composite pour dates de création
            if (!$this->indexExists('clients', 'clients_created_at_index')) {
                $table->index('created_at');
            }
        });

        // Index sur la table contrats
        Schema::table('contrats', function (Blueprint $table) {
            // Index pour filtres par statut
            if (!$this->indexExists('contrats', 'contrats_statut_index')) {
                $table->index('statut');
            }
            // Index pour dates de fin (renouvellements)
            if (!$this->indexExists('contrats', 'contrats_date_fin_index')) {
                $table->index('date_fin');
            }
            // Index composite pour dashboard
            if (!$this->indexExists('contrats', 'contrats_statut_created_at_index')) {
                $table->index(['statut', 'created_at']);
            }
        });

        // Index sur la table factures
        Schema::table('factures', function (Blueprint $table) {
            // Index pour filtres par statut
            if (!$this->indexExists('factures', 'factures_statut_index')) {
                $table->index('statut');
            }
            // Index pour dates d'échéance (impayés)
            if (!$this->indexExists('factures', 'factures_date_echeance_index')) {
                $table->index('date_echeance');
            }
            // Index composite pour calculs CA
            if (!$this->indexExists('factures', 'factures_statut_created_at_index')) {
                $table->index(['statut', 'created_at']);
            }
        });

        // Index sur la table reglements
        Schema::table('reglements', function (Blueprint $table) {
            // Index pour dates de règlement (statistiques)
            if (!$this->indexExists('reglements', 'reglements_date_reglement_index')) {
                $table->index('date_reglement');
            }
            // Index composite pour dashboard CA
            if (!$this->indexExists('reglements', 'reglements_date_reglement_montant_index')) {
                $table->index(['date_reglement', 'montant']);
            }
        });

        // Index sur la table boxes
        Schema::table('boxes', function (Blueprint $table) {
            // Index pour filtres par statut
            if (!$this->indexExists('boxes', 'boxes_statut_index')) {
                $table->index('statut');
            }
            // Index pour famille (statistiques)
            if (!$this->indexExists('boxes', 'boxes_famille_id_index')) {
                $table->index('famille_id');
            }
            // Index pour actif
            if (!$this->indexExists('boxes', 'boxes_is_active_index')) {
                $table->index('is_active');
            }
        });

        // Index sur la table prospects
        Schema::table('prospects', function (Blueprint $table) {
            // Index pour filtres par statut
            if (!$this->indexExists('prospects', 'prospects_statut_index')) {
                $table->index('statut');
            }
            // Index pour dates de création
            if (!$this->indexExists('prospects', 'prospects_created_at_index')) {
                $table->index('created_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('contrats', function (Blueprint $table) {
            $table->dropIndex(['statut']);
            $table->dropIndex(['date_fin']);
            $table->dropIndex(['statut', 'created_at']);
        });

        Schema::table('factures', function (Blueprint $table) {
            $table->dropIndex(['statut']);
            $table->dropIndex(['date_echeance']);
            $table->dropIndex(['statut', 'created_at']);
        });

        Schema::table('reglements', function (Blueprint $table) {
            $table->dropIndex(['date_reglement']);
            $table->dropIndex(['date_reglement', 'montant']);
        });

        Schema::table('boxes', function (Blueprint $table) {
            $table->dropIndex(['statut']);
            $table->dropIndex(['famille_id']);
            $table->dropIndex(['is_active']);
        });

        Schema::table('prospects', function (Blueprint $table) {
            $table->dropIndex(['statut']);
            $table->dropIndex(['created_at']);
        });
    }

    /**
     * Vérifie si un index existe
     */
    private function indexExists(string $table, string $index): bool
    {
        $conn = Schema::getConnection();
        $dbName = $conn->getDatabaseName();

        $result = $conn->select(
            "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.STATISTICS
             WHERE table_schema = ? AND table_name = ? AND index_name = ?",
            [$dbName, $table, $index]
        );

        return $result[0]->count > 0;
    }
};
