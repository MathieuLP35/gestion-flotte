<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('passengers', function (Blueprint $table) {
            $table->id();

            // Le trajet auquel le passager est rattaché
            $table->foreignId('reservation_id')->constrained('reservations')->onDelete('cascade');

            // L'utilisateur qui est passager
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Statut de la demande du passager
            $table->string('statut')->default('en_attente'); // en_attente, confirme, refuse

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('passengers');
    }
};