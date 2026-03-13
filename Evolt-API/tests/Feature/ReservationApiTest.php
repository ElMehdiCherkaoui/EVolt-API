<?php

use App\Models\Reservation;
use App\Models\Role;
use App\Models\Station;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('authenticated user can create reservation', function () {
    $role = Role::firstOrCreate(['name' => 'user']);
    $user = User::create([
        'name' => 'Res User',
        'email' => 'res@example.com',
        'password' => bcrypt('password123'),
        'role_id' => $role->id,
    ]);

    $station = Station::create([
        'name' => 'Station 1',
        'address' => 'Address 1',
        'latitude' => 33.5,
        'longitude' => -7,
        'connector_type' => 'CCS',
        'power_kw' => 50,
        'status' => 'available',
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/reservations', [
        'station_id' => $station->id,
        'start_time' => now()->addHour()->toDateTimeString(),
        'estimated_duration_minutes' => 45,
        'status' => 'confirmed',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('reservation.user_id', $user->id);
});

test('user can update and cancel own reservation', function () {
    $role = Role::firstOrCreate(['name' => 'user']);
    $user = User::create([
        'name' => 'Update User',
        'email' => 'update@example.com',
        'password' => bcrypt('password123'),
        'role_id' => $role->id,
    ]);

    $station = Station::create([
        'name' => 'Station 2',
        'address' => 'Address 2',
        'latitude' => 33,
        'longitude' => -7,
        'connector_type' => 'Type2',
        'power_kw' => 22,
        'status' => 'available',
    ]);

    $reservation = Reservation::create([
        'user_id' => $user->id,
        'station_id' => $station->id,
        'start_time' => now()->addHours(2),
        'end_time' => now()->addHours(3),
        'estimated_duration_minutes' => 60,
        'status' => 'confirmed',
    ]);

    Sanctum::actingAs($user);

    $this->putJson('/api/reservations/'.$reservation->id, [
        'estimated_duration_minutes' => 90,
        'status' => 'confirmed',
    ])->assertStatus(200);

    $this->patchJson('/api/reservations/'.$reservation->id.'/cancel')
        ->assertStatus(200)
        ->assertJsonPath('reservation.status', 'cancelled');
});

test('user can view only own history', function () {
    $role = Role::firstOrCreate(['name' => 'user']);
    $userA = User::create([
        'name' => 'A',
        'email' => 'a@example.com',
        'password' => bcrypt('password123'),
        'role_id' => $role->id,
    ]);
    $userB = User::create([
        'name' => 'B',
        'email' => 'b@example.com',
        'password' => bcrypt('password123'),
        'role_id' => $role->id,
    ]);

    $station = Station::create([
        'name' => 'Station 3',
        'address' => 'Address 3',
        'latitude' => 33.5,
        'longitude' => -7,
        'connector_type' => 'CCS',
        'power_kw' => 75,
        'status' => 'available',
    ]);

    Reservation::create([
        'user_id' => $userA->id,
        'station_id' => $station->id,
        'start_time' => now()->subHours(2),
        'end_time' => now()->subHour(),
        'estimated_duration_minutes' => 60,
        'status' => 'confirmed',
    ]);

    Reservation::create([
        'user_id' => $userB->id,
        'station_id' => $station->id,
        'start_time' => now()->subHours(4),
        'end_time' => now()->subHours(3),
        'estimated_duration_minutes' => 60,
        'status' => 'confirmed',
    ]);

    Sanctum::actingAs($userA);

    $response = $this->getJson('/api/my-reservations');

    $response->assertStatus(200)->assertJsonCount(1);
});
