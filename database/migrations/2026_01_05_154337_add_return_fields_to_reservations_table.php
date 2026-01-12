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
        Schema::table('reservations', function (Blueprint $table) {
            $table->dateTime('date_retour')->nullable()->after('date_fin');
            $table->integer('km_final')->nullable()->after('date_retour');
            $table->string('emplacement_retour')->nullable()->after('km_final');
            $table->string('etat_vehicule')->nullable()->after('emplacement_retour')->comment('excellent, bon, moyen, mauvais');
            $table->text('notes_retour')->nullable()->after('etat_vehicule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['date_retour', 'km_final', 'emplacement_retour', 'etat_vehicule', 'notes_retour']);
        });
    }
};
