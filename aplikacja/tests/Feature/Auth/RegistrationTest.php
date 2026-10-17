<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

function registrationData(array $overrides = []): array
{
    return array_merge([
        'name' => 'Jan',
        'surname' => 'Kowalski',
        'email' => 'jan@example.com',
        'phone' => '123456789',
        'password' => 'password',
        'password_confirmation' => 'password',
    ], $overrides);
}

test('registration screen can be rendered', function () {
    $this->get('/register')->assertOk();
});

test('new users can register', function () {
    Event::fake([Registered::class]);

    $response = $this->post('/register', registrationData());

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard.index', absolute: false));

    $user = User::where('email', 'jan@example.com')->firstOrFail();

    $this->assertAuthenticatedAs($user);
    expect($user->name)->toBe('Jan')
        ->and($user->surname)->toBe('Kowalski')
        ->and($user->phone)->toBe('123456789')
        ->and($user->role)->toBe('client')
        ->and($user->active)->toBeTrue()
        ->and(Hash::check('password', $user->password))->toBeTrue();

    Event::assertDispatched(Registered::class, fn ($event) => $event->user->is($user));
});

test('spaces and dashes are removed from the phone number', function (string $phone, string $expected) {
    $this->post('/register', registrationData(['phone' => $phone]))
        ->assertSessionHasNoErrors();

    expect(User::where('email', 'jan@example.com')->value('phone'))->toBe($expected);
})->with([
    'spaces' => ['123 456 789', '123456789'],
    'dashes' => ['123-456-789', '123456789'],
    'country code' => ['+48 123-456-789', '+48123456789'],
]);

test('invalid phone numbers are rejected', function (string $phone) {
    $this->post('/register', registrationData(['phone' => $phone]))
        ->assertSessionHasErrors('phone');

    $this->assertGuest();
})->with([
    'too short' => '12345678',
    'too long' => '1234567890',
    'letters' => '12345678a',
    'other country code' => '+49123456789',
]);

test('all fields are required', function (string $field) {
    $this->post('/register', registrationData([$field => '']))
        ->assertSessionHasErrors($field);

    $this->assertGuest();
    expect(User::count())->toBe(0);
})->with(['name', 'surname', 'email', 'phone', 'password']);

test('email must be valid and lowercase', function (string $email) {
    $this->post('/register', registrationData(['email' => $email]))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
})->with([
    'invalid' => 'not-an-email',
    'uppercase' => 'Jan@Example.com',
]);

test('email must not be taken', function () {
    User::factory()->create(['email' => 'jan@example.com']);

    $this->post('/register', registrationData())
        ->assertSessionHasErrors('email');

    $this->assertGuest();
    expect(User::count())->toBe(1);
});

test('password must be confirmed', function () {
    $this->post('/register', registrationData(['password_confirmation' => 'different']))
        ->assertSessionHasErrors('password');

    $this->assertGuest();
});

test('password must have at least 8 characters', function () {
    $this->post('/register', registrationData([
        'password' => 'short',
        'password_confirmation' => 'short',
    ]))->assertSessionHasErrors('password');

    $this->assertGuest();
});

test('the role field is ignored so users can not register as admin', function () {
    $this->post('/register', registrationData(['role' => 'admin']))
        ->assertSessionHasNoErrors();

    expect(User::where('email', 'jan@example.com')->value('role'))->toBe('client');
});

test('logged in users can not open the registration screen', function () {
    $user = User::factory()->create(['active' => true]);

    $this->actingAs($user)->get('/register')->assertRedirect();
});
