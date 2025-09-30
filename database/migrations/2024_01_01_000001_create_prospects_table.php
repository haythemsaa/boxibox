<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospects', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom');
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->text('adresse')->nullable();
            $table->string('code_postal', 10)->nullable();
            $table->string('ville')->nullable();
            $table->enum('origine', ['site_web', 'telephone', 'visite', 'recommandation', 'publicite', 'autre']);
            $table->enum('statut', ['nouveau', 'contacte', 'interesse', 'perdu'])->default('nouveau');
            $table->text('notes')->nullable();
            $table->date('date_contact')->nullable();
            $table->date('date_relance')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->timestamp('converted_to_client_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['statut', 'date_contact']);
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospects');
    }
};