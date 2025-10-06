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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id']);
        });

        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');

            // Types de notifications
            $table->boolean('email_paiement_recu')->default(true);
            $table->boolean('email_paiement_retard')->default(true);
            $table->boolean('email_nouvelle_reservation')->default(true);
            $table->boolean('email_fin_contrat')->default(true);
            $table->boolean('email_acces_refuse')->default(true);

            $table->boolean('push_paiement_recu')->default(true);
            $table->boolean('push_paiement_retard')->default(true);
            $table->boolean('push_nouvelle_reservation')->default(true);
            $table->boolean('push_fin_contrat')->default(false);
            $table->boolean('push_acces_refuse')->default(true);

            $table->boolean('sms_paiement_retard')->default(false);
            $table->boolean('sms_fin_contrat')->default(false);
            $table->boolean('sms_acces_refuse')->default(false);

            // Paramètres généraux
            $table->boolean('notifications_activees')->default(true);
            $table->time('heure_debut_notifications')->default('08:00:00');
            $table->time('heure_fin_notifications')->default('20:00:00');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
        Schema::dropIfExists('notifications');
    }
};
