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

// "now" is Monday 2026-10-12 08:00, the employee works on Tuesdays (2026-10-13) 09:00-17:00
// and the service takes 60 minutes - same setup as ReservationEditTest.
beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-10-12 08:00'));

    $this->admin = User::factory()->create(['role' => 'admin', 'active' => true]);
    $this->client = User::factory()->create(['role' => 'client', 'active' => true, 'name' => 'Klementyna', 'surname' => 'Klientowska']);

    $this->employee = Employee::forceCreate([
        'uuid' => (string) Str::uuid(),
        'user_id' => User::factory()->create(['role' => 'employee', 'active' => true, 'name' => 'Pracowita'])->uuid,
        'active' => 1,
        'created_at' => '2026-03-15 10:00:00',
    ]);

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

    DB::table('employee_services')->insert([
        'uuid' => (string) Str::uuid(),
        'employee_id' => $this->employee->uuid,
        'service_id' => $this->service->uuid,
    ]);

    DB::table('employee_availabilities')->insert([
        'uuid' => (string) Str::uuid(),
        'employee_id' => $this->employee->uuid,
        'day_of_week' => 2,
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
    ]);

    $this->reservation = employeeReservation($this, '2026-10-13', '10:00:00', 'confirmed');
});

function employeeReservation($test, string $date, string $start, string $status = 'pending', ?Employee $employee = null): Reservation
{
    $uuid = (string) Str::uuid();

    DB::table('reservations')->insert([
        'uuid' => $uuid,
        'user_id' => $test->client->uuid,
        'employee_id' => ($employee ?? $test->employee)->uuid,
        'service_id' => $test->service->uuid,
        'reservation_date' => $date,
        'start_time' => $start,
        'end_time' => Carbon::parse($start)->addHour()->format('H:i:s'),
        'status' => $status,
    ]);

    return Reservation::find($uuid);
}

function employeeDetails($test)
{
    return Livewire::actingAs($test->admin)
        ->test('admin-employee-details')
        ->call('open', $test->employee->uuid);
}

test('details show the start date, services and upcoming reservations', function () {
    employeeReservation($this, '2026-10-06', '10:00:00', 'completed');
    employeeReservation($this, '2026-10-20', '12:00:00', 'cancelled');

    $details = employeeDetails($this)
        ->assertSee('15.03.2026')
        ->assertSee('Strzyzenie')
        ->assertSee('Fryzjer')
        ->assertSee('Klementyna')
        ->assertSee('13.10.2026 10:00-11:00');

    // past and cancelled visits are not "upcoming"
    expect($details->get('upcomingReservations')->pluck('uuid')->all())->toBe([$this->reservation->uuid])
        ->and($details->get('upcomingCount'))->toBe(1);
});

test('upcoming reservations are listed 10 at a time', function () {
    foreach (range(1, 11) as $week) {
        employeeReservation($this, Carbon::parse('2026-10-13')->addWeeks($week)->toDateString(), '10:00:00');
    }

    $details = employeeDetails($this)->assertSee('Pokaż więcej');
    expect($details->get('upcomingReservations'))->toHaveCount(10)
        ->and($details->get('upcomingCount'))->toBe(12);

    $details->call('showMore')->assertDontSee('Pokaż więcej');
    expect($details->get('upcomingReservations'))->toHaveCount(12);
});

test('bookings chart counts only this employee\'s reservations per week', function () {
    $other = Employee::forceCreate([
        'uuid' => (string) Str::uuid(),
        'user_id' => User::factory()->create(['role' => 'employee', 'active' => true])->uuid,
        'active' => 1,
    ]);
    employeeReservation($this, '2026-10-13', '12:00:00', 'pending', $other);
    employeeReservation($this, '2026-10-06', '10:00:00', 'completed');
    employeeReservation($this, '2026-10-14', '10:00:00', 'cancelled');

    $bookings = employeeDetails($this)->set('weeks', 4)->get('bookings');

    expect($bookings['labels'])->toBe(['21.09', '28.09', '05.10', '12.10'])
        ->and($bookings['values'])->toBe([0, 0, 1, 1]);
});

test('bookings period can be 4, 12 or 52 weeks', function (int $weeks) {
    expect(employeeDetails($this)->set('weeks', $weeks)->get('bookings')['values'])->toHaveCount($weeks);
})->with([4, 12, 52]);

test('unknown bookings period falls back to 12 weeks', function () {
    employeeDetails($this)->set('weeks', 7)->assertSet('weeks', 12);
});

