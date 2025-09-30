<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('relances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_id')->constrained('factures');
            $table->foreignId('client_id')->constrained('clients');
            $table->enum('type_relance', ['amiable', 'mise_en_demeure', 'juridique']);
            $table->integer('niveau')->default(1);
            $table->date('date_envoi')->nullable();
            $table->date('date_prevue');
            $table->enum('statut', ['en_attente', 'envoyee', 'annulee'])->default('en_attente');
            $table->decimal('montant_penalite', 10, 2)->default(0);
            $table->text('contenu');
            $table->enum('mode_envoi', ['email', 'courrier', 'sms', 'fax'])->default('email');
            $table->text('adresse_envoi')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['facture_id', 'statut']);
            $table->index(['client_id', 'date_envoi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relances');
    }
};