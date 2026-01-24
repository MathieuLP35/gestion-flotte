<?php

use App\Models\Passenger;
use App\Models\Reservation;
use App\Models\User;
use App\Policies\PassengerPolicy;

it('autorise le conducteur à modifier le passager', function () {
    $driver = User::factory()->create();
    $r = new Reservation(['user_id' => $driver->id]);
    $r->id = 1;
    $p = new Passenger(['user_id' => 999, 'reservation_id' => 1]);
    $p->setRelation('reservation', $r);
    $policy = new PassengerPolicy;

    expect($policy->update($driver, $p))->toBeTrue();
});

it('autorise le passager à se supprimer lui-même', function () {
    $user = User::factory()->create();
    $p = new Passenger(['user_id' => $user->id, 'reservation_id' => 1]);
    $p->setRelation('reservation', new Reservation(['user_id' => 999]));
    $policy = new PassengerPolicy;

    expect($policy->delete($user, $p))->toBeTrue();
});

it('autorise le conducteur à supprimer un passager', function () {
    $driver = User::factory()->create();
    $p = new Passenger(['user_id' => 999, 'reservation_id' => 1]);
    $p->setRelation('reservation', new Reservation(['user_id' => $driver->id]));
    $policy = new PassengerPolicy;

    expect($policy->delete($driver, $p))->toBeTrue();
});
