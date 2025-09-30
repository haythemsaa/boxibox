<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('contrat_id')->nullable()->constrained('contrats');
            $table->string('numero_facture')->unique();
            $table->enum('type_facture', ['loyer', 'caution', 'frais_dossier', 'services', 'penalite', 'regularisation']);
            $table->date('date_emission');
            $table->date('date_echeance');
            $table->decimal('montant_ht', 10, 2);
            $table->decimal('taux_tva', 5, 2)->default(20.00);
            $table->decimal('montant_tva', 10, 2);
            $table->decimal('montant_ttc', 10, 2);
            $table->enum('statut', ['brouillon', 'emise', 'envoyee', 'payee', 'en_retard', 'annulee'])->default('brouillon');
            $table->text('notes')->nullable();
            $table->date('date_paiement')->nullable();
            $table->enum('mode_paiement', ['especes', 'cheque', 'virement', 'cb', 'prelevement', 'autre'])->nullable();
            $table->string('reference_paiement')->nullable();
            $table->timestamps();

            $table->index(['statut', 'date_echeance']);
            $table->index(['client_id', 'statut']);
            $table->index('contrat_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};