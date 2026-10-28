<?php

use App\Models\Employee;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Support\AdminStats;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

// "now" is Monday 2026-10-12 08:00 - same setup as ReservationEditTest. The service takes
// 60 minutes and costs 100 zł.
beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-10-12 08:00'));

    $this->admin = User::factory()->create(['role' => 'admin', 'active' => true]);
    $this->client = User::factory()->create(['role' => 'client', 'active' => true, 'name' => 'Klementyna', 'surname' => 'Klientowska', 'email' => 'klementyna@example.test']);
    $this->otherClient = User::factory()->create(['role' => 'client', 'active' => true, 'name' => 'Bonifacy', 'surname' => 'Bywalski', 'email' => 'bonifacy@example.test']);

    $this->employee = staffMember('Pracowita');
    $this->otherEmployee = staffMember('Zapracowany');

    $category = ServiceCategory::create(['name' => 'Fryzjer', 'description' => 'Opis', 'icon' => 'fa-scissors']);
    $this->service = Service::forceCreate([
        'uuid' => (string) Str::uuid(),
        'category_id' => $category->uuid,
        'name' => 'Strzyzenie',
        'description' => 'Opis uslugi',
        'duration' => 60,
        'price' => 100,
        'active' => 1,
    ]);
});

function staffMember(string $name): Employee
{
    return Employee::forceCreate([
        'uuid' => (string) Str::uuid(),
        'user_id' => User::factory()->create(['role' => 'employee', 'active' => true, 'name' => $name])->uuid,
        'active' => 1,
    ]);
}

function bookedVisit($test, string $date, string $start, string $status = 'pending', ?Employee $employee = null, ?User $client = null, int $price = 100): Reservation
{
    $uuid = (string) Str::uuid();

    DB::table('reservations')->insert([
        'uuid' => $uuid,
        'user_id' => ($client ?? $test->client)->uuid,
        'employee_id' => ($employee ?? $test->employee)->uuid,
        'service_id' => $test->service->uuid,
        'reservation_date' => $date,
        'start_time' => $start,
        'end_time' => Carbon::parse($start)->addHour()->format('H:i:s'),
        'status' => $status,
        'price' => $price,
    ]);

    return Reservation::find($uuid);
}

// a - completed last week, b - pending tomorrow, c - confirmed tomorrow with the other employee and client,
// d - cancelled next week, e - pending a week ago, f - confirmed today at 07:00 (already started)
function sixVisits($test): array
{
    return [
        'a' => bookedVisit($test, '2026-10-06', '10:00:00', 'completed'),
        'b' => bookedVisit($test, '2026-10-13', '10:00:00', 'pending'),
        'c' => bookedVisit($test, '2026-10-13', '12:00:00', 'confirmed', $test->otherEmployee, $test->otherClient),
        'd' => bookedVisit($test, '2026-10-20', '10:00:00', 'cancelled', client: $test->otherClient),
        'e' => bookedVisit($test, '2026-10-05', '10:00:00', 'pending'),
        'f' => bookedVisit($test, '2026-10-12', '07:00:00', 'confirmed'),
    ];
}

function reservationsTab($test)
{
    return Livewire::actingAs($test->admin)->test('admin-reservations');
}

// the visits a list shows, as their letters from sixVisits()
function listed($component, array $visits): array
{
    $letters = collect($visits)->mapWithKeys(fn (Reservation $r, string $letter) => [$r->uuid => $letter]);

    return $component->get('reservations')->pluck('uuid')->map(fn ($uuid) => $letters[$uuid])->all();
}

function manageReservation($test, Reservation $reservation, ?User $as = null)
{
    return Livewire::actingAs($as ?? $test->admin)
        ->test('reservation-edit')
        ->call('open', $reservation->uuid);
}

