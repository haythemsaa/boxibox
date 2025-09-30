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
        Schema::create('reminders_config', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('payment'); // payment, contract_expiry, etc.
            $table->integer('days_before')->default(5); // Nombre de jours avant échéance
            $table->boolean('send_email')->default(true);
            $table->boolean('send_sms')->default(false);
            $table->text('email_template')->nullable();
            $table->text('sms_template')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Créer la table pour historiser les rappels envoyés
        Schema::create('payment_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_id')->constrained('factures')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->enum('type', ['preventif', 'relance_1', 'relance_2', 'mise_en_demeure']);
            $table->enum('canal', ['email', 'sms', 'both']);
            $table->date('date_envoi');
            $table->boolean('email_sent')->default(false);
            $table->boolean('sms_sent')->default(false);
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_reminders');
        Schema::dropIfExists('reminders_config');
    }
};
