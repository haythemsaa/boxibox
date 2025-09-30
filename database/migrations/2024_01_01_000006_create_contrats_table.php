<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('box_id')->constrained('boxes');
            $table->string('numero_contrat')->unique();
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->enum('duree_type', ['determine', 'indetermine'])->default('indetermine');
            $table->decimal('prix_mensuel', 8, 2);
            $table->decimal('caution', 8, 2);
            $table->decimal('frais_dossier', 8, 2)->default(0);
            $table->enum('periodicite_facturation', ['mensuelle', 'trimestrielle', 'semestrielle', 'annuelle'])->default('mensuelle');
            $table->enum('statut', ['en_cours', 'actif', 'termine', 'resilie', 'litige'])->default('en_cours');
            $table->text('notes')->nullable();
            $table->date('date_signature')->nullable();
            $table->boolean('renouvellement_automatique')->default(true);
            $table->integer('preavis_jours')->default(30);
            $table->boolean('assurance_incluse')->default(false);
            $table->decimal('montant_assurance', 8, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['statut', 'date_debut']);
            $table->index(['client_id', 'statut']);
            $table->index('box_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrats');
    }
};