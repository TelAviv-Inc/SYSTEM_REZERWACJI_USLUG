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
