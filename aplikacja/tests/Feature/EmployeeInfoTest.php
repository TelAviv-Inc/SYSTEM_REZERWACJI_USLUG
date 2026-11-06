<?php

use App\Models\Employee;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

// "now" is Thursday 2026-10-15 10:00; the employee works Mon-Fri 9-17
beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-10-15 10:00'));

    $this->client = User::factory()->create(['role' => 'client', 'active' => true, 'name' => 'Klementyna', 'surname' => 'Klientowska', 'phone' => '+48600100200']);

    $this->employee = Employee::forceCreate([
        'uuid' => (string) Str::uuid(),
        'user_id' => User::factory()->create(['role' => 'employee', 'active' => true, 'name' => 'Anna', 'surname' => 'Nowak'])->uuid,
        'active' => 1,
    ]);

    $this->service = Service::forceCreate([
        'uuid' => (string) Str::uuid(),
        'category_id' => ServiceCategory::create(['name' => 'Fryzjer', 'description' => 'Opis', 'icon' => 'fa-scissors'])->uuid,
        'name' => 'Strzyzenie',
        'description' => 'Opis',
        'duration' => 60,
        'price' => 120,
        'active' => 1,
    ]);
    $this->employee->services()->attach($this->service->uuid, ['uuid' => (string) Str::uuid()]);

    foreach (range(1, 5) as $day) {
        DB::table('employee_availabilities')->insert([
            'uuid' => (string) Str::uuid(),
            'employee_id' => $this->employee->uuid,
            'day_of_week' => $day,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);
    }
});

function infoVisit(Employee $employee, Service $service, User $client, string $date, string $start, string $status): void
{
    DB::table('reservations')->insert([
        'uuid' => (string) Str::uuid(),
        'user_id' => $client->uuid,
        'employee_id' => $employee->uuid,
        'service_id' => $service->uuid,
        'reservation_date' => $date,
        'start_time' => "{$start}:00",
        'end_time' => Carbon::parse($start)->addHour()->format('H:i:s'),
        'status' => $status,
        'price' => $service->price,
    ]);
}

function infoTab(Employee $employee)
{
    return Livewire::actingAs($employee->user)->test('employee-info');
}

test('it shows the profile, services and working hours', function () {
    infoTab($this->employee)
        ->assertSee('Anna Nowak')
        ->assertSee('Dostępny do rezerwacji')
        ->assertSee('Strzyzenie')
        ->assertSee('120,00 zł')
        ->assertSee('09:00–17:00')
        ->assertSee('Wolne')
        ->assertSet('weeklyMinutes', 2400)
        ->assertSee('40 h')
        ->assertDontSee('Nie masz przypisanych usług');
});

test('it counts today\'s plan and the visits waiting for the employee', function () {
    infoVisit($this->employee, $this->service, $this->client, '2026-10-15', '09:00', 'confirmed'); // started, to complete
    infoVisit($this->employee, $this->service, $this->client, '2026-10-15', '12:00', 'pending');
    infoVisit($this->employee, $this->service, $this->client, '2026-10-15', '14:00', 'cancelled');
    infoVisit($this->employee, $this->service, $this->client, '2026-10-20', '10:00', 'pending');
    infoVisit($this->employee, $this->service, $this->client, '2026-10-25', '10:00', 'confirmed'); // Sunday, outside hours

    $component = infoTab($this->employee);

    expect($component->get('today'))->toHaveCount(2)
        ->and($component->get('counts'))->toBe(['upcoming' => 3, 'week' => 2, 'toConfirm' => 2, 'toComplete' => 1])
        ->and($component->get('nextVisit')->full_date_time)->toBe('15.10.2026 12:00-13:00')
        ->and($component->get('outsideHours'))->toBe(1);

    $component
        ->assertSee('+48600100200')
        ->assertSee('Nadchodzące wizyty poza godzinami pracy')
        ->assertSee('reservation_status=to_confirm', false)
        ->assertSee('reservation_status=to_complete', false);
});

test('it warns when the employee can\'t be booked', function () {
    $this->employee->update(['active' => 0]);
    $this->employee->services()->detach();
    $this->employee->availability()->delete();

    infoTab($this->employee)
        ->assertSee('Twój profil pracownika jest nieaktywny')
        ->assertSee('Niedostępny do rezerwacji')
        ->assertSee('Nie masz przypisanych usług')
        ->assertSee('Nie masz ustalonych godzin pracy');
});

test('one-off working dates from today on are listed', function () {
    foreach (['2026-10-10', '2026-10-24'] as $date) {
        DB::table('employee_availabilities')->insert([
            'uuid' => (string) Str::uuid(),
            'employee_id' => $this->employee->uuid,
            'specific_date' => $date,
            'start_time' => '10:00:00',
            'end_time' => '14:00:00',
        ]);
    }

    infoTab($this->employee)
        ->assertSee('Dodatkowe terminy')
        ->assertSee('24.10.2026')
        ->assertDontSee('10.10.2026');
});

test('an admin without an employee profile sees a message', function () {
    $admin = User::factory()->create(['role' => 'admin', 'active' => true]);

    Livewire::actingAs($admin)
        ->test('employee-info')
        ->assertSee('To konto nie ma profilu pracownika');
});

test('the info tab renders in the employee panel', function () {
    $this->actingAs($this->employee->user)
        ->get(route('dashboard.employee', ['tab' => 'info']))
        ->assertOk()
        ->assertSee('Twoje usługi')
        ->assertSee('Godziny pracy');
});

test('a client can\'t open the employee panel', function () {
    $this->actingAs($this->client)
        ->get(route('dashboard.employee', ['tab' => 'info']))
        ->assertForbidden();
});
