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
        Schema::create('rappels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('facture_id')->constrained('factures')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->integer('niveau')->default(1); // 1 = 1ère relance, 2 = 2ème, 3 = mise en demeure
            $table->enum('mode_envoi', ['email', 'courrier', 'sms'])->default('email');
            $table->date('date_rappel');
            $table->enum('statut', ['en_attente', 'envoye', 'regle'])->default('en_attente');
            $table->decimal('montant_du', 10, 2);
            $table->string('document_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Index pour performances
            $table->index(['tenant_id', 'client_id']);
            $table->index(['facture_id', 'statut']);
            $table->index('date_rappel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rappels');
    }
};
