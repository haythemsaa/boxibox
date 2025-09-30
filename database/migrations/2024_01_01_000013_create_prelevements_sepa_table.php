<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prelevements_sepa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mandat_sepa_id')->constrained('mandats_sepa');
            $table->foreignId('facture_id')->constrained('factures');
            $table->decimal('montant', 10, 2);
            $table->date('date_prelevement');
            $table->date('date_valeur');
            $table->enum('statut', ['en_attente', 'envoye', 'reussi', 'rejete'])->default('en_attente');
            $table->string('reference_end_to_end')->unique();
            $table->string('fichier_sepa')->nullable();
            $table->string('code_retour')->nullable();
            $table->string('libelle_retour')->nullable();
            $table->date('date_retour')->nullable();
            $table->timestamps();

            $table->index(['mandat_sepa_id', 'statut']);
            $table->index(['date_prelevement', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prelevements_sepa');
    }
};