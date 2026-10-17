<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $this->get('/login')->assertOk();
});

test('active users can log in', function () {
    $user = User::factory()->create(['active' => true]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard.index', absolute: false));
});

test('users are redirected to the page they wanted before logging in', function () {
    $user = User::factory()->create(['active' => true]);

    $this->get('/dashboard/profile')->assertRedirect('/login');

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect('/dashboard/profile');
});

test('users can not log in with a wrong password', function () {
    $user = User::factory()->create(['active' => true]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('users can not log in with an unknown email', function () {
    $this->post('/login', [
        'email' => 'nobody@example.com',
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('inactive users are logged out and sent to the inactive account page', function () {
    $user = User::factory()->create(['active' => false]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('account.inactive'));

    $this->assertGuest();
});

test('login requires a valid email and a password', function (array $data, string $field) {
    $this->post('/login', $data)->assertSessionHasErrors($field);

    $this->assertGuest();
})->with([
    'missing email' => [['password' => 'password'], 'email'],
    'invalid email' => [['email' => 'not-an-email', 'password' => 'password'], 'email'],
    'missing password' => [['email' => 'user@example.com'], 'password'],
]);

test('login is locked after 5 failed attempts even with the correct password', function () {
    $user = User::factory()->create(['active' => true]);

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);
    }

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('a successful login resets the failed attempts counter', function () {
    $user = User::factory()->create(['active' => true]);

    for ($i = 0; $i < 4; $i++) {
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password']);
    }

    $this->post('/login', ['email' => $user->email, 'password' => 'password']);
    $this->post('/logout');

    for ($i = 0; $i < 4; $i++) {
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password']);
    }

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasNoErrors();

    $this->assertAuthenticatedAs($user);
});

test('logged in users can not open the login screen', function () {
    $user = User::factory()->create(['active' => true]);

    $this->actingAs($user)->get('/login')->assertRedirect();
});

test('users can log out', function () {
    $user = User::factory()->create(['active' => true]);

    $this->actingAs($user)->post('/logout')->assertRedirect('/');

    $this->assertGuest();
});
