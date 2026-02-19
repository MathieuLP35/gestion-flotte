<?php

use App\Models\User;
use App\Models\Passenger;
use App\Models\Reservation;
use App\Policies\PassengerPolicy;
use App\Policies\ReservationPolicy;
use App\Providers\AuthServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Force le re-enregistrement pour le coverage
    app()->register(AuthServiceProvider::class, true);
});

it('enregistre correctement les policies', function () {
    expect(Gate::getPolicyFor(Reservation::class))->toBeInstanceOf(ReservationPolicy::class)
        ->and(Gate::getPolicyFor(Passenger::class))->toBeInstanceOf(PassengerPolicy::class);
});

it('donne tous les accès au Super Admin via Gate::before', function () {
    // Correction de la signature : $roles, ?string $guard = null
    $superAdminUser = new class extends User {
        public function hasRole($roles, ?string $guard = null): bool {
            return $roles === 'Super Admin';
        }
    };
    $superAdminUser->id = 1;

    expect(Gate::forUser($superAdminUser)->allows('action-quelconque'))->toBeTrue();
});

it('refuse le bypass automatique aux utilisateurs normaux', function () {
    $regularUser = new class extends User {
        public function hasRole($roles, ?string $guard = null): bool {
            return false;
        }
    };
    $regularUser->id = 2;

    expect(Gate::forUser($regularUser)->allows('action-quelconque'))->toBeFalse();
});
