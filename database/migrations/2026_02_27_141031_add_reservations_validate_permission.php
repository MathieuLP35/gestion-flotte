<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Nettoyer le cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Ajouter la nouvelle permission
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'reservations.validate']);

        // Donner la permission au Super Admin (s'il existe déjà)
        $role = \Spatie\Permission\Models\Role::where('name', 'Super Admin')->first();
        if ($role) {
            $role->givePermissionTo('reservations.validate');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $permission = \Spatie\Permission\Models\Permission::where('name', 'reservations.validate')->first();
        if ($permission) {
            $permission->delete();
        }
    }
};