test('weekly reservations without an employee still count everyone', function () {
    $other = Employee::forceCreate([
        'uuid' => (string) Str::uuid(),
        'user_id' => User::factory()->create(['role' => 'employee', 'active' => true])->uuid,
        'active' => 1,
    ]);
    employeeReservation($this, '2026-10-13', '12:00:00', 'pending', $other);

    expect(AdminStats::weeklyReservations(4)['values'])->toBe([0, 0, 0, 2])
        ->and(AdminStats::weeklyReservations(4, $other->uuid)['values'])->toBe([0, 0, 0, 1]);
});

test('only an admin can open employee details', function (string $role) {
    $user = User::factory()->create(['role' => $role, 'active' => true]);

    Livewire::actingAs($user)
        ->test('admin-employee-details')
        ->call('open', $this->employee->uuid)
        ->assertForbidden();
})->with(['client', 'employee']);

test('employees list opens the details modal for a row', function () {
    Livewire::actingAs($this->admin)
        ->test('admin-employees')
        ->assertSeeHtml("\$dispatch('detailsEmployee', { uuid: '{$this->employee->uuid}' })")
        ->assertSeeLivewire('admin-employee-details');
});

test('details list opens the reservation edit modal', function () {
    employeeDetails($this)
        ->assertSeeHtml("\$dispatch('editReservation', { uuid: '{$this->reservation->uuid}' })")
        ->assertSeeLivewire('reservation-edit');
});

test('admin can move an employee\'s reservation', function () {
    Livewire::actingAs($this->admin)
        ->test('reservation-edit')
        ->call('open', $this->reservation->uuid)
        ->assertSee('Klementyna')
        ->call('startReschedule')
        ->call('setDate', '2026-10-13')
        ->call('setHour', '14:00')
        ->call('reschedule')
        ->assertHasNoErrors()
        ->assertDispatched('reservationUpdated');

    $reservation = $this->reservation->fresh();

    expect($reservation->start_time->format('H:i'))->toBe('14:00')
        ->and($reservation->user_id)->toBe($this->client->uuid);
});

test('admin can cancel an employee\'s reservation and the details refresh', function () {
    Livewire::actingAs($this->admin)
        ->test('reservation-edit')
        ->call('open', $this->reservation->uuid)
        ->call('startCancel')
        ->call('cancelReservation')
        ->assertDispatched('reservationUpdated');

    expect($this->reservation->fresh()->status)->toBe('cancelled');

    $details = employeeDetails($this)->dispatch('reservationUpdated');
    expect($details->get('upcomingCount'))->toBe(0);
});

test('admin can add a service to an employee', function () {
    $category = ServiceCategory::create(['name' => 'Kosmetyka', 'description' => 'Opis', 'icon' => 'fa-spa']);
    $manicure = Service::forceCreate([
        'uuid' => (string) Str::uuid(),
        'category_id' => $category->uuid,
        'name' => 'Manicure',
        'description' => 'Opis uslugi',
        'duration' => 30,
        'price' => 80,
        'active' => 1,
    ]);

    $details = employeeDetails($this);
    // already assigned services are not offered again
    expect($details->get('availableServices')->flatten()->pluck('uuid')->all())->toBe([$manicure->uuid]);

    $details->set('serviceToAdd', $manicure->uuid)
        ->call('addService')
        ->assertHasNoErrors()
        ->assertSet('serviceToAdd', '')
        ->assertDispatched('employeeUpdated')
        ->assertSee('Manicure');

    expect($this->employee->services()->pluck('services.uuid')->sort()->values()->all())
        ->toBe(collect([$this->service->uuid, $manicure->uuid])->sort()->values()->all());
});

test('a service can\'t be added twice or when it is inactive', function () {
    $inactive = Service::forceCreate([
        'uuid' => (string) Str::uuid(),
        'category_id' => $this->service->category_id,
        'name' => 'Koloryzacja',
        'description' => 'Opis uslugi',
        'duration' => 90,
        'price' => 200,
        'active' => 0,
    ]);

    employeeDetails($this)
        ->set('serviceToAdd', $this->service->uuid)
        ->call('addService')
        ->assertHasErrors(['serviceToAdd' => 'not_in'])
        ->set('serviceToAdd', $inactive->uuid)
        ->call('addService')
        ->assertHasErrors(['serviceToAdd' => 'exists']);

    expect($this->employee->services()->count())->toBe(1);
});

