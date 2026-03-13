<?php

use App\Models\Role;
use App\Models\Station;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('can list stations', function () {
    Station::create([
        'name' => 'S1',
        'address' => 'A1',
        'latitude' => 33,
        'longitude' => -7,
        'connector_type' => 'Type2',
        'power_kw' => 22,
        'status' => 'available',
    ]);

    $response = $this->getJson('/api/stations');

    $response->assertStatus(200)->assertJsonCount(1);
});

test('can search available stations by location', function () {
     Station::create([
        'name' => 'Near',
        'address' => 'Near Address',
        'latitude' => 33,
        'longitude' => -7,
        'connector_type' => 'CCS',
        'power_kw' => 50,
        'status' => 'available',
    ]);

    Station::create([
        'name' => 'Far',
        'address' => 'Far Address',
        'latitude' => 34,
        'longitude' => -8,
        'connector_type' => 'Type2',
        'power_kw' => 22,
        'status' => 'available',
    ]);

    $response = $this->postJson('/api/stations/search', [
        'latitude' => 33.5731,
        'longitude' => -7.5898,
    ]);

    $response->assertStatus(200);
});

test('admin can create update and delete station', function () {
    $adminRole = Role::create(['name' => 'admin']);
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('password123'),
        'role_id' => $adminRole->id,
    ]);

    Sanctum::actingAs($admin);

    $create = $this->postJson('/api/stations', [
        'name' => 'New Station',
        'address' => 'Address',
        'latitude' => 33.6,
        'longitude' => -7.6,
        'connector_type' => 'CCS',
        'power_kw' => 60,
        'status' => 'available',
    ]);

    $create->assertStatus(201);
    $id = $create->json('station.id');

    $this->putJson('/api/stations/'.$id, [
        'name' => 'Updated Station',
        'latitude' => 33,
        'longitude' => -7,
        'connector_type' => 'Type2',
        'power_kw' => 40,
        'status' => 'occupied',
    ])->assertStatus(200);

    $this->deleteJson('/api/stations/'.$id)->assertStatus(200);
});

test('non admin cannot create station', function () {
    $role = Role::create(['name' => 'user']);
    $user = User::create([
        'name' => 'User',
        'email' => 'basic@example.com',
        'password' => bcrypt('password123'),
        'role_id' => $role->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/stations', [
        'name' => 'Blocked Station',
        'address' => 'Address',
        'latitude' => 33.6,
        'longitude' => -7.6,
        'connector_type' => 'CCS',
        'power_kw' => 60,
        'status' => 'available',
    ]);

    $response->assertStatus(403);
});
