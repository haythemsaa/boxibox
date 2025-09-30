<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrat_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrat_id')->constrained('contrats');
            $table->foreignId('service_id')->constrained('services');
            $table->decimal('quantite', 8, 2)->default(1);
            $table->decimal('prix_unitaire', 8, 2);
            $table->decimal('prix_total', 8, 2);
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['contrat_id', 'is_active']);
            $table->index('service_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrat_services');
    }
};