test('only an admin can open the reservations tab', function (string $role) {
    Livewire::actingAs(User::factory()->create(['role' => $role, 'active' => true]))
        ->test('admin-reservations')
        ->assertForbidden();
})->with(['client', 'employee']);

test('the list shows each reservation with the price stored at booking', function () {
    $visit = bookedVisit($this, '2026-10-13', '10:00:00', 'confirmed', price: 80);
    $this->service->update(['price' => 150]);

    reservationsTab($this)
        ->assertSee('13.10.2026 10:00-11:00')
        ->assertSee('Klementyna Klientowska')
        ->assertSee('Strzyzenie')
        ->assertSee('80,00 zł')
        ->assertDontSee('150,00 zł')
        // the status badge, not the option of the status filter
        ->assertSeeHtml('>Potwierdzona</span>')
        ->assertSeeHtml("\$dispatch('editReservation', { uuid: '{$visit->uuid}' })")
        ->assertSeeLivewire('reservation-edit');
});

test('reservations are listed newest first and the order can be flipped', function () {
    $visits = sixVisits($this);
    $tab = reservationsTab($this);

    expect(listed($tab, $visits))->toBe(['d', 'c', 'b', 'f', 'a', 'e']);

    $tab->call('toggleDirection');
    expect(listed($tab, $visits))->toBe(['e', 'a', 'f', 'b', 'c', 'd']);
});

test('reservations can be filtered', function (array $filters, array $expected) {
    $visits = sixVisits($this);
    $tab = reservationsTab($this);

    foreach ($filters as $property => $value) {
        $tab->set($property, $value instanceof Closure ? $value->call($this) : $value);
    }

    expect(listed($tab, $visits))->toBe($expected);
})->with([
    'status' => [['status' => 'pending'], ['b', 'e']],
    'waiting to be completed' => [['status' => 'to_complete'], ['f', 'e']],
    'employee' => [['employee' => fn () => $this->otherEmployee->uuid], ['c']],
    'date range' => [['from' => '2026-10-12', 'to' => '2026-10-13'], ['c', 'b', 'f']],
    'client surname' => [['search' => 'Bywal'], ['d', 'c']],
    'client email' => [['search' => 'klementyna@'], ['b', 'f', 'a', 'e']],
    'status and employee' => [['status' => 'confirmed', 'employee' => fn () => $this->employee->uuid], ['f']],
    'not a real date is ignored' => [['from' => '2026-02-30'], ['d', 'c', 'b', 'f', 'a', 'e']],
]);

test('a range that ends before it starts shows an error instead of the list', function () {
    sixVisits($this);

    reservationsTab($this)
        ->set('from', '2026-10-20')
        ->set('to', '2026-10-01')
        ->assertSee('Data początkowa nie może być późniejsza niż końcowa.')
        ->assertDontSee('Klementyna Klientowska');
});

test('changing a filter goes back to page 1', function () {
    Livewire::actingAs($this->admin)
        ->withQueryParams(['reservations_page' => 2])
        ->test('admin-reservations')
        ->assertSet('paginators.reservations_page', 2)
        ->set('status', 'pending')
        ->assertSet('paginators.reservations_page', 1);
});

test('visits waiting to be completed are pointed out', function () {
    sixVisits($this);

    reservationsTab($this)
        ->assertSee('czekają na zakończenie lub anulowanie: 2')
        ->set('search', 'Bywal')
        ->call('showToComplete')
        ->assertSet('status', 'to_complete')
        ->assertSet('search', '')
        ->assertSet('direction', 'asc')
        ->assertDontSee('czekają na zakończenie');
});

test('admin confirms a pending reservation and the list refreshes', function () {
    $visits = sixVisits($this);
    $tab = reservationsTab($this)->set('status', 'pending');

    manageReservation($this, $visits['b'])
        ->assertSeeHtml('wire:click="confirm"')
        ->assertDontSeeHtml('wire:click="complete"')
        ->call('confirm')
        ->assertDispatched('reservationUpdated')
        ->assertSee('Rezerwacja została potwierdzona.');

    expect($visits['b']->fresh()->status)->toBe('confirmed')
        ->and(listed($tab->dispatch('reservationUpdated'), $visits))->toBe(['e']);
});

