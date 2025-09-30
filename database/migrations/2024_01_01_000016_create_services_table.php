<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->enum('type_service', ['assurance', 'materiel', 'manutention', 'nettoyage', 'securite', 'autre']);
            $table->decimal('prix', 8, 2);
            $table->string('unite')->nullable(); // mois, pièce, heure, etc.
            $table->boolean('facturable')->default(true);
            $table->boolean('obligatoire')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type_service', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};