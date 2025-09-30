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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('nom_entreprise');
            $table->string('slug')->unique();
            $table->string('email')->unique();
            $table->string('telephone')->nullable();
            $table->text('adresse')->nullable();
            $table->string('code_postal')->nullable();
            $table->string('ville')->nullable();
            $table->string('pays')->default('France');
            $table->string('siret')->nullable();

            // Abonnement
            $table->enum('plan', ['gratuit', 'starter', 'business', 'enterprise'])->default('gratuit');
            $table->decimal('prix_mensuel', 10, 2)->default(0);
            $table->integer('max_boxes')->default(10);
            $table->integer('max_users')->default(2);
            $table->date('date_debut_abonnement')->nullable();
            $table->date('date_fin_abonnement')->nullable();
            $table->enum('statut_abonnement', ['actif', 'suspendu', 'expire', 'annule'])->default('actif');

            // Configuration
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
