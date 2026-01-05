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
            $table->decimal('destination_latitude', 10, 7)->nullable()->after('depart_longitude');
            $table->decimal('destination_longitude', 10, 7)->nullable()->after('destination_latitude');
            $table->decimal('depart_latitude', 10, 7)->nullable()->after('destination_longitude');
            $table->decimal('depart_longitude', 10, 7)->nullable()->after('depart_latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
