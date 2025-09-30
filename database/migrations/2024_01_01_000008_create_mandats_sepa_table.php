<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mandats_sepa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('contrat_id')->nullable()->constrained('contrats');
            $table->string('rum')->unique(); // Référence Unique de Mandat
            $table->enum('type_mandat', ['core', 'b2b'])->default('core');
            $table->enum('statut', ['attente_signature', 'valide', 'annule', 'expire'])->default('attente_signature');
            $table->date('date_signature')->nullable();
            $table->date('date_premiere_utilisation')->nullable();
            $table->date('date_derniere_utilisation')->nullable();
            $table->string('iban', 34);
            $table->string('bic', 11);
            $table->string('titulaire_compte');
            $table->text('adresse_debiteur');
            $table->boolean('is_active')->default(true);
            $table->string('motif_inactivation')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'is_active']);
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mandats_sepa');
    }
};