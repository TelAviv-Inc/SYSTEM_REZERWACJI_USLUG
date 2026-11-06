<?php

use App\Models\Employee;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

// "now" is Thursday 2026-10-15. October has 22 working days (Mon-Fri) and Anna works
// 9-17 on each of them. Ewa's visits must never show up in Anna's stats.
beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-10-15 10:00'));

    [$this->c1, $this->c2, $this->c3] = User::factory()->count(3)->create(['role' => 'client', 'active' => true])->all();
    $this->c1->update(['name' => 'Klementyna', 'surname' => 'Klientowska']);

    $hair = ServiceCategory::create(['name' => 'Fryzjer', 'description' => 'Opis', 'icon' => 'fa-solid fa-scissors']);
    $nails = ServiceCategory::create(['name' => 'Paznokcie', 'description' => 'Opis', 'icon' => 'fa-solid fa-spa']);

    $this->cut = statsService($hair, 'Strzyzenie', 100, 60);
    $this->color = statsService($hair, 'Koloryzacja', 200, 90);
    $this->manicure = statsService($nails, 'Manicure', 80, 60);

    $this->anna = statsEmployee('Anna');
    $this->ewa = statsEmployee('Ewa');
    $this->anna->services()->attach([$this->cut->uuid => ['uuid' => (string) Str::uuid()], $this->color->uuid => ['uuid' => (string) Str::uuid()]]);
    $this->ewa->services()->attach($this->manicure->uuid, ['uuid' => (string) Str::uuid()]);

    foreach (range(1, 5) as $day) {
        DB::table('employee_availabilities')->insert([
            'uuid' => (string) Str::uuid(),
            'employee_id' => $this->anna->uuid,
            'day_of_week' => $day,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);
    }

    statsVisit($this->anna, $this->cut, $this->c1, '2026-10-05', '10:00', 60, 'completed');
    statsVisit($this->anna, $this->color, $this->c2, '2026-10-06', '12:00', 90, 'completed');
    statsVisit($this->anna, $this->cut, $this->c1, '2026-10-07', '10:00', 60, 'cancelled');
    statsVisit($this->anna, $this->cut, $this->c1, '2026-10-20', '10:00', 60, 'confirmed');
    // the previous period (31 days: 31.08 - 30.09)
    statsVisit($this->anna, $this->cut, $this->c1, '2026-09-10', '10:00', 60, 'completed');
    // another employee's busy month
    statsVisit($this->ewa, $this->manicure, $this->c3, '2026-10-06', '10:00', 60, 'completed');
    statsVisit($this->ewa, $this->manicure, $this->c3, '2026-10-08', '10:00', 60, 'completed');
});

function statsService(ServiceCategory $category, string $name, int $price, int $duration): Service
{
    return Service::forceCreate([
        'uuid' => (string) Str::uuid(),
        'category_id' => $category->uuid,
        'name' => $name,
        'description' => 'Opis',
        'duration' => $duration,
        'price' => $price,
        'active' => 1,
    ]);
}

function statsEmployee(string $name): Employee
{
    return Employee::forceCreate([
        'uuid' => (string) Str::uuid(),
        'user_id' => User::factory()->create(['role' => 'employee', 'active' => true, 'name' => $name])->uuid,
        'active' => 1,
    ]);
}

function statsVisit(Employee $employee, Service $service, User $client, string $date, string $start, int $minutes, string $status): void
{
    DB::table('reservations')->insert([
        'uuid' => (string) Str::uuid(),
        'user_id' => $client->uuid,
        'employee_id' => $employee->uuid,
        'service_id' => $service->uuid,
        'reservation_date' => $date,
        'start_time' => "{$start}:00",
        'end_time' => Carbon::parse($start)->addMinutes($minutes)->format('H:i:s'),
        'status' => $status,
        'price' => $service->price,
    ]);
}

function statsTab(Employee $employee)
{
    return Livewire::actingAs($employee->user)->test('employee-stats');
}

