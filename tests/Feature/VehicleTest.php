<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Agence;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_vehicle()
    {
        $agence = Agence::factory()->create();
        $user = User::factory()->create(['agence_id' => $agence->id]);

        $this->actingAs($user);

        $response = $this->post(route('vehicles.store'), [
            'modele' => 'Peugeot 208',
            'immatriculation' => 'AB-123-CD',
            'km_initial' => 0,
            'emplacement' => 'Garage A',
            'nbr_places' => 5,
        ]);

        $response->assertRedirect(route('vehicles.index'));

        $this->assertDatabaseHas('vehicles', [
            'modele' => 'Peugeot 208',
            'immatriculation' => 'AB-123-CD',
            'agence_id' => $agence->id,
        ]);
    }
}
