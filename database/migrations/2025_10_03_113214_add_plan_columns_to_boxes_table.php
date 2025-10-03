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
        Schema::table('boxes', function (Blueprint $table) {
            $table->integer('plan_x')->nullable()->after('coordonnees_y');
            $table->integer('plan_y')->nullable()->after('plan_x');
            $table->integer('plan_width')->nullable()->after('plan_y');
            $table->integer('plan_height')->nullable()->after('plan_width');
        });

        // Ajouter table pour les images de fond
        Schema::create('plan_layouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emplacement_id')->constrained()->onDelete('cascade');
            $table->text('background_image')->nullable();
            $table->integer('canvas_width')->default(1200);
            $table->integer('canvas_height')->default(800);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boxes', function (Blueprint $table) {
            $table->dropColumn(['plan_x', 'plan_y', 'plan_width', 'plan_height']);
        });

        Schema::dropIfExists('plan_layouts');
    }
};