test('admin can remove a service and its booked visits stay', function () {
    employeeDetails($this)
        ->assertSeeHtml('nadchodzących rezerwacji tej usługi')
        ->call('removeService', $this->service->uuid)
        ->assertDispatched('employeeUpdated')
        ->assertSee('Pracownik nie ma przypisanych usług.');

    expect($this->employee->services()->count())->toBe(0)
        ->and($this->reservation->fresh()->status)->toBe('confirmed');
});

test('working hours are listed per weekday', function () {
    employeeDetails($this)
        ->assertSee('Wtorek')
        ->assertSee('09:00–17:00')
        ->assertSee('Wolne');
});

test('admin can add working hours that the booking flow accepts', function () {
    employeeDetails($this)
        ->set('day', 3)
        ->set('startTime', '08:00')
        ->set('endTime', '12:00')
        ->call('saveAvailability')
        ->assertHasNoErrors()
        ->assertSee('08:00–12:00');

    $availability = $this->employee->availability()->where('day_of_week', 3)->sole();
    expect($availability->getRawOriginal('start_time'))->toBe('08:00:00')
        ->and($availability->getRawOriginal('end_time'))->toBe('12:00:00');

    // Wednesday 2026-10-14 11:00-12:00 ends exactly when the new range does
    Livewire::actingAs($this->client)
        ->test('reserve-service')
        ->call('serviceChosen', $this->service->uuid)
        ->call('selectEmployee', $this->employee->uuid)
        ->call('setDate', '2026-10-14')
        ->call('setHour', '11:00')
        ->call('reserve')
        ->assertHasNoErrors();

    expect(Reservation::whereDate('reservation_date', '2026-10-14')->count())->toBe(1);
});

test('working hours must end after they start and not overlap the same day', function () {
    employeeDetails($this)
        ->set('day', 2)
        ->set('startTime', '12:00')
        ->set('endTime', '10:00')
        ->call('saveAvailability')
        ->assertHasErrors(['endTime' => 'after'])
        ->set('startTime', '16:00')
        ->set('endTime', '19:00')
        ->call('saveAvailability')
        ->assertHasErrors('startTime')
        // touching ranges are fine
        ->set('startTime', '17:00')
        ->call('saveAvailability')
        ->assertHasNoErrors();

    expect($this->employee->availability()->where('day_of_week', 2)->count())->toBe(2);
});

test('admin can change and remove working hours', function () {
    $availability = $this->employee->availability()->sole();

    $details = employeeDetails($this)
        ->call('editAvailability', $availability->uuid)
        ->assertSet('day', 2)
        ->assertSet('startTime', '09:00')
        ->assertSet('endTime', '17:00')
        // the range being edited doesn't count as an overlap with itself
        ->set('endTime', '15:00')
        ->call('saveAvailability')
        ->assertHasNoErrors()
        ->assertSet('editingAvailability', null)
        ->assertSee('09:00–15:00');

    expect($availability->fresh()->getRawOriginal('end_time'))->toBe('15:00:00');

    $details->call('removeAvailability', $availability->uuid);
    expect($this->employee->availability()->count())->toBe(0);
});

test('details warn about upcoming visits outside the working hours', function () {
    $details = employeeDetails($this)->assertDontSee('poza godzinami pracy');
    expect($details->get('reservationsOutsideHours'))->toBe(0);

    // the Tuesday 10:00 visit no longer fits
    $details->call('editAvailability', $this->employee->availability()->sole()->uuid)
        ->set('startTime', '12:00')
        ->call('saveAvailability')
        ->assertSee('poza godzinami pracy: 1');
});

test('only an admin can manage services and working hours', function (string $role) {
    $user = User::factory()->create(['role' => $role, 'active' => true]);

    // the modal is opened in an admin session, then the action runs as someone else
    $asOther = function (string $method, ...$params) use ($user) {
        $component = Livewire::actingAs($this->admin)->test('admin-employee-details')->call('open', $this->employee->uuid);
        $this->actingAs($user);

        return $component->call($method, ...$params);
    };

    $asOther('removeService', $this->service->uuid)->assertForbidden();
    $asOther('saveAvailability')->assertForbidden();
    $asOther('removeAvailability', $this->employee->availability()->sole()->uuid)->assertForbidden();

    expect($this->employee->services()->count())->toBe(1)
        ->and($this->employee->availability()->count())->toBe(1);
})->with(['client', 'employee']);
