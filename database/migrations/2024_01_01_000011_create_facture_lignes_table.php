<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facture_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_id')->constrained('factures')->onDelete('cascade');
            $table->string('designation');
            $table->decimal('quantite', 8, 2)->default(1);
            $table->decimal('prix_unitaire', 10, 2);
            $table->decimal('taux_tva', 5, 2)->default(20.00);
            $table->decimal('montant_ht', 10, 2);
            $table->decimal('montant_tva', 10, 2);
            $table->decimal('montant_ttc', 10, 2);
            $table->timestamps();

            $table->index('facture_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facture_lignes');
    }
};