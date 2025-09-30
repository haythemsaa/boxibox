<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->nullable()->constrained('prospects');
            $table->enum('type_client', ['particulier', 'entreprise'])->default('particulier');
            $table->string('nom');
            $table->string('prenom')->nullable();
            $table->string('raison_sociale')->nullable();
            $table->string('email')->unique();
            $table->string('telephone')->nullable();
            $table->string('telephone_urgence')->nullable();
            $table->text('adresse');
            $table->string('code_postal', 10);
            $table->string('ville');
            $table->string('pays')->default('France');
            $table->string('siret', 20)->nullable();
            $table->date('date_naissance')->nullable();
            $table->enum('piece_identite_type', ['cni', 'passeport', 'permis', 'autre'])->nullable();
            $table->string('piece_identite_numero')->nullable();
            $table->string('contact_urgence_nom')->nullable();
            $table->string('contact_urgence_telephone')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type_client', 'is_active']);
            $table->index('prospect_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};