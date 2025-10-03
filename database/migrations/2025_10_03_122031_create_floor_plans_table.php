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
        Schema::create('floor_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emplacement_id')->constrained()->onDelete('cascade');
            $table->string('nom')->nullable();
            $table->text('path_data'); // Données du tracé SVG
            $table->integer('canvas_width')->default(1200);
            $table->integer('canvas_height')->default(800);
            $table->decimal('echelle_metres_par_pixel', 8, 4)->default(0.05); // 1 pixel = 5cm par défaut
            $table->json('metadata')->nullable(); // Couleurs, épaisseurs, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('floor_plans');
    }
};
