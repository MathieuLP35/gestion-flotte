<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Permissions pour la suggestion de véhicule (paramètres admin).
     * À exécuter sur les bases existantes ; le PermissionSeeder les crée
     * aussi pour les nouvelles installations.
     */
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::firstOrCreate(['name' => 'vehicle_suggestion.view']);
        Permission::firstOrCreate(['name' => 'vehicle_suggestion.edit']);

        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo(['vehicle_suggestion.view', 'vehicle_suggestion.edit']);
        }
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->revokePermissionTo(['vehicle_suggestion.view', 'vehicle_suggestion.edit']);
        }

        Permission::whereIn('name', ['vehicle_suggestion.view', 'vehicle_suggestion.edit'])->delete();
    }
};
