<?php

use App\Models\Employee;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin', 'active' => true]);
    $this->target = User::factory()->create(['role' => 'client', 'active' => true]);
});

function changeRole($test, string $role)
{
    return Livewire::actingAs($test->admin)
        ->test('admin-users-edit')
        ->call('open', $test->target->uuid)
        ->set('selectedRole', $role)
        ->call('changeRole');
}

test('changing role to employee creates an active employee', function () {
    changeRole($this, 'employee')->assertOk();

    expect($this->target->fresh()->role)->toBe('employee')
        ->and($this->target->employee)->not->toBeNull()
        ->and((bool) $this->target->employee->active)->toBeTrue();
});

test('changing role refreshes the users list', function () {
    changeRole($this, 'employee')
        ->assertDispatched('userUpdated')
        ->assertSee('Rola została zmieniona.');
});

test('admin can deactivate another account', function () {
    Livewire::actingAs($this->admin)
        ->test('admin-users-edit')
        ->call('open', $this->target->uuid)
        ->call('startDisable')
        ->assertSet('mode', 'disable')
        ->call('disable')
        ->assertDispatched('userUpdated')
        ->assertSet('mode', 'details');

    expect($this->target->fresh()->active)->toBeFalse();
});

test('admin cannot deactivate their own account', function () {
    Livewire::actingAs($this->admin)
        ->test('admin-users-edit')
        ->call('open', $this->admin->uuid)
        ->assertDontSeeHtml('wire:click="startDisable"')
        ->call('disable')
        ->assertForbidden();
});

test('admin can activate a deactivated account', function () {
    $this->target->update(['active' => false]);

    Livewire::actingAs($this->admin)
        ->test('admin-users-edit')
        ->call('open', $this->target->uuid)
        ->assertSeeHtml('wire:click="enable"')
        ->assertDontSeeHtml('wire:click="startDisable"')
        ->call('enable')
        ->assertDispatched('userUpdated')
        ->assertSet('mode', 'details')
        ->assertDontSeeHtml('wire:click="enable"');

    expect($this->target->fresh()->active)->toBeTrue();
});

test('active account cannot be activated again', function () {
    Livewire::actingAs($this->admin)
        ->test('admin-users-edit')
        ->call('open', $this->target->uuid)
        ->assertDontSeeHtml('wire:click="enable"')
        ->call('enable')
        ->assertForbidden();
});

test('non-admin cannot activate an account', function () {
    $this->target->update(['active' => false]);
    $employee = User::factory()->create(['role' => 'employee', 'active' => true]);

    Livewire::actingAs($employee)
        ->test('admin-users-edit')
        ->set('user', $this->target)
        ->call('enable')
        ->assertForbidden();

    expect($this->target->fresh()->active)->toBeFalse();
});

test('users list opens the edit modal for a row', function () {
    Livewire::actingAs($this->admin)
        ->test('admin-users')
        ->assertSeeHtml("\$dispatch('editUser', { uuid: '{$this->target->uuid}' })")
        ->assertSeeLivewire('admin-users-edit');
});

test('changing role away from employee deactivates the employee', function () {
    changeRole($this, 'employee');
    changeRole($this, 'client');

    expect($this->target->fresh()->role)->toBe('client')
        ->and((bool) $this->target->fresh()->employee->active)->toBeFalse();
});

test('becoming an employee again reactivates the old employee row', function () {
    changeRole($this, 'employee');
    $uuid = $this->target->fresh()->employee->uuid;

    changeRole($this, 'client');
    changeRole($this, 'employee');

    expect(Employee::where('user_id', $this->target->uuid)->count())->toBe(1)
        ->and($this->target->fresh()->employee->uuid)->toBe($uuid)
        ->and((bool) $this->target->fresh()->employee->active)->toBeTrue();
});

test('unknown role is ignored', function () {
    changeRole($this, 'superuser');

    expect($this->target->fresh()->role)->toBe('client')
        ->and($this->target->employee)->toBeNull();
});

test('admin cannot change their own role', function () {
    Livewire::actingAs($this->admin)
        ->test('admin-users-edit')
        ->call('open', $this->admin->uuid)
        ->set('selectedRole', 'client')
        ->call('changeRole')
        ->assertForbidden();

    expect($this->admin->fresh()->role)->toBe('admin');
});

test('non-admin cannot open the edit modal', function () {
    Livewire::actingAs($this->target)
        ->test('admin-users-edit')
        ->call('open', $this->admin->uuid)
        ->assertForbidden();
});

test('deactivated employee is not offered in the booking modal', function () {
    $service = Service::forceCreate([
        'uuid' => (string) Str::uuid(),
        'name' => 'Strzyzenie',
        'description' => 'Opis uslugi',
        'duration' => 60,
        'price' => 100,
        'active' => 1,
    ]);

    changeRole($this, 'employee');
    $employee = $this->target->fresh()->employee;
    DB::table('employee_services')->insert([
        'uuid' => (string) Str::uuid(),
        'employee_id' => $employee->uuid,
        'service_id' => $service->uuid,
    ]);

    $client = User::factory()->create(['role' => 'client', 'active' => true]);
    $booking = fn () => Livewire::actingAs($client)->test('reserve-service')->call('serviceChosen', $service->uuid);

    expect($booking()->get('employees')->pluck('uuid')->all())->toBe([$employee->uuid]);

    changeRole($this, 'client');

    expect($booking()->get('employees'))->toBeEmpty();
});

function editContact($test, string $email, string $phone)
{
    return Livewire::actingAs($test->admin)
        ->test('admin-users-edit')
        ->call('open', $test->target->uuid)
        ->set('email', $email)
        ->set('phone', $phone)
        ->call('saveContact');
}

