<?php

use App\Models\Employee;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Support\Report;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

// "now" is Thursday 2026-10-15. October has 22 working days (Mon-Fri), Anna works
// 9-17 on each of them, Ewa has no schedule. Every number below is worked out by hand.
beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-10-15 10:00'));

    $this->admin = User::factory()->create(['role' => 'admin', 'active' => true]);
    $this->clients = User::factory()->count(3)->create(['role' => 'client', 'active' => true]);

    $this->hair = ServiceCategory::create(['name' => 'Fryzjer', 'description' => 'Opis', 'icon' => 'fa-solid fa-scissors']);
    $this->nails = ServiceCategory::create(['name' => 'Paznokcie', 'description' => 'Opis', 'icon' => 'fa-solid fa-spa']);

    $this->cut = reportService($this->hair, 'Strzyzenie', 100, 60);
    $this->color = reportService($this->hair, 'Koloryzacja', 200, 90);
    $this->manicure = reportService($this->nails, 'Manicure', 80, 60);
    $this->pedicure = reportService($this->nails, 'Pedicure', 90, 60);

    $this->anna = reportEmployee('Anna', 'Nowak');
    $this->ewa = reportEmployee('Ewa', 'Kowalska');
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

    [$c1, $c2, $c3] = $this->clients;
    reportReservation($this->anna, $this->cut, $c1, '2026-10-05', '10:00', 60, 'completed');
    reportReservation($this->anna, $this->color, $c2, '2026-10-06', '12:00', 90, 'completed');
    reportReservation($this->ewa, $this->manicure, $c1, '2026-10-06', '10:00', 60, 'cancelled');
    reportReservation($this->anna, $this->cut, $c3, '2026-10-20', '10:00', 60, 'confirmed');
    reportReservation($this->ewa, $this->manicure, $c2, '2026-10-21', '11:00', 60, 'pending');
    // the previous period (31 days: 31.08 - 30.09)
    reportReservation($this->anna, $this->cut, $c1, '2026-09-10', '10:00', 60, 'completed');
});

function reportService(ServiceCategory $category, string $name, int $price, int $duration): Service
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

function reportEmployee(string $name, string $surname): Employee
{
    return Employee::factory()->create([
        'active' => 1,
        'user_id' => User::factory()->create(['role' => 'employee', 'active' => true, 'name' => $name, 'surname' => $surname])->uuid,
    ]);
}

function reportReservation(Employee $employee, Service $service, User $client, string $date, string $start, int $minutes, string $status): void
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

