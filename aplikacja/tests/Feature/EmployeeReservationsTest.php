<?php

use App\Models\Employee;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

// "now" is Monday 2026-10-12 08:00 - same setup as AdminReservationsTest
beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-10-12 08:00'));

    $this->client = User::factory()->create(['role' => 'client', 'active' => true, 'name' => 'Klementyna', 'surname' => 'Klientowska']);
    $this->otherClient = User::factory()->create(['role' => 'client', 'active' => true, 'name' => 'Bonifacy', 'surname' => 'Bywalski']);

    $this->employee = employeeProfile();
    $this->otherEmployee = employeeProfile();

    $this->haircut = offeredService($this->employee, 'Fryzjer', 'Strzyzenie');
    $this->massage = offeredService($this->employee, 'Masaz', 'Masaz plecow');
});

function employeeProfile(): Employee
{
    return Employee::forceCreate([
        'uuid' => (string) Str::uuid(),
        'user_id' => User::factory()->create(['role' => 'employee', 'active' => true])->uuid,
        'active' => 1,
    ]);
}

function offeredService(Employee $employee, string $category, string $name): Service
{
    $service = Service::forceCreate([
        'uuid' => (string) Str::uuid(),
        'category_id' => ServiceCategory::create(['name' => $category, 'description' => 'Opis', 'icon' => 'fa-scissors'])->uuid,
        'name' => $name,
        'description' => 'Opis uslugi',
        'duration' => 60,
        'price' => 100,
        'active' => 1,
    ]);

    $employee->services()->attach($service->uuid, ['uuid' => (string) Str::uuid()]);

    return $service;
}

function visitOf(Employee $employee, Service $service, User $client, string $date, string $status = 'pending'): Reservation
{
    $uuid = (string) Str::uuid();

    DB::table('reservations')->insert([
        'uuid' => $uuid,
        'user_id' => $client->uuid,
        'employee_id' => $employee->uuid,
        'service_id' => $service->uuid,
        'reservation_date' => $date,
        'start_time' => '10:00:00',
        'end_time' => '11:00:00',
        'status' => $status,
        'price' => 100,
    ]);

    return Reservation::find($uuid);
}

function visitsTab(Employee $employee)
{
    return Livewire::actingAs($employee->user)->test('employee-reservations');
}

function listedUuids($component): array
{
    return $component->get('reservations')->pluck('uuid')->all();
}

test('the tab lists only the visits of the logged in employee', function () {
    $own = visitOf($this->employee, $this->haircut, $this->client, '2026-10-13');
    visitOf($this->otherEmployee, $this->haircut, $this->otherClient, '2026-10-13');

    $component = visitsTab($this->employee)
        ->assertSee('Klementyna Klientowska')
        ->assertSee('Strzyzenie')
        ->assertSee('100,00 zł')
        ->assertSee('wire:model.live="category"', false)
        ->assertDontSee('Bonifacy');

    expect(listedUuids($component))->toBe([$own->uuid]);
});

test('the filters narrow the list', function () {
    $haircut = visitOf($this->employee, $this->haircut, $this->client, '2026-10-13', 'confirmed');
    $massage = visitOf($this->employee, $this->massage, $this->otherClient, '2026-10-20');

    $filtered = fn (string $property, string $value) => listedUuids(visitsTab($this->employee)->set($property, $value));

    expect($filtered('search', 'Bywalski'))->toBe([$massage->uuid])
        ->and($filtered('status', 'confirmed'))->toBe([$haircut->uuid])
        ->and($filtered('service', $this->massage->uuid))->toBe([$massage->uuid])
        ->and($filtered('category', $this->haircut->category_id))->toBe([$haircut->uuid])
        ->and($filtered('from', '2026-10-14'))->toBe([$massage->uuid])
        ->and($filtered('to', '2026-10-14'))->toBe([$haircut->uuid]);
});

test('changing the category clears the service and limits the service options', function () {
    visitsTab($this->employee)
        ->set('service', $this->massage->uuid)
        ->set('category', $this->haircut->category_id)
        ->assertSet('service', '')
        ->assertSee('Strzyzenie')
        ->assertDontSee('Masaz plecow');
});

test('a date range that ends before it starts shows an error instead of the list', function () {
    visitOf($this->employee, $this->haircut, $this->client, '2026-10-13');

    visitsTab($this->employee)
        ->set('from', '2026-10-20')
        ->set('to', '2026-10-01')
        ->assertSee('Data początkowa nie może być późniejsza niż końcowa.')
        ->assertDontSee('Klementyna');
});

test('the banner counts only own visits waiting for completion and opens them', function () {
    $waiting = visitOf($this->employee, $this->haircut, $this->client, '2026-10-05', 'confirmed');
    visitOf($this->employee, $this->haircut, $this->client, '2026-10-13');
    visitOf($this->otherEmployee, $this->haircut, $this->client, '2026-10-05', 'confirmed');

    $component = visitsTab($this->employee)
        ->assertSee('czekają na zakończenie: 1.')
        ->call('showToComplete')
        ->assertDontSee('czekają na zakończenie');

    expect(listedUuids($component))->toBe([$waiting->uuid]);
});

test('an employee can open and confirm their own visit but cannot move it', function () {
    $visit = visitOf($this->employee, $this->haircut, $this->client, '2026-10-13');

    Livewire::actingAs($this->employee->user)
        ->test('reservation-edit')
        ->call('open', $visit->uuid)
        ->assertSee('Klementyna')
        ->assertDontSee('Zmień termin')
        ->call('confirm')
        ->assertDispatched('reservationUpdated');

    expect($visit->fresh()->status)->toBe('confirmed');
});

test('nobody else can open a visit', function () {
    $visit = visitOf($this->employee, $this->haircut, $this->client, '2026-10-13');

    foreach ([$this->otherEmployee->user, $this->otherClient] as $user) {
        Livewire::actingAs($user)
            ->test('reservation-edit')
            ->call('open', $visit->uuid)
            ->assertNotFound();
    }
});

test('an account without an employee profile sees a message instead of a crash', function () {
    Livewire::actingAs(User::factory()->create(['role' => 'admin', 'active' => true]))
        ->test('employee-reservations')
        ->assertOk()
        ->assertSee('To konto nie ma profilu pracownika');
});

test('the panel page renders the visits tab', function () {
    visitOf($this->employee, $this->haircut, $this->client, '2026-10-13');

    $this->actingAs($this->employee->user)
        ->get(route('dashboard.employee'))
        ->assertOk()
        ->assertSee('Klementyna Klientowska');
});
