<?php

use App\Models\Reservation;
use App\Models\User;
use App\Policies\ReservationPolicy;

it('autorise le conducteur à voir la réservation', function (): void {
    $user = User::factory()->create();
    $r = new Reservation(['user_id' => $user->id]);
    $policy = new ReservationPolicy;

    expect($policy->view($user, $r))->toBeTrue();
});

it('autorise le conducteur à modifier la réservation', function (): void {
    $user = User::factory()->create();
    $r = new Reservation(['user_id' => $user->id]);
    $policy = new ReservationPolicy;

    expect($policy->update($user, $r))->toBeTrue();
});

it('refuse à un autre utilisateur de modifier la réservation', function (): void {
    $user = User::factory()->create();
    $autre = User::factory()->create();
    $r = new Reservation(['user_id' => $autre->id]);
    $policy = new ReservationPolicy;

    expect($policy->update($user, $r))->toBeFalse();
});

it('autorise le conducteur à supprimer la réservation', function (): void {
    $user = User::factory()->create();
    $r = new Reservation(['user_id' => $user->id]);
    $policy = new ReservationPolicy;

    expect($policy->delete($user, $r))->toBeTrue();
});
