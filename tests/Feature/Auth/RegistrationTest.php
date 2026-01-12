<?php

use Spatie\Permission\Models\Role;
use function Pest\Laravel\postJson;

beforeEach(function () {
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'hr']);
    Role::create(['name' => 'user']);
});

test('new users can register', function () {

    $response = postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);



    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['token']
        ]);
});
