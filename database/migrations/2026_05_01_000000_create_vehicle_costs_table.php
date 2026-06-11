<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->string('type'); // fuel, toll, parking, cleaning, other
            $table->date('date');
            $table->decimal('amount', 10, 2);
            $table->integer('kilometrage')->nullable();
            $table->decimal('quantity', 8, 2)->nullable(); // litres pour carburant
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_costs');
    }
};
