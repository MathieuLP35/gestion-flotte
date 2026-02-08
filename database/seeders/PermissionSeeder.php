<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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

        // Permissions pour la SUGGESTION DE VÉHICULE (paramètres admin)
        Permission::firstOrCreate(['name' => 'vehicle_suggestion.view']);
        Permission::firstOrCreate(['name' => 'vehicle_suggestion.edit']);

        // Accès à l'administration (lien / menu admin)
        Permission::firstOrCreate(['name' => 'admin.view']);

        // Permissions pour les AGENCES
        Permission::firstOrCreate(['name' => 'agences.view']);
        Permission::firstOrCreate(['name' => 'agences.view_all']); // Vision globale (toutes les agences)

        // --- CRÉATION DU RÔLE SUPER-ADMIN ---
        $role = Role::firstOrCreate(['name' => 'Super Admin']);
        $role->givePermissionTo(Permission::all());

        $user = User::find(1);
        if ($user) {
            $user->assignRole('Super Admin');
        }

        // --- RÔLE ADMINISTRATEUR (accès admin sans tout gérer) ---
        $adminRole = Role::firstOrCreate(['name' => 'Administrateur']);
        $adminRole->syncPermissions([
            'admin.view',
            'agences.view', 'agences.view_all',
            'vehicles.view', 'vehicles.create', 'vehicles.edit',
            'reservations.view', 'reservations.create', 'reservations.edit',
            'users.view', 'users.create', 'users.edit',
        ]);

        // Second admin (compatible DEMO_MAIL_RECIPIENT : email peut être toi+admin2@...)
        $admin2 = User::where('name', 'Marie Martin')->first();
        if ($admin2 && ! $admin2->hasRole('Administrateur')) {
            $admin2->assignRole('Administrateur');
        }
    }
}
