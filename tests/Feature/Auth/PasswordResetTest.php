<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use function Pest\Laravel\postJson;

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = postJson('/api/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message'
        ]);
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    postJson('/api/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function (object $notification) use ($user) {
        $response = postJson('/api/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message'
            ]);

        return true;
    });
});
