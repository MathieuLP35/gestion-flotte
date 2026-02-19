<?php

use App\Models\Passenger;
use App\Models\Reservation;
use App\Models\User;
use App\Policies\PassengerPolicy;
use App\Policies\ReservationPolicy;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Force le re-enregistrement pour le coverage
    app()->register(AuthServiceProvider::class, true);
});

it('enregistre correctement les policies', function (): void {
    expect(Gate::getPolicyFor(Reservation::class))->toBeInstanceOf(ReservationPolicy::class)
        ->and(Gate::getPolicyFor(Passenger::class))->toBeInstanceOf(PassengerPolicy::class);
});

it('donne tous les accès au Super Admin via Gate::before', function (): void {
    // Correction de la signature et de la position des accolades
    $superAdminUser = new class extends User
    {
        public function hasRole($roles, ?string $guard = null): bool
        {
            return $roles === 'Super Admin';
        }
    };
    $superAdminUser->id = 1;

    expect(Gate::forUser($superAdminUser)->allows('action-quelconque'))->toBeTrue();
});

it('refuse le bypass automatique aux utilisateurs normaux', function (): void {
    $regularUser = new class extends User
    {
        public function hasRole($roles, ?string $guard = null): bool
        {
            return false;
        }
    };
    $regularUser->id = 2;

    expect(Gate::forUser($regularUser)->allows('action-quelconque'))->toBeFalse();
});
