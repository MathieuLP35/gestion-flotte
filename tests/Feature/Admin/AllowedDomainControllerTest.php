<?php

use App\Models\Agence;
use App\Models\AllowedDomain;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

function domainAdminUser(): User
{
    $agence = Agence::factory()->create();
    $u = User::factory()->create(['agence_id' => $agence->id]);
    $u->assignRole('Super Admin');
    return $u;
}

it('affiche la liste des domaines', function () {
    AllowedDomain::create(['name' => 'example.com']);

    $response = $this->actingAs(domainAdminUser())->get(route('admin.domains.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Domains/Index')
        ->has('domains')
        ->has('can')
    );
});

it('ajoute un domaine', function () {
    $response = $this->actingAs(domainAdminUser())->post(route('admin.domains.store'), [
        'name' => 'nouveau.com',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('allowed_domains', ['name' => 'nouveau.com']);
});

it('supprime un domaine', function () {
    $d = AllowedDomain::create(['name' => 'todelete.com']);

    $response = $this->actingAs(domainAdminUser())->delete(route('admin.domains.destroy', $d));

    $response->assertRedirect();
    $this->assertDatabaseMissing('allowed_domains', ['id' => $d->id]);
});