function october(string $type, ?string $category = null): Report
{
    return new Report($type, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'), $category);
}

function rowFor(Report $report, string $key): array
{
    return $report->rows()->firstWhere('key', $key);
}

test('summary counts reservations, income and clients in the period', function () {
    expect(october('services')->summary())->toMatchArray([
        'total' => 5,
        'reservations' => 4,
        'completed' => 2,
        'confirmed' => 1,
        'pending' => 1,
        'cancelled' => 1,
        'income' => 300.0,
        'expected' => 180.0,
        'lost' => 80.0,
        'avg_ticket' => 150.0,
        'cancellation_rate' => 20.0,
        'clients' => 3,
        'new_clients' => 2,
        'booked_minutes' => 270,
    ]);
});

test('the previous period has the same length and ends the day before', function () {
    $previous = october('services')->previous();

    expect($previous->from->toDateString())->toBe('2026-08-31')
        ->and($previous->to->toDateString())->toBe('2026-09-30')
        ->and($previous->summary()['income'])->toBe(100.0)
        ->and(Report::change(300, 100))->toBe(200.0)
        ->and(Report::change(5, 0))->toBeNull();
});

test('service rows include services without reservations', function () {
    $report = october('services');

    expect($report->rows())->toHaveCount(4)
        ->and(rowFor($report, $this->cut->uuid))->toMatchArray(['reservations' => 2, 'completed' => 1, 'income' => 100.0, 'share' => 33.3, 'top' => 'Anna Nowak', 'employees_count' => 1])
        ->and(rowFor($report, $this->color->uuid))->toMatchArray(['income' => 200.0, 'share' => 66.7, 'avg_ticket' => 200.0])
        ->and(rowFor($report, $this->manicure->uuid))->toMatchArray(['total' => 2, 'reservations' => 1, 'cancelled' => 1, 'cancellation_rate' => 50.0, 'expected' => 80.0, 'lost' => 80.0])
        ->and(rowFor($report, $this->pedicure->uuid))->toMatchArray(['total' => 0, 'income' => 0.0, 'top' => null]);
});

test('category rows add up their services', function () {
    $report = october('categories');

    expect(rowFor($report, $this->hair->uuid))->toMatchArray(['services_count' => 2, 'reservations' => 3, 'income' => 300.0, 'share' => 100.0, 'top' => 'Strzyzenie'])
        ->and(rowFor($report, $this->nails->uuid))->toMatchArray(['services_count' => 2, 'reservations' => 1, 'cancelled' => 1, 'income' => 0.0]);
});

test('employee rows show utilization of the weekly schedule', function () {
    $report = october('employees');

    // 22 working days x 8 h = 10560 min, booked 60 + 90 + 60 = 210 min
    expect(rowFor($report, $this->anna->uuid))->toMatchArray([
        'name' => 'Anna Nowak',
        'reservations' => 3,
        'income' => 300.0,
        'booked_minutes' => 210,
        'available_minutes' => 10560,
        'utilization' => 2.0,
        'top' => 'Strzyzenie',
    ])
        ->and(rowFor($report, $this->ewa->uuid))->toMatchArray(['available_minutes' => 0, 'utilization' => null, 'cancelled' => 1]);
});

test('the category filter narrows reservations and rows', function () {
    $services = october('services', $this->nails->uuid);
    $employees = october('employees', $this->nails->uuid);

    expect($services->summary())->toMatchArray(['total' => 2, 'income' => 0.0, 'expected' => 80.0])
        ->and($services->rows()->pluck('key')->all())->toEqualCanonicalizing([$this->manicure->uuid, $this->pedicure->uuid])
        ->and($employees->rows()->pluck('key')->all())->toBe([$this->ewa->uuid]);
});

test('the timeline uses days, weeks or months depending on the period length', function () {
    $days = october('services')->timeline();

    expect($days['unit'])->toBe('day')
        ->and($days['labels'])->toHaveCount(31)
        ->and($days['labels'][4])->toBe('05.10')
        ->and(array_slice($days['reservations'], 4, 2))->toBe([1, 1])
        ->and(array_slice($days['income'], 4, 2))->toBe([100.0, 200.0])
        ->and(array_sum($days['reservations']))->toBe(4);

    $weeks = (new Report('services', Carbon::parse('2026-09-01'), Carbon::parse('2026-10-31')))->timeline();
    expect($weeks['unit'])->toBe('week')
        ->and(array_sum($weeks['income']))->toBe(400.0);

    $months = (new Report('services', Carbon::parse('2026-01-01'), Carbon::parse('2026-12-31')))->timeline();
    expect($months['unit'])->toBe('month')
        ->and($months['labels'])->toHaveCount(12)
        ->and($months['income'][8])->toBe(100.0)
        ->and($months['income'][9])->toBe(300.0);
});

test('reservations are counted by weekday and starting hour', function () {
    $report = october('services');

    expect($report->byWeekday()['values'])->toBe([1, 2, 1, 0, 0, 0, 0]);

    $hours = array_combine($report->byHour()['labels'], $report->byHour()['values']);
    expect($hours)->toMatchArray(['08:00' => 0, '10:00' => 2, '11:00' => 1, '12:00' => 1, '20:00' => 0]);
});

test('insights name the leaders, unused items and the busiest day', function () {
    $insights = implode("\n", october('services')->insights());

    expect($insights)->toContain('Najwyższy przychód: Koloryzacja – 200,00 zł (66,7% całości).')
        ->toContain('Najwięcej rezerwacji: Strzyzenie (2).')
        ->toContain('Bez rezerwacji w tym okresie (1): Pedicure.')
        ->toContain('Najbardziej oblegany dzień: wtorek, najczęstsza godzina rozpoczęcia: 10:00.');

    expect(implode("\n", october('employees')->insights()))
        ->toContain('Aktywni pracownicy bez godzin pracy w tym okresie: 1.');
});

test('a period without reservations still produces a report', function () {
    $report = new Report('services', Carbon::parse('2025-01-01'), Carbon::parse('2025-01-31'));

    expect($report->summary()['total'])->toBe(0)
        ->and($report->rows())->toHaveCount(4)
        ->and($report->insights())->toHaveCount(1);
});

test('only admins can open the reports page', function (string $role, int $status) {
    $user = User::factory()->create(['role' => $role, 'active' => true]);

    $this->actingAs($user)->get(route('dashboard.admin.reports'))->assertStatus($status);
})->with([
    'client' => ['client', 403],
    'employee' => ['employee', 403],
    'admin' => ['admin', 200],
]);

test('the reports component is forbidden for non-admins', function () {
    Livewire::actingAs(User::factory()->create(['role' => 'employee', 'active' => true]))
        ->test('admin-reports')
        ->assertForbidden();
});

test('the admin panel links to the reports', function () {
    $this->actingAs($this->admin)->get(route('dashboard.admin'))
        ->assertSee(route('dashboard.admin.reports'));
});

test('the page shows the summary, insights and table for this month', function () {
    Livewire::actingAs($this->admin)
        ->test('admin-reports')
        ->assertSet('from', '2026-10-01')
        ->assertSet('to', '2026-10-31')
        ->assertSee('300,00 zł')
        ->assertSee('Wnioski')
        ->assertSee('Koloryzacja')
        ->assertSee('Pedicure')
        // +200% income against September
        ->assertSee('+200%');
});

test('period presets fill in the dates and editing a date switches to a custom range', function () {
    Livewire::actingAs($this->admin)
        ->test('admin-reports')
        ->set('period', 'last_month')
        ->assertSet('from', '2026-09-01')
        ->assertSet('to', '2026-09-30')
        ->set('period', 'last_30')
        ->assertSet('from', '2026-09-16')
        ->assertSet('to', '2026-10-15')
        ->set('from', '2026-10-05')
        ->assertSet('period', 'custom')
        ->assertSet('from', '2026-10-05');
});

test('a shared link keeps its custom dates', function () {
    Livewire::actingAs($this->admin)
        ->withQueryParams(['period' => 'custom', 'from' => '2026-10-06', 'to' => '2026-10-06', 'type' => 'employees'])
        ->test('admin-reports')
        ->assertSet('from', '2026-10-06')
        ->assertSet('type', 'employees')
        ->assertSee('Anna Nowak');
});

test('invalid dates show an error instead of a report', function (string $from, string $to, string $message) {
    Livewire::actingAs($this->admin)
        ->test('admin-reports')
        ->set('from', $from)
        ->set('to', $to)
        ->assertSee($message)
        ->assertDontSee('Wnioski');
})->with([
    'reversed' => ['2026-10-10', '2026-10-01', 'Data początkowa nie może być późniejsza niż końcowa.'],
    'not a date' => ['2026-02-30', '2026-03-01', 'Podaj poprawne daty początku i końca okresu.'],
    'too long' => ['2020-01-01', '2026-01-01', 'Raport może obejmować najwyżej 3 lata.'],
]);

test('unknown url values fall back to defaults', function () {
    Livewire::actingAs($this->admin)
        ->withQueryParams(['type' => 'users', 'period' => 'forever', 'sort' => 'password'])
        ->test('admin-reports')
        ->assertSet('type', 'services')
        ->assertSet('period', 'this_month')
        ->assertSet('sort', 'income');
});

test('the table can be sorted by any column', function () {
    $component = Livewire::actingAs($this->admin)->test('admin-reports');

    expect($component->get('rows')->pluck('name')->first())->toBe('Koloryzacja');

    $component->call('sortBy', 'name')->assertSet('direction', 'asc');
    expect($component->get('rows')->pluck('name')->all())->toBe(['Koloryzacja', 'Manicure', 'Pedicure', 'Strzyzenie']);

    $component->call('sortBy', 'name')->assertSet('direction', 'desc');
    expect($component->get('rows')->pluck('name')->first())->toBe('Strzyzenie');

    $component->call('sortBy', 'nope')->assertSet('sort', 'name');
});

test('utilization sorting is only for the employee report', function () {
    Livewire::actingAs($this->admin)
        ->test('admin-reports')
        ->set('type', 'employees')
        ->call('sortBy', 'utilization')
        ->assertSet('sort', 'utilization')
        ->set('type', 'services')
        ->assertSet('sort', 'income');
});

test('the report exports to csv', function () {
    Livewire::actingAs($this->admin)
        ->test('admin-reports')
        ->set('type', 'categories')
        ->call('export')
        ->assertFileDownloaded('raport-kategorie-2026-10-01_2026-10-31.csv');
});

test('the csv has polish formatting and escapes formulas', function () {
    $this->color->update(['name' => '=HYPERLINK("x")']);

    $component = Livewire::actingAs($this->admin)->test('admin-reports');
    $component->call('export');

    $csv = base64_decode($component->effects['download']['content']);

    expect($csv)->toStartWith("\xEF\xBB\xBF")
        ->toContain('Raport;Usługi')
        ->toContain('Okres;"01.10.2026 – 31.10.2026"')
        ->toContain('"\'=HYPERLINK(""x"")"')
        ->toContain('Strzyzenie;Fryzjer;100,00;60;1;"Anna Nowak";2;2;1;1;0;0;0,00;100,00;33,30;100,00;100,00;0,00;2');
});
