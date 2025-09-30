<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospect_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained('prospects');
            $table->enum('type_interaction', ['appel_entrant', 'appel_sortant', 'email', 'visite', 'courrier', 'sms', 'autre']);
            $table->datetime('date_interaction');
            $table->integer('duree_minutes')->nullable();
            $table->text('contenu');
            $table->enum('resultat', ['interesse', 'pas_interesse', 'demande_rappel', 'rdv_pris', 'sans_reponse', 'autre']);
            $table->date('date_relance_prevue')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['prospect_id', 'date_interaction']);
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospect_interactions');
    }
};