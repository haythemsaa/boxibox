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
        Schema::create('client_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->string('type'); // facture, paiement, relance, contrat, document, systeme, message
            $table->string('titre');
            $table->text('message');
            $table->string('lien')->nullable();
            $table->boolean('lu')->default(false);
            $table->timestamps();

            $table->index(['client_id', 'lu', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_notifications');
    }
};
