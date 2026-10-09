<?php

use App\Models\User;
use Illuminate\Auth\Events\OtherDeviceLogout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    // Factory password is 'password'
    $this->user = User::factory()->create();

    $this->actingAs($this->user);
});

test('password can be changed with the correct current password', function () {
    Livewire::test('profile-change-password')
        ->set('form.current_password', 'password')
        ->set('form.password', 'new-password')
        ->set('form.password_confirmation', 'new-password')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('Haslo zostalo zmienione');

    expect(Hash::check('new-password', $this->user->fresh()->password))->toBeTrue();
});

test('each input is bound to its own form property', function () {
    // ->set() skips the HTML, so check the bindings directly
    Livewire::test('profile-change-password')
        ->assertSeeHtml('wire:model="form.current_password"')
        ->assertSeeHtml('wire:model="form.password"')
        ->assertSeeHtml('wire:model="form.password_confirmation"');
});

test('form is cleared after a successful change', function () {
    Livewire::test('profile-change-password')
        ->set('form.current_password', 'password')
        ->set('form.password', 'new-password')
        ->set('form.password_confirmation', 'new-password')
        ->call('save')
        ->assertSet('form.current_password', '')
        ->assertSet('form.password', '')
        ->assertSet('form.password_confirmation', '');
});

test('other sessions are logged out after a change', function () {
    Event::fake([OtherDeviceLogout::class]);

    Livewire::test('profile-change-password')
        ->set('form.current_password', 'password')
        ->set('form.password', 'new-password')
        ->set('form.password_confirmation', 'new-password')
        ->call('save')
        ->assertHasNoErrors();

    Event::assertDispatched(OtherDeviceLogout::class);
});

test('current password is required', function () {
    Livewire::test('profile-change-password')
        ->set('form.password', 'new-password')
        ->set('form.password_confirmation', 'new-password')
        ->call('save')
        ->assertHasErrors(['form.current_password' => 'required']);

    expect(Hash::check('password', $this->user->fresh()->password))->toBeTrue();
});

test('wrong current password is rejected', function () {
    Livewire::test('profile-change-password')
        ->set('form.current_password', 'wrong-password')
        ->set('form.password', 'new-password')
        ->set('form.password_confirmation', 'new-password')
        ->call('save')
        ->assertHasErrors(['form.current_password' => 'current_password'])
        ->assertDontSee('Haslo zostalo zmienione');

    expect(Hash::check('password', $this->user->fresh()->password))->toBeTrue();
});

test('new password is required', function () {
    Livewire::test('profile-change-password')
        ->set('form.current_password', 'password')
        ->call('save')
        ->assertHasErrors(['form.password' => 'required']);
});

test('new password must be confirmed', function () {
    Livewire::test('profile-change-password')
        ->set('form.current_password', 'password')
        ->set('form.password', 'new-password')
        ->set('form.password_confirmation', 'different-password')
        ->call('save')
        ->assertHasErrors(['form.password' => 'confirmed']);

    expect(Hash::check('password', $this->user->fresh()->password))->toBeTrue();
});

test('new password must have at least 8 characters', function () {
    Livewire::test('profile-change-password')
        ->set('form.current_password', 'password')
        ->set('form.password', 'short')
        ->set('form.password_confirmation', 'short')
        ->call('save')
        ->assertHasErrors('form.password')
        ->assertSee('Haslo musi miec co najmniej 8 znakow');
});

test('production requires a strong password', function (string $weak) {
    // Password::defaults() checks the environment when the rule is built
    app()->detectEnvironment(fn () => 'production');
    Http::fake(); // uncompromised() would otherwise call the haveibeenpwned API

    Livewire::test('profile-change-password')
        ->set('form.current_password', 'password')
        ->set('form.password', $weak)
        ->set('form.password_confirmation', $weak)
        ->call('save')
        ->assertHasErrors('form.password');

    expect(Hash::check('password', $this->user->fresh()->password))->toBeTrue();
})->with([
    'no uppercase' => 'lowercase1!',
    'no lowercase' => 'UPPERCASE1!',
    'no digit' => 'NoDigits!!',
    'no symbol' => 'NoSymbol123',
]);
