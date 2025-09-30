<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('box_familles', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->decimal('surface_min', 8, 2)->nullable();
            $table->decimal('surface_max', 8, 2)->nullable();
            $table->decimal('prix_base', 8, 2);
            $table->string('couleur_plan', 7)->default('#3498db');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('box_familles');
    }
};