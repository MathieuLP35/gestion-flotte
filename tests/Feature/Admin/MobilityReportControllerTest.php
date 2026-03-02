<?php

use App\Models\Agence;
use App\Models\Passenger;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\PermissionSeeder;

beforeEach(function (): void {
    $this->seed(PermissionSeeder::class);
});

// ─── Helpers ─────────────────────────────────────────────────────────────────

function makeAdmin(): User
{
    $agence = Agence::factory()->create();
    $admin = User::factory()->create(['agence_id' => $agence->id]);
    $admin->assignRole('Super Admin');

    return $admin;
}

// ─── index() ─────────────────────────────────────────────────────────────────

it('refuse l\'accès au rapport à un utilisateur non admin', function (): void {
    $agence = Agence::factory()->create();
    $user = User::factory()->create(['agence_id' => $agence->id]);

    $this->actingAs($user)->get(route('admin.mobility-report'))->assertForbidden();
});

it('rend la page du rapport avec la période par défaut (année en cours)', function (): void {
    $admin = makeAdmin();

    $this->actingAs($admin)
        ->get(route('admin.mobility-report'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/MobilityReport')
            ->has('reports', 1)
            ->where('reports.0.year', now()->year)
        );
});

it('génère un rapport par période avec un label "Année XXXX"', function (): void {
    $admin = makeAdmin();

    $this->actingAs($admin)
        ->get(route('admin.mobility-report', ['periods' => json_encode([['year' => 2023, 'month' => 'all']])]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('reports.0.periodLabel', 'Année 2023')
        );
});

it('génère un rapport par mois avec un label en français', function (): void {
    $admin = makeAdmin();

    $this->actingAs($admin)
        ->get(route('admin.mobility-report', ['periods' => json_encode([['year' => 2024, 'month' => 3]])]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('reports.0.periodLabel', 'Mars 2024')
        );
});

it('génère "Toutes périodes" si year=all', function (): void {
    $admin = makeAdmin();

    $this->actingAs($admin)
        ->get(route('admin.mobility-report', ['periods' => json_encode([['year' => 'all', 'month' => 'all']])]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('reports.0.periodLabel', 'Toutes périodes')
        );
});

it('rend le rapport avec plusieurs périodes pour comparaison', function (): void {
    $admin = makeAdmin();

    $periods = [
        ['year' => 2023, 'month' => 1],
        ['year' => 2024, 'month' => 1],
        ['year' => 2025, 'month' => 1],
    ];

    $this->actingAs($admin)
        ->get(route('admin.mobility-report', ['periods' => json_encode($periods)]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('reports', 3)
            ->where('reports.0.periodLabel', 'Janvier 2023')
            ->where('reports.1.periodLabel', 'Janvier 2024')
            ->where('reports.2.periodLabel', 'Janvier 2025')
        );
});

it('inclut les stats delta_co2 et delta_carpools dans le rapport', function (): void {
    $admin = makeAdmin();

    $this->actingAs($admin)
        ->get(route('admin.mobility-report'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('reports.0.stats')
            ->has('reports.0.stats.total_co2_saved')
            ->has('reports.0.stats.total_carpools')
            ->has('reports.0.stats.delta_co2')
            ->has('reports.0.stats.delta_carpools')
        );
});

it('inclut les agencesStats et vehicleEnergyStats dans le rapport', function (): void {
    $admin = makeAdmin();

    $this->actingAs($admin)
        ->get(route('admin.mobility-report'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('reports.0.agencesStats')
            ->has('reports.0.vehicleEnergyStats')
        );
});

it('calcule le co2 total des réservations terminées pour la période', function (): void {
    $admin = makeAdmin();
    $agence = Agence::factory()->create();
    $driver = User::factory()->create(['agence_id' => $agence->id]);

    $vehicle = Vehicle::create([
        'agence_id' => $agence->id, 'modele' => 'Zoé', 'immatriculation' => 'EL-CO2',
        'km_initial' => 0, 'emplacement' => 'X', 'nbr_places' => 5, 'energie' => 'electrique', 'en_maintenance' => false,
    ]);

    // Trajet terminé en Janvier 2024 avec 1 passager
    $reservation = Reservation::create([
        'user_id' => $driver->id,
        'vehicle_id' => $vehicle->id,
        'depart' => 'A', 'destination' => 'B',
        'depart_latitude' => 45.75, 'depart_longitude' => 4.85,
        'destination_latitude' => 48.85, 'destination_longitude' => 2.35,
        'date_debut' => '2024-01-15', 'date_fin' => '2024-01-15',
        'statut' => 'terminé', 'covoiturage' => true,
    ]);
    Passenger::create(['reservation_id' => $reservation->id, 'user_id' => $admin->id, 'statut' => 'confirme']);

    // Trajet d'une autre période (ne doit pas être compté)
    Reservation::create([
        'user_id' => $driver->id, 'vehicle_id' => $vehicle->id,
        'depart' => 'A', 'destination' => 'B',
        'depart_latitude' => 45.75, 'depart_longitude' => 4.85,
        'destination_latitude' => 48.85, 'destination_longitude' => 2.35,
        'date_debut' => '2023-10-01', 'date_fin' => '2023-10-01',
        'statut' => 'terminé', 'covoiturage' => false,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.mobility-report', ['periods' => json_encode([['year' => 2024, 'month' => 1]])]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('reports.0.stats.total_co2_saved', fn ($v) => $v > 0)
            ->where('reports.0.stats.total_carpools', 1)
        );
});

// ─── export() ─────────────────────────────────────────────────────────────────

it('exporte un PDF technique', function (): void {
    $admin = makeAdmin();

    $response = $this->actingAs($admin)
        ->get(route('admin.mobility-report.export', ['type' => 'technical']))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    expect($response->headers->get('content-disposition'))->toContain('rapport-mobilite-technical-');
});

it('exporte un PDF de communication', function (): void {
    $admin = makeAdmin();

    $response = $this->actingAs($admin)
        ->get(route('admin.mobility-report.export', ['type' => 'communication']))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    expect($response->headers->get('content-disposition'))->toContain('rapport-mobilite-communication-');
});

it('exporte un PDF avec plusieurs périodes', function (): void {
    $admin = makeAdmin();

    $periods = [
        ['year' => 2023, 'month' => 'all'],
        ['year' => 2024, 'month' => 'all'],
    ];

    $this->actingAs($admin)
        ->get(route('admin.mobility-report.export', ['type' => 'technical', 'periods' => json_encode($periods)]))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('exporte un PDF technical par défaut si type inconnu', function (): void {
    $admin = makeAdmin();

    $this->actingAs($admin)
        ->get(route('admin.mobility-report.export', ['type' => 'xyz']))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('refuse l\'export PDF à un non-admin', function (): void {
    $agence = Agence::factory()->create();
    $user = User::factory()->create(['agence_id' => $agence->id]);

    $this->actingAs($user)
        ->get(route('admin.mobility-report.export', ['type' => 'technical']))
        ->assertForbidden();
});
