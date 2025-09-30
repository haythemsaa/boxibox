<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reglements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_id')->constrained('factures');
            $table->foreignId('client_id')->constrained('clients');
            $table->decimal('montant', 10, 2);
            $table->enum('mode_paiement', ['especes', 'cheque', 'virement', 'cb', 'prelevement', 'autre']);
            $table->date('date_reglement');
            $table->string('reference')->nullable();
            $table->enum('statut', ['en_attente', 'valide', 'rejete'])->default('valide');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['facture_id', 'statut']);
            $table->index(['client_id', 'date_reglement']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reglements');
    }
};