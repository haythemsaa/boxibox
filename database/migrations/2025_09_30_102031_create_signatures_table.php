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
        Schema::create('signatures', function (Blueprint $table) {
            $table->id();
            $table->string('signable_type'); // Contrat ou MandatSepa
            $table->unsignedBigInteger('signable_id');
            $table->unsignedBigInteger('client_id');
            $table->string('signataire_nom');
            $table->string('signataire_email');
            $table->string('signataire_telephone')->nullable();
            $table->enum('statut', ['en_attente', 'signe', 'refuse', 'expire'])->default('en_attente');
            $table->text('signature_data')->nullable(); // Données de signature (base64 ou certificat)
            $table->string('signature_ip')->nullable();
            $table->string('signature_method')->nullable(); // simple, qualified, advanced
            $table->timestamp('date_envoi')->nullable();
            $table->timestamp('date_signature')->nullable();
            $table->timestamp('date_expiration')->nullable();
            $table->text('token')->nullable(); // Token unique pour le lien de signature
            $table->text('certificat_path')->nullable(); // Chemin vers le certificat eIDAS si applicable
            $table->text('notes')->nullable();
            $table->timestamps();

            // Index
            $table->index(['signable_type', 'signable_id']);
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signatures');
    }
};
