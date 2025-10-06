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
        Schema::create('access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('access_code_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('box_id')->nullable()->constrained()->onDelete('set null');

            // Détails de l'accès
            $table->enum('type_acces', ['entree', 'sortie'])->default('entree');
            $table->enum('methode', ['pin', 'qr', 'badge', 'manuel', 'maintenance'])->default('pin');
            $table->enum('statut', ['autorise', 'refuse', 'erreur'])->default('autorise');

            // Date et heure
            $table->timestamp('date_heure')->useCurrent();

            // Informations complémentaires
            $table->string('code_utilise')->nullable(); // Code PIN ou QR utilisé
            $table->string('terminal_id')->nullable(); // Identifiant du terminal/lecteur
            $table->string('emplacement')->nullable(); // Localisation précise
            $table->string('ip_address', 45)->nullable(); // IPv4 ou IPv6
            $table->text('user_agent')->nullable(); // Si accès via web

            // Raison du refus si applicable
            $table->string('raison_refus')->nullable(); // Ex: "Code expiré", "Hors horaire", etc.

            // Métadonnées
            $table->json('metadata')->nullable(); // Données additionnelles (photos, biométrie future, etc.)
            $table->text('notes')->nullable();

            $table->timestamps();

            // Index pour recherches rapides
            $table->index(['client_id', 'date_heure']);
            $table->index(['box_id', 'date_heure']);
            $table->index(['access_code_id', 'date_heure']);
            $table->index(['type_acces', 'statut']);
            $table->index('date_heure');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_logs');
    }
};
