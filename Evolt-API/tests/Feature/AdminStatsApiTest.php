<?php

use App\Models\Role;
use App\Models\Station;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('admin can access station stats', function () {
    $adminRole = Role::firstOrCreate(['name' => 'admin']);

    $admin = User::create([
        'name' => 'Admin Stats',
        'email' => 'adminstats@example.com',
        'password' => bcrypt('password123'),
        'role_id' => $adminRole->id,
    ]);

    Station::create([
        'name' => 'A',
        'address' => 'A',
        'latitude' => 33.5,
        'longitude' => -7,
        'connector_type' => 'CCS',
        'power_kw' => 50,
        'status' => 'available',
    ]);

    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/stations/stats');

    $response->assertStatus(200)->assertJsonStructure([
        'total_stations',
        'available',
        'occupied',
        'active_reservations',
        'average_power_kw',
    ]);
});

test('non admin cannot access station stats', function () {
    $role = Role::firstOrCreate(['name' => 'user']);

    $user = User::create([
        'name' => 'Normal User',
        'email' => 'normal@example.com',
        'password' => bcrypt('password123'),
        'role_id' => $role->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/stations/stats');

    $response->assertStatus(403);
});
