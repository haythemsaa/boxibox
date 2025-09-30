<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->enum('type_document', ['piece_identite', 'justificatif_domicile', 'rib', 'kbis', 'assurance', 'mandat_sepa', 'contrat', 'correspondance', 'autre']);
            $table->string('nom_fichier');
            $table->string('nom_original');
            $table->string('chemin_fichier');
            $table->integer('taille');
            $table->string('mime_type');
            $table->text('description')->nullable();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();

            $table->index(['client_id', 'type_document']);
            $table->index('uploaded_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_documents');
    }
};