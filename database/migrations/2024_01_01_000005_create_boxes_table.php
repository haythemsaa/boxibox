<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('famille_id')->constrained('box_familles');
            $table->foreignId('emplacement_id')->constrained('emplacements');
            $table->string('numero')->unique();
            $table->decimal('surface', 8, 2);
            $table->decimal('volume', 8, 2);
            $table->decimal('prix_mensuel', 8, 2);
            $table->enum('statut', ['libre', 'occupe', 'reserve', 'maintenance', 'hors_service'])->default('libre');
            $table->integer('coordonnees_x')->nullable();
            $table->integer('coordonnees_y')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['statut', 'is_active']);
            $table->index(['famille_id', 'emplacement_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boxes');
    }
};