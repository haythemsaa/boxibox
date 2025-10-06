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
        Schema::create('access_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('box_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('contrat_id')->nullable()->constrained()->onDelete('set null');

            // Code d'accès
            $table->string('code_pin', 6); // Code PIN à 6 chiffres
            $table->string('qr_code_data')->unique()->nullable(); // Données QR code
            $table->string('qr_code_path')->nullable(); // Chemin fichier QR code

            // Type et statut
            $table->enum('type', ['pin', 'qr', 'badge'])->default('pin');
            $table->enum('statut', ['actif', 'expire', 'suspendu', 'revoque'])->default('actif');

            // Validité
            $table->timestamp('date_debut')->nullable();
            $table->timestamp('date_fin')->nullable();
            $table->boolean('temporaire')->default(false);

            // Restrictions
            $table->json('jours_autorises')->nullable(); // ['lundi', 'mardi', ...]
            $table->time('heure_debut')->nullable(); // Ex: 08:00
            $table->time('heure_fin')->nullable(); // Ex: 20:00
            $table->integer('max_utilisations')->nullable(); // Limite utilisation
            $table->integer('nb_utilisations')->default(0); // Compteur

            // Métadonnées
            $table->text('notes')->nullable();
            $table->timestamp('derniere_utilisation')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index(['client_id', 'statut']);
            $table->index(['box_id', 'statut']);
            $table->index('code_pin');
            $table->index('qr_code_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_codes');
    }
};
