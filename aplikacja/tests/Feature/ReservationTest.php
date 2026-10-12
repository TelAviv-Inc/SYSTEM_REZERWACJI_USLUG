<?php

use App\Models\Employee;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

// messages from ReservationForm - asserting the exact one makes sure a slot is rejected for the right reason
const TAKEN = 'Ten termin jest juz zajety. Wybierz inna godzine.';
const UNAVAILABLE = 'Pracownik nie jest dostępny w tym terminie.';
const PAST = 'Nie mozna zarezerwowac terminu w przeszlosci';

// "Now" is Monday 2026-10-12 08:00. The employee works on Tuesdays (2026-10-13) 09:00-17:00,
// and the service takes 60 minutes.
beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-10-12 08:00'));

    $this->client = User::factory()->create(['role' => 'client', 'active' => 1]);

    $this->employee = Employee::forceCreate([
        'uuid' => (string) Str::uuid(),
        'user_id' => User::factory()->create(['role' => 'employee', 'active' => 1])->uuid,
        'active' => 1,
    ]);

    $this->service = Service::forceCreate([
        'uuid' => (string) Str::uuid(),
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

    // raw insert - the model has no factory definition and doesn't fill uuid
    DB::table('employee_availabilities')->insert([
        'uuid' => (string) Str::uuid(),
        'employee_id' => $this->employee->uuid,
        'day_of_week' => 2,
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
    ]);
});

// walks the same steps as the UI: service -> employee -> day -> hour -> reserve
function reserveSlot($test, string $date, string $hour)
{
    return Livewire::actingAs($test->client)
        ->test('reserve-service')
        ->call('serviceChosen', $test->service->uuid)
        ->call('selectEmployee', $test->employee->uuid)
        ->call('setDate', $date)
        ->call('setHour', $hour)
        ->call('reserve');
}

function existingReservation($test, string $start, string $end, string $status = 'pending'): void
{
    DB::table('reservations')->insert([
        'uuid' => (string) Str::uuid(),
        'user_id' => $test->client->uuid,
        'employee_id' => $test->employee->uuid,
        'service_id' => $test->service->uuid,
        'reservation_date' => '2026-10-13',
        'start_time' => $start,
        'end_time' => $end,
        'status' => $status,
    ]);
}

test('client can reserve a free slot', function () {
    reserveSlot($this, '2026-10-13', '10:00')
        ->assertHasNoErrors()
        ->assertSet('reserved', true);

    $reservation = Reservation::sole();

    expect($reservation->user_id)->toBe($this->client->uuid)
        ->and($reservation->employee_id)->toBe($this->employee->uuid)
        ->and($reservation->service_id)->toBe($this->service->uuid)
        ->and($reservation->reservation_date->format('Y-m-d'))->toBe('2026-10-13')
        ->and($reservation->start_time->format('H:i'))->toBe('10:00')
        ->and($reservation->end_time->format('H:i'))->toBe('11:00')
        ->and($reservation->status)->toBe('pending');
});

test('slot overlapping an existing reservation is rejected', function () {
    existingReservation($this, '10:30:00', '11:30:00');

    reserveSlot($this, '2026-10-13', '10:00')->assertHasErrors(['form.hour' => TAKEN]);

    expect(Reservation::count())->toBe(1);
});

test('slot ending exactly when another reservation starts is allowed', function () {
    existingReservation($this, '11:00:00', '12:00:00');

    reserveSlot($this, '2026-10-13', '10:00')->assertHasNoErrors();

    expect(Reservation::count())->toBe(2);
});

test('cancelled reservation does not block the slot', function () {
    existingReservation($this, '10:00:00', '11:00:00', 'cancelled');

    reserveSlot($this, '2026-10-13', '10:00')->assertHasNoErrors();

    expect(Reservation::count())->toBe(2);
});

test('the same slot cannot be reserved twice', function () {
    reserveSlot($this, '2026-10-13', '10:00')->assertHasNoErrors();
    reserveSlot($this, '2026-10-13', '10:00')->assertHasErrors(['form.hour' => TAKEN]);

    expect(Reservation::count())->toBe(1);
});

test('service that would end after working hours is rejected', function () {
    // 16:30 + 60 min = 17:30, the employee finishes at 17:00
    reserveSlot($this, '2026-10-13', '16:30')->assertHasErrors(['form.hour' => UNAVAILABLE]);

    expect(Reservation::count())->toBe(0);
});

test('day the employee does not work is rejected', function () {
    // Wednesday
    reserveSlot($this, '2026-10-14', '10:00')->assertHasErrors(['form.hour' => UNAVAILABLE]);

    expect(Reservation::count())->toBe(0);
});

test('hour earlier today is rejected', function () {
    $this->travelTo(Carbon::parse('2026-10-13 12:00'));

    reserveSlot($this, '2026-10-13', '10:00')->assertHasErrors(['form.hour' => PAST]);

    expect(Reservation::count())->toBe(0);
});

test('past date is rejected', function () {
    reserveSlot($this, '2026-10-06', '10:00')->assertHasErrors(['form.date']);

    expect(Reservation::count())->toBe(0);
});

test('malformed hour returns a validation error instead of crashing', function () {
    reserveSlot($this, '2026-10-13', 'abc')->assertHasErrors(['form.hour']);

    expect(Reservation::count())->toBe(0);
});
