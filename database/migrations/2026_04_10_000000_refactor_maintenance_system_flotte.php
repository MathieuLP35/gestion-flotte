<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            // Supprimer les anciens champs de l'ex système
            $table->dropColumn(['km_alert_threshold', 'date_dernier_entretien']);

            // Ajouter les nouveaux champs pour l'historique
            $table->date('date')->nullable()->after('vehicle_id');
            $table->integer('kilometrage')->nullable()->after('date');
            // type possible : revision, pneus, freins, autre
            $table->string('type')->default('revision')->after('kilometrage');
            $table->decimal('cost', 10, 2)->nullable()->after('type'); // Coût
            $table->text('notes')->nullable()->after('cost');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            // Compléter la politique de maintenance du véhicule
            // Par sécurité, on check si kilometrage existe et sinon on l'ajoute
            if (! Schema::hasColumn('vehicles', 'kilometrage')) {
                $table->integer('kilometrage')->default(0)->after('km_initial');
            }
            if (! Schema::hasColumn('vehicles', 'last_service_km')) {
                $table->integer('last_service_km')->default(0)->after('kilometrage');
            }
            if (! Schema::hasColumn('vehicles', 'service_interval_km')) {
                $table->integer('service_interval_km')->default(20000)->after('last_service_km');
            }
            if (! Schema::hasColumn('vehicles', 'service_interval_months')) {
                $table->integer('service_interval_months')->default(12)->after('service_interval_km');
            }
            // Informations additionnelles pour potentiels historiques TCO
            if (! Schema::hasColumn('vehicles', 'purchase_price')) {
                $table->decimal('purchase_price', 12, 2)->nullable()->after('energie');
            }
            if (! Schema::hasColumn('vehicles', 'purchase_date')) {
                $table->date('purchase_date')->nullable()->after('purchase_price');
            }
            if (! Schema::hasColumn('vehicles', 'insurance_monthly')) {
                $table->decimal('insurance_monthly', 10, 2)->nullable()->after('purchase_date');
            }
            if (! Schema::hasColumn('vehicles', 'maintenance_monthly')) {
                $table->decimal('maintenance_monthly', 10, 2)->nullable()->after('insurance_monthly');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'kilometrage',
                'last_service_km',
                'service_interval_km',
                'service_interval_months',
                'purchase_price',
                'purchase_date',
                'insurance_monthly',
                'maintenance_monthly',
            ]);
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropColumn(['date', 'kilometrage', 'type', 'cost', 'notes']);

            $table->integer('km_alert_threshold')->default(0);
            $table->date('date_dernier_entretien')->nullable();
        });
    }
};
