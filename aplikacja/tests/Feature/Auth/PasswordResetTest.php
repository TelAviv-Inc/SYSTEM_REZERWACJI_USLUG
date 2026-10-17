<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

// The pages extend a layout, so check the form is really there - a status 200 alone passed while they rendered blank
test('reset password link screen can be rendered', function () {
    $this->get('/forgot-password')
        ->assertOk()
        ->assertSee('action="'.route('password.email').'"', false)
        ->assertSee('name="email"', false);
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->from('/forgot-password')
        ->followingRedirects()
        ->post('/forgot-password', ['email' => $user->email])
        ->assertSee('Wysłaliśmy e-mail z linkiem do resetowania hasła.');

    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        $this->get('/reset-password/'.$notification->token)
            ->assertOk()
            ->assertSee('name="token" value="'.$notification->token.'"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="password_confirmation"', false);

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        return true;
    });
});
