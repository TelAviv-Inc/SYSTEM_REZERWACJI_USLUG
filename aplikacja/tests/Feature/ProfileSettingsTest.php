<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    // Factory password is 'password'
    // Factory picks a random role, employees see a different view
    $this->user = User::factory()->create(['active' => 1, 'role' => 'client']);

    $this->actingAs($this->user);
});

// Reservation/Service factories are not usable yet, so insert the rows directly
function createReservation(string $userId, string $employeeId): string
{
    $categoryId = (string) Str::uuid();
    $serviceId = (string) Str::uuid();
    $reservationId = (string) Str::uuid();

    DB::table('service_categories')->insert([
        'uuid' => $categoryId,
        'name' => 'Kategoria '.$categoryId,
        'description' => 'Opis',
        'icon' => 'fa-solid fa-scissors',
    ]);

    DB::table('services')->insert([
        'uuid' => $serviceId,
        'category_id' => $categoryId,
        'name' => 'Usługa',
        'slug' => 'usluga-'.$serviceId,
        'description' => 'Opis',
    ]);

    DB::table('reservations')->insert([
        'uuid' => $reservationId,
        'user_id' => $userId,
        'employee_id' => $employeeId,
        'service_id' => $serviceId,
        'reservation_date' => now()->addDay()->toDateString(),
        'start_time' => '10:00',
        'end_time' => '11:00',
    ]);

    return $reservationId;
}

function createEmployee(User $user): string
{
    $employeeId = (string) Str::uuid();

    DB::table('employees')->insert([
        'uuid' => $employeeId,
        'user_id' => $user->uuid,
    ]);

    return $employeeId;
}


test('password input is bound to the password property', function () {
    Livewire::test('profile-settings')
        ->assertSeeHtml('wire:model="password"');
});

test('both actions ask for confirmation', function () {
    Livewire::test('profile-settings')
        ->assertSeeHtml('wire:click="deactivate"')
        ->assertSeeHtml('wire:click="delete"')
        ->assertSeeHtml('wire:confirm="Czy na pewno chcesz dezaktywować konto?"')
        ->assertSeeHtml('wire:confirm="Czy na pewno chcesz usunąć konto? Tej operacji nie można cofnąć."');
});

test('account can be deactivated', function () {
    Livewire::test('profile-settings')
        ->call('deactivate')
        ->assertRedirect('/');

    expect($this->user->fresh()->active)->toBeFalse();
    $this->assertGuest();
});

test('deactivated account is kept in the database', function () {
    Livewire::test('profile-settings')
        ->call('deactivate');

    $this->assertDatabaseHas('users', ['uuid' => $this->user->uuid, 'active' => 0]);
});

test('account can be deleted with the correct password', function () {
    Livewire::test('profile-settings')
        ->set('password', 'password')
        ->call('delete')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $this->assertDatabaseMissing('users', ['uuid' => $this->user->uuid]);
    $this->assertGuest();
});

test('password is required to delete the account', function () {
    Livewire::test('profile-settings')
        ->call('delete')
        ->assertHasErrors(['password' => 'required'])
        ->assertNoRedirect();

    $this->assertDatabaseHas('users', ['uuid' => $this->user->uuid]);
    $this->assertAuthenticatedAs($this->user);
});

test('wrong password does not delete the account', function () {
    Livewire::test('profile-settings')
        ->set('password', 'wrong-password')
        ->call('delete')
        ->assertHasErrors(['password' => 'current_password'])
        ->assertNoRedirect();

    $this->assertDatabaseHas('users', ['uuid' => $this->user->uuid]);
    $this->assertAuthenticatedAs($this->user);
});

test('deleting the account removes its reservations', function () {
    $employeeUser = User::factory()->create(['role' => 'employee']);
    $reservationId = createReservation($this->user->uuid, createEmployee($employeeUser));

    Livewire::test('profile-settings')
        ->set('password', 'password')
        ->call('delete')
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('users', ['uuid' => $this->user->uuid]);
    $this->assertDatabaseMissing('reservations', ['uuid' => $reservationId]);
});

test('employee sees an info message instead of the actions', function () {
    $this->user->update(['role' => 'employee']);

    Livewire::test('profile-settings')
        ->assertSee('Twoje konto jest zarzadzane przez')
        ->assertDontSeeHtml('wire:click="deactivate"')
        ->assertDontSeeHtml('wire:click="delete"')
        ->assertDontSeeHtml('wire:model="password"');
});

test('employee cannot delete the account', function () {
    $this->user->update(['role' => 'employee']);
    $employeeId = createEmployee($this->user);

    // Call the action directly, the buttons are hidden but the method is still public
    Livewire::test('profile-settings')
        ->set('password', 'password')
        ->call('delete')
        ->assertNoRedirect();

    $this->assertDatabaseHas('users', ['uuid' => $this->user->uuid]);
    $this->assertDatabaseHas('employees', ['uuid' => $employeeId]);
    $this->assertAuthenticatedAs($this->user);
});

test('employee cannot deactivate the account', function () {
    $this->user->update(['role' => 'employee']);

    Livewire::test('profile-settings')
        ->call('deactivate')
        ->assertNoRedirect();

    expect($this->user->fresh()->active)->toBeTrue();
    $this->assertAuthenticatedAs($this->user);
});
