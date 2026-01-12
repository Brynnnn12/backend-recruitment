<?php

use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['token']
        ]);
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $response = postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors'
        ]);
});

test('users berhak untuk logout', function () {
    $user = User::factory()->create();

    $response = actingAs($user, 'sanctum')->postJson('/api/logout');
    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message'
        ]);
});
