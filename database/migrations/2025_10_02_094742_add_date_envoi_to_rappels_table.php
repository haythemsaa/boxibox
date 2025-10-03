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
        Schema::table('rappels', function (Blueprint $table) {
            $table->dateTime('date_envoi')->nullable()->after('date_rappel');
            $table->decimal('montant', 10, 2)->nullable()->after('montant_du');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rappels', function (Blueprint $table) {
            $table->dropColumn(['date_envoi', 'montant']);
        });
    }
};
