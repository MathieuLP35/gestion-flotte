<?php

use App\Models\Agence;
use App\Models\User;
use App\Models\VehicleSuggestionSetting;
use Database\Seeders\PermissionSeeder;

beforeEach(function (): void {
    $this->seed(PermissionSeeder::class);
});

// ─── Helpers ─────────────────────────────────────────────────────────────────

function makeVehicleSuggestionAdmin(): User
{
    $agence = Agence::factory()->create();
    $admin = User::factory()->create(['agence_id' => $agence->id]);
    $admin->assignRole('Super Admin');

    return $admin;
}

// ─── edit() ──────────────────────────────────────────────────────────────────

it('affiche la page de paramétrage de suggestion de véhicule', function (): void {
    $admin = makeVehicleSuggestionAdmin();

    $this->actingAs($admin)
        ->get(route('admin.settings.vehicleSuggestion.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Settings/VehicleSuggestion')
            ->has('setting')
            ->has('setting.petit_trajet_seuil_km')
            ->has('setting.priorite_petit_trajet')
            ->has('setting.priorite_long_trajet')
            ->has('energies')
            ->has('can')
        );
});

it('retourne les valeurs par défaut si aucune configuration n\'existe', function (): void {
    $admin = makeVehicleSuggestionAdmin();

    // Aucune ligne en base
    VehicleSuggestionSetting::truncate();

    $this->actingAs($admin)
        ->get(route('admin.settings.vehicleSuggestion.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('setting.petit_trajet_seuil_km', 100)
            ->where('setting.priorite_petit_trajet', ['electrique', 'hybride'])
            ->where('setting.priorite_long_trajet', ['hybride', 'essence', 'diesel'])
        );
});

it('retourne la configuration existante depuis la base', function (): void {
    $admin = makeVehicleSuggestionAdmin();

    VehicleSuggestionSetting::truncate();
    VehicleSuggestionSetting::create([
        'petit_trajet_seuil_km' => 150,
        'priorite_petit_trajet' => ['hybride', 'electrique'],
        'priorite_long_trajet' => ['diesel', 'essence'],
    ]);

    $this->actingAs($admin)
        ->get(route('admin.settings.vehicleSuggestion.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('setting.petit_trajet_seuil_km', 150)
        );
});

it('expose les énergies disponibles pour la validation', function (): void {
    $admin = makeVehicleSuggestionAdmin();

    $this->actingAs($admin)
        ->get(route('admin.settings.vehicleSuggestion.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('energies', ['electrique', 'hybride', 'essence', 'diesel'])
        );
});

it('expose les permissions can.edit', function (): void {
    $admin = makeVehicleSuggestionAdmin();

    $this->actingAs($admin)
        ->get(route('admin.settings.vehicleSuggestion.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('can.edit')
        );
});

it('refuse l\'accès à la page à un utilisateur sans permission', function (): void {
    $agence = Agence::factory()->create();
    $user = User::factory()->create(['agence_id' => $agence->id]);

    $this->actingAs($user)
        ->get(route('admin.settings.vehicleSuggestion.edit'))
        ->assertForbidden();
});

// ─── update() ────────────────────────────────────────────────────────────────

it('met à jour les paramètres de suggestion de véhicule', function (): void {
    $admin = makeVehicleSuggestionAdmin();

    $data = [
        'petit_trajet_seuil_km' => 80,
        'priorite_petit_trajet' => ['electrique'],
        'priorite_long_trajet' => ['diesel', 'essence'],
    ];

    $this->actingAs($admin)
        ->put(route('admin.settings.vehicleSuggestion.update'), $data)
        ->assertRedirect();

    $setting = VehicleSuggestionSetting::first();
    expect($setting->petit_trajet_seuil_km)->toBe(80);
    expect($setting->priorite_petit_trajet)->toBe(['electrique']);
    expect($setting->priorite_long_trajet)->toBe(['diesel', 'essence']);
});

it('persiste le message de succès après la mise à jour', function (): void {
    $admin = makeVehicleSuggestionAdmin();

    $data = [
        'petit_trajet_seuil_km' => 100,
        'priorite_petit_trajet' => ['electrique', 'hybride'],
        'priorite_long_trajet' => ['hybride', 'essence'],
    ];

    $this->actingAs($admin)
        ->put(route('admin.settings.vehicleSuggestion.update'), $data)
        ->assertSessionHas('success', 'Paramètres de suggestion de véhicule enregistrés.');
});

it('rejette une mise à jour avec seuil_km invalide (< 1)', function (): void {
    $admin = makeVehicleSuggestionAdmin();

    $data = [
        'petit_trajet_seuil_km' => 0,
        'priorite_petit_trajet' => ['electrique'],
        'priorite_long_trajet' => ['hybride'],
    ];

    $this->actingAs($admin)
        ->put(route('admin.settings.vehicleSuggestion.update'), $data)
        ->assertSessionHasErrors('petit_trajet_seuil_km');
});

it('rejette une mise à jour avec seuil_km invalide (> 2000)', function (): void {
    $admin = makeVehicleSuggestionAdmin();

    $data = [
        'petit_trajet_seuil_km' => 9999,
        'priorite_petit_trajet' => ['electrique'],
        'priorite_long_trajet' => ['hybride'],
    ];

    $this->actingAs($admin)
        ->put(route('admin.settings.vehicleSuggestion.update'), $data)
        ->assertSessionHasErrors('petit_trajet_seuil_km');
});

it('rejette une mise à jour si priorite_petit_trajet est vide', function (): void {
    $admin = makeVehicleSuggestionAdmin();

    $data = [
        'petit_trajet_seuil_km' => 100,
        'priorite_petit_trajet' => [],
        'priorite_long_trajet' => ['hybride'],
    ];

    $this->actingAs($admin)
        ->put(route('admin.settings.vehicleSuggestion.update'), $data)
        ->assertSessionHasErrors('priorite_petit_trajet');
});

it('rejette une mise à jour si une énergie est invalide', function (): void {
    $admin = makeVehicleSuggestionAdmin();

    $data = [
        'petit_trajet_seuil_km' => 100,
        'priorite_petit_trajet' => ['nuclear'], // invalide
        'priorite_long_trajet' => ['hybride'],
    ];

    $this->actingAs($admin)
        ->put(route('admin.settings.vehicleSuggestion.update'), $data)
        ->assertSessionHasErrors('priorite_petit_trajet.0');
});

it('refuse la mise à jour à un utilisateur sans permission', function (): void {
    $agence = Agence::factory()->create();
    $user = User::factory()->create(['agence_id' => $agence->id]);

    $data = [
        'petit_trajet_seuil_km' => 100,
        'priorite_petit_trajet' => ['electrique'],
        'priorite_long_trajet' => ['hybride'],
    ];

    $this->actingAs($user)
        ->put(route('admin.settings.vehicleSuggestion.update'), $data)
        ->assertForbidden();
});