test('the summary counts only the employee\'s own visits in the period', function () {
    $summary = statsTab($this->anna)->get('summary');

    expect($summary)
        ->total->toBe(4)
        ->reservations->toBe(3)
        ->completed->toBe(2)
        ->cancelled->toBe(1)
        ->income->toBe(300.0)
        ->expected->toBe(100.0)
        ->lost->toBe(100.0)
        ->avg_ticket->toBe(150.0)
        ->cancellation_rate->toBe(25.0)
        ->clients->toBe(2)
        // Klementyna had a visit with Anna in September already
        ->new_clients->toBe(1)
        ->booked_minutes->toBe(210);
});

test('the tiles compare with the previous period and show the schedule utilization', function () {
    $kpis = collect(statsTab($this->anna)->get('kpis'))->keyBy('label');

    expect($kpis['Przychód'])->toMatchArray(['value' => '300,00 zł', 'change' => 200.0])
        ->and($kpis['Wizyty'])->toMatchArray(['value' => 3, 'change' => 200.0])
        // 210 booked minutes of 22 days x 8 h
        ->and($kpis['Obłożenie grafiku'])->toMatchArray(['value' => '2%', 'hint' => '3,5 h z 176 h']);
});

test('utilization is empty without working hours', function () {
    $kpi = collect(statsTab($this->ewa)->get('kpis'))->firstWhere('label', 'Obłożenie grafiku');

    expect($kpi)->toMatchArray(['value' => '—', 'change' => null, 'hint' => 'brak godzin pracy']);
});

test('the services table lists only the employee\'s services', function () {
    $rows = statsTab($this->anna)->get('rows')->keyBy('name');

    expect($rows->keys()->all())->toBe(['Strzyzenie', 'Koloryzacja'])
        ->and($rows['Strzyzenie'])->reservations->toBe(2)->cancelled->toBe(1)->income->toBe(100.0)
        ->and($rows['Koloryzacja'])->reservations->toBe(1)->income->toBe(200.0)->share->toBe(66.7);
});

test('top clients are ordered by visits and skip cancelled ones', function () {
    $clients = statsTab($this->anna)->get('topClients');

    expect($clients)->toHaveCount(2)
        ->and($clients[0])->name->toBe('Klementyna Klientowska')->visits->toBe(2)->income->toBe(100.0)->last->toBe('2026-10-20')
        ->and($clients[1])->visits->toBe(1)->income->toBe(200.0);
});

test('all-time totals are not limited by the period', function () {
    expect(statsTab($this->anna)->get('allTime'))
        ->completed->toBe(3)
        ->income->toBe(400.0)
        ->clients->toBe(2)
        ->returning->toBe(1);
});

test('a preset period sets the dates', function () {
    statsTab($this->anna)
        ->set('period', 'last_month')
        ->assertSet('from', '2026-09-01')
        ->assertSet('to', '2026-09-30')
        ->assertSee('01.09.2026 – 30.09.2026');
});

test('own dates switch the period to custom', function () {
    $component = statsTab($this->anna)
        ->set('from', '2026-10-06')
        ->set('to', '2026-10-06')
        ->assertSet('period', 'custom');

    expect($component->get('summary'))->reservations->toBe(1)->income->toBe(200.0);
});

test('invalid dates show an error instead of the stats', function () {
    statsTab($this->anna)
        ->set('from', '2026-10-20')
        ->set('to', '2026-10-01')
        ->assertSee('Data początkowa nie może być późniejsza niż końcowa.')
        ->assertDontSee('Twoje statystyki');
});

test('an unknown preset from the URL falls back to this month', function () {
    Livewire::actingAs($this->anna->user)
        ->withQueryParams(['stats_period' => 'forever'])
        ->test('employee-stats')
        ->assertSet('period', 'this_month')
        ->assertSet('from', '2026-10-01')
        ->assertSet('to', '2026-10-31');
});

test('an admin without an employee profile sees a message', function () {
    $admin = User::factory()->create(['role' => 'admin', 'active' => true]);

    Livewire::actingAs($admin)
        ->test('employee-stats')
        ->assertSee('To konto nie ma profilu pracownika');
});

test('the stats tab renders in the employee panel', function () {
    $this->actingAs($this->anna->user)
        ->get(route('dashboard.employee', ['tab' => 'stats']))
        ->assertOk()
        ->assertSee('Twoje statystyki')
        ->assertSee('Koloryzacja')
        ->assertDontSee('Manicure');
});