test('modal is prefilled with the user\'s email and phone', function () {
    $this->target->update(['email' => 'old@example.com', 'phone' => '123456789']);

    Livewire::actingAs($this->admin)
        ->test('admin-users-edit')
        ->call('open', $this->target->uuid)
        ->assertSet('email', 'old@example.com')
        ->assertSet('phone', '123456789');
});

test('admin can change a user\'s email and phone', function () {
    editContact($this, 'new@example.com', '+48 987 654-321')
        ->assertHasNoErrors()
        ->assertDispatched('userUpdated')
        ->assertSee('Dane kontaktowe zostały zapisane.');

    // no password needed, and the phone is normalized like in the profile form
    expect($this->target->fresh()->email)->toBe('new@example.com')
        ->and($this->target->fresh()->phone)->toBe('+48987654321');
});

test('email already used by another user is rejected', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $email = $this->target->email;

    editContact($this, 'taken@example.com', '123456789')
        ->assertHasErrors(['email' => 'unique'])
        ->assertNotDispatched('userUpdated');

    expect($this->target->fresh()->email)->toBe($email);
});

test('invalid contact data is rejected', function (string $email, string $phone, string $field) {
    $before = $this->target->only(['email', 'phone']);

    editContact($this, $email, $phone)->assertHasErrors([$field]);

    expect($this->target->fresh()->only(['email', 'phone']))->toBe($before);
})->with([
    'bad email' => ['nie-email', '123456789', 'email'],
    'empty email' => ['', '123456789', 'email'],
    'bad phone' => ['ok@example.com', '12345', 'phone'],
    'empty phone' => ['ok@example.com', '', 'phone'],
]);

test('saving unchanged contact data does nothing', function () {
    $this->target->update(['phone' => '123456789']);

    editContact($this, $this->target->email, '123456789')
        ->assertHasNoErrors()
        ->assertNotDispatched('userUpdated')
        ->assertDontSee('Dane kontaktowe zostały zapisane.');
});

// makes the target an employee who offers one service and returns a closure counting
// the employees a client is offered for it in the booking modal
function offeredEmployees($test): Closure
{
    $service = Service::forceCreate([
        'uuid' => (string) Str::uuid(),
        'name' => 'Strzyzenie',
        'description' => 'Opis uslugi',
        'duration' => 60,
        'price' => 100,
        'active' => 1,
    ]);

    changeRole($test, 'employee');
    DB::table('employee_services')->insert([
        'uuid' => (string) Str::uuid(),
        'employee_id' => $test->target->fresh()->employee->uuid,
        'service_id' => $service->uuid,
    ]);

    $client = User::factory()->create(['role' => 'client', 'active' => true]);

    return fn () => Livewire::actingAs($client)->test('reserve-service')->call('serviceChosen', $service->uuid)->get('employees')->count();
}

test('deactivating an account hides its employee profile from booking until it is activated again', function () {
    $offered = offeredEmployees($this);
    expect($offered())->toBe(1);

    Livewire::actingAs($this->admin)
        ->test('admin-users-edit')
        ->call('open', $this->target->uuid)
        ->call('startDisable')
        ->assertSee('Profil pracownika tego konta zniknie z listy przy rezerwacji.')
        ->call('disable');

    expect((bool) $this->target->fresh()->employee->active)->toBeFalse()
        ->and($offered())->toBe(0);

    Livewire::actingAs($this->admin)
        ->test('admin-users-edit')
        ->call('open', $this->target->uuid)
        ->call('enable')
        ->assertSee('Konto zostało aktywowane.');

    expect((bool) $this->target->fresh()->employee->active)->toBeTrue()
        ->and($offered())->toBe(1);
});

test('an employee whose account is inactive is not offered even with an active profile', function () {
    $offered = offeredEmployees($this);

    // data from before deactivation switched the profile off too
    $this->target->update(['active' => false]);

    expect((bool) $this->target->fresh()->employee->active)->toBeTrue()
        ->and($offered())->toBe(0);
});

test('an inactive account given the employee role stays unbookable', function () {
    $this->target->update(['active' => false]);

    changeRole($this, 'employee');

    expect((bool) $this->target->fresh()->employee->active)->toBeFalse();
});

test('changing an employee\'s role says how many upcoming visits stay booked', function () {
    offeredEmployees($this);
    $employee = $this->target->fresh()->employee;
    DB::table('reservations')->insert([
        'uuid' => (string) Str::uuid(),
        'user_id' => $this->admin->uuid,
        'employee_id' => $employee->uuid,
        'service_id' => $employee->services()->value('services.uuid'),
        'reservation_date' => now()->addWeek()->toDateString(),
        'start_time' => '10:00:00',
        'end_time' => '11:00:00',
        'status' => 'confirmed',
    ]);

    Livewire::actingAs($this->admin)
        ->test('admin-users-edit')
        ->call('open', $this->target->uuid)
        ->set('selectedRole', 'client')
        ->assertSee('Nadchodzące wizyty u tego pracownika (1)');
});

test('users list shows polish role names and searches by surname', function () {
    $this->target->update(['surname' => 'Zdzisławska']);

    $list = Livewire::actingAs($this->admin)
        ->test('admin-users')
        ->assertSeeHtml('md:p-2">Klient</td>')
        ->assertSeeHtml('md:p-2">Administrator</td>')
        ->assertDontSeeHtml('md:p-2">client</td>')
        ->set('search', 'Zdzisław');

    expect($list->get('users')->pluck('uuid')->all())->toBe([$this->target->uuid]);
});