test('a visit that has started can be completed but not confirmed', function () {
    $visits = sixVisits($this);

    manageReservation($this, $visits['e'])
        ->assertDontSeeHtml('wire:click="confirm"')
        ->assertSeeHtml('wire:click="complete"')
        ->call('confirm')
        ->assertForbidden();

    manageReservation($this, $visits['e'])
        ->call('complete')
        ->assertDispatched('reservationUpdated')
        ->assertSee('Wizyta została oznaczona jako zakończona.');

    expect($visits['e']->fresh()->status)->toBe('completed');
});

test('a visit that has not started yet cannot be completed', function () {
    $visits = sixVisits($this);

    manageReservation($this, $visits['b'])->call('complete')->assertForbidden();

    expect($visits['b']->fresh()->status)->toBe('pending');
});

test('completing a visit adds its price to the income chart', function () {
    $visits = sixVisits($this);
    expect(AdminStats::weeklyIncome(1)['values'])->toBe([0.0]);

    manageReservation($this, $visits['f'])->call('complete');

    expect(AdminStats::weeklyIncome(1)['values'])->toBe([100.0]);
});

test('admin can cancel a visit that has already started, e.g. a no-show', function () {
    $visits = sixVisits($this);

    manageReservation($this, $visits['f'])
        ->call('startCancel')
        ->call('cancelReservation')
        ->assertDispatched('reservationUpdated');

    expect($visits['f']->fresh()->status)->toBe('cancelled');
});

test('a closed reservation cannot be reopened, even by an admin', function (string $status) {
    $visit = bookedVisit($this, '2026-10-20', '10:00:00', $status);

    manageReservation($this, $visit)->assertSee('Tej rezerwacji nie można już zmienić ani anulować.');

    foreach (['startReschedule', 'reschedule', 'startCancel', 'cancelReservation', 'confirm', 'complete'] as $action) {
        manageReservation($this, $visit)->call($action)->assertForbidden();
    }

    expect($visit->fresh()->status)->toBe($status);
})->with(['cancelled', 'completed']);

test('clients cannot confirm or complete their own reservations', function () {
    $visits = sixVisits($this);

    manageReservation($this, $visits['b'], $this->client)
        ->assertDontSeeHtml('wire:click="confirm"')
        ->call('confirm')
        ->assertForbidden();

    manageReservation($this, $visits['e'], $this->client)->call('complete')->assertForbidden();

    expect($visits['b']->fresh()->status)->toBe('pending')
        ->and($visits['e']->fresh()->status)->toBe('pending');
});

test('only the booked employee can confirm and complete their visits', function () {
    $visits = sixVisits($this);
    $booked = $this->employee->user;
    $other = $this->otherEmployee->user;

    expect($booked->can('confirm', $visits['b']))->toBeTrue()
        ->and($booked->can('complete', $visits['e']))->toBeTrue()
        ->and($other->can('confirm', $visits['b']))->toBeFalse()
        ->and($other->can('complete', $visits['e']))->toBeFalse();
});

test('the admin menu opens the reservations tab by default', function () {
    Livewire::actingAs($this->admin)
        ->test('admin-menu')
        ->assertSet('option', 'reservations')
        ->assertSeeLivewire('admin-reservations');
});

test('the admin menu tab is read from the URL', function () {
    Livewire::actingAs($this->admin)
        ->withQueryParams(['tab' => 'users'])
        ->test('admin-menu')
        ->assertSeeLivewire('admin-users');

    Livewire::actingAs($this->admin)
        ->withQueryParams(['tab' => 'secret'])
        ->test('admin-menu')
        ->assertSet('option', 'reservations');
});
