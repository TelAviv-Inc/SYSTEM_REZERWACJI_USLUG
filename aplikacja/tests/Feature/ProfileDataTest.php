<?php

use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    // The factory's faker phone doesn't match the form's regex, so pin a valid one
    $this->user = User::factory()->create([
        'email' => 'old@example.com',
        'phone' => '123456789',
    ]);

    $this->actingAs($this->user);
});

test('form is prefilled with the current email and phone', function () {
    Livewire::test('profile-data')
        ->assertSet('form.email', 'old@example.com')
        ->assertSet('form.phone', '123456789');
});

test('phone can be changed without a password', function () {
    Livewire::test('profile-data')
        ->set('form.phone', '+48987654321')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('profile-updated');

    expect($this->user->fresh()->phone)->toBe('+48987654321');
});

test('phone is normalized before validation', function () {
    Livewire::test('profile-data')
        ->set('form.phone', '987 654-321')
        ->call('save')
        ->assertHasNoErrors();

    expect($this->user->fresh()->phone)->toBe('987654321');
});

test('invalid phone is rejected', function (string $phone) {
    Livewire::test('profile-data')
        ->set('form.phone', $phone)
        ->call('save')
        ->assertHasErrors(['form.phone' => 'regex'])
        ->assertNotDispatched('profile-updated');

    expect($this->user->fresh()->phone)->toBe('123456789');
})->with(['12345', '+491234567890', 'abcdefghi']);

test('phone is required', function () {
    Livewire::test('profile-data')
        ->set('form.phone', '')
        ->call('save')
        ->assertHasErrors(['form.phone' => 'required']);
});

test('email can be changed with the correct password', function () {
    Livewire::test('profile-data')
        ->set('form.email', 'new@example.com')
        ->set('form.current_password', 'password')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('form.current_password', '')
        ->assertDispatched('profile-updated');

    expect($this->user->fresh()->email)->toBe('new@example.com');
});

test('changing email requires the current password', function () {
    Livewire::test('profile-data')
        ->set('form.email', 'new@example.com')
        ->call('save')
        ->assertHasErrors(['form.current_password' => 'required']);

    expect($this->user->fresh()->email)->toBe('old@example.com');
});

test('changing email with a wrong password fails', function () {
    Livewire::test('profile-data')
        ->set('form.email', 'new@example.com')
        ->set('form.current_password', 'wrong-password')
        ->call('save')
        ->assertHasErrors(['form.current_password' => 'current_password']);

    expect($this->user->fresh()->email)->toBe('old@example.com');
});

test('email already used by another user is rejected', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    Livewire::test('profile-data')
        ->set('form.email', 'taken@example.com')
        ->set('form.current_password', 'password')
        ->call('save')
        ->assertHasErrors(['form.email' => 'unique']);

    expect($this->user->fresh()->email)->toBe('old@example.com');
});

test('email taken between validation and save shows a validation error', function () {
    // Simulate the race: another user grabs the email right before our UPDATE runs
    User::saving(function (User $saving) {
        if ($saving->is($this->user)) {
            User::withoutEvents(fn () => User::factory()->create(['email' => 'race@example.com']));
        }
    });

    Livewire::test('profile-data')
        ->set('form.email', 'race@example.com')
        ->set('form.current_password', 'password')
        ->call('save')
        ->assertHasErrors(['form.email'])
        ->assertNotDispatched('profile-updated');

    expect($this->user->fresh()->email)->toBe('old@example.com');
});

test('saving without changes does not update or dispatch', function () {
    Livewire::test('profile-data')
        ->call('save')
        ->assertHasNoErrors()
        ->assertNotDispatched('profile-updated');
});

test('password field is shown only when the email differs', function () {
    Livewire::test('profile-data')
        ->assertDontSeeHtml('id="current_password"')
        ->set('form.email', 'new@example.com')
        ->assertSeeHtml('id="current_password"')
        ->set('form.email', 'old@example.com')
        ->assertDontSeeHtml('id="current_password"');
});

test('profile summary re-renders with the new email on profile-updated', function () {
    $summary = Livewire::test('profile-summary')->assertSee('old@example.com');

    $this->user->update(['email' => 'new@example.com']);

    $summary->dispatch('profile-updated')
        ->assertSee('new@example.com')
        ->assertDontSee('old@example.com');
});
