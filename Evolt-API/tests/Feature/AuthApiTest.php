<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('register returns sanctum token', function () {
    Role::firstOrCreate(['name' => 'user']);

    $response = $this->postJson('/api/register', [
        'name' => 'User One',
        'email' => 'user1@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(201)->assertJsonStructure(['message', 'user', 'token']);

    $this->assertDatabaseHas('users', [
        'email' => 'user1@example.com',
    ]);
});

test('login returns sanctum token', function () {
    $role = Role::firstOrCreate(['name' => 'user']);

    User::create([
        'name' => 'User Two',
        'email' => 'user2@example.com',
        'password' => Hash::make('password123'),
        'role_id' => $role->id,
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'user2@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)->assertJsonStructure(['message', 'user', 'token']);

});

test('authenticated user can logout', function () {
    $role = Role::firstOrCreate(['name' => 'user']);
    $user = User::create([
        'name' => 'User Three',
        'email' => 'user3@example.com',
        'password' => Hash::make('password123'),
        'role_id' => $role->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/auth/logout');

    $response->assertStatus(200);
});
