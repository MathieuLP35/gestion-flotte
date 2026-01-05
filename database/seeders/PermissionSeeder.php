<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions pour les RÔLES
        Permission::create(['name' => 'roles.view']);
        Permission::create(['name' => 'roles.create']);
        Permission::create(['name' => 'roles.edit']);
        Permission::create(['name' => 'roles.delete']);
        
        // Permissions pour les UTILISATEURS
        Permission::create(['name' => 'users.view']);
        Permission::create(['name' => 'users.create']);
        Permission::create(['name' => 'users.edit']);
        Permission::create(['name' => 'users.delete']);

        // Permissions pour les VÉHICULES
        Permission::create(['name' => 'vehicles.view']);
        Permission::create(['name' => 'vehicles.create']);
        Permission::create(['name' => 'vehicles.edit']);
        Permission::create(['name' => 'vehicles.delete']);

        // Permissions pour les RÉSERVATIONS
        Permission::create(['name' => 'reservations.view']);
        Permission::create(['name' => 'reservations.create']);
        Permission::create(['name' => 'reservations.edit']);
        Permission::create(['name' => 'reservations.delete']);

        // Permissions pour la GESTION DES DOMAINES AUTORISÉS
        Permission::create(['name' => 'allowed_domains.view']);
        Permission::create(['name' => 'allowed_domains.create']);
        Permission::create(['name' => 'allowed_domains.edit']);
        Permission::create(['name' => 'allowed_domains.delete']);


        // --- CRÉATION DU RÔLE SUPER-ADMIN ---
        $role = Role::firstOrCreate(['name' => 'Super Admin']);
        $role->givePermissionTo(Permission::all());

        $user = User::find(1);
        if ($user) {
            $user->assignRole('Super Admin');
        }
    }
}