<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_suggestion_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('petit_trajet_seuil_km')->default(100)->comment('Seuil en km : trajets <= sont "petits"');
            $table->json('priorite_petit_trajet')->comment('Ordre de priorité des énergies pour petits trajets, ex: ["electrique","hybride"]');
            $table->json('priorite_long_trajet')->comment('Ordre de priorité des énergies pour trajets longs, ex: ["hybride","essence","diesel"]');
            $table->timestamps();
        });

        // Une seule ligne de config globale
        DB::table('vehicle_suggestion_settings')->insert([
            'petit_trajet_seuil_km' => 100,
            'priorite_petit_trajet' => json_encode(['electrique', 'hybride']),
            'priorite_long_trajet' => json_encode(['hybride', 'essence', 'diesel']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_suggestion_settings');
    }
};
