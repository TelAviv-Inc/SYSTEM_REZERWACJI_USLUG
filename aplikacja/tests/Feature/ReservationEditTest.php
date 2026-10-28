<?php

use App\Livewire\Forms\ReservationForm;
use App\Models\Employee;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

// Same setup as ReservationTest: "now" is Monday 2026-10-12 08:00, the employee works on
// Tuesdays (2026-10-13) 09:00-17:00 and the service takes 60 minutes.
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

    DB::table('employee_availabilities')->insert([
        'uuid' => (string) Str::uuid(),
        'employee_id' => $this->employee->uuid,
        'day_of_week' => 2,
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
    ]);

    $this->reservation = makeReservation($this, $this->client, '10:00:00', '11:00:00', 'confirmed');
});

function makeReservation($test, User $user, string $start, string $end, string $status = 'pending', string $date = '2026-10-13'): Reservation
{
    $uuid = (string) Str::uuid();

    DB::table('reservations')->insert([
        'uuid' => $uuid,
        'user_id' => $user->uuid,
        'employee_id' => $test->employee->uuid,
        'service_id' => $test->service->uuid,
        'reservation_date' => $date,
        'start_time' => $start,
        'end_time' => $end,
        'status' => $status,
    ]);

    return Reservation::find($uuid);
}

function editModal($test)
{
    return Livewire::actingAs($test->client)
        ->test('reservation-edit')
        ->call('open', $test->reservation->uuid);
}

test('client can move a reservation to a free slot', function () {
    editModal($this)
        ->call('startReschedule')
        ->call('setDate', '2026-10-13')
        ->call('setHour', '14:00')
        ->call('reschedule')
        ->assertHasNoErrors()
        ->assertSet('mode', 'details')
        ->assertDispatched('reservationUpdated');

    $reservation = $this->reservation->fresh();

    expect($reservation->start_time->format('H:i'))->toBe('14:00')
        ->and($reservation->end_time->format('H:i'))->toBe('15:00')
        // the employee has to confirm the new date again
        ->and($reservation->status)->toBe('pending')
        ->and(Reservation::count())->toBe(1);
});

test('reservation with a deactivated employee cannot be moved', function () {
    $this->employee->update(['active' => 0]);

    editModal($this)
        ->assertDontSeeHtml('wire:click="startReschedule"')
        ->call('startReschedule')
        ->assertForbidden();
});

test('moving a reservation into its own old slot is allowed', function () {
    // 10:30-11:30 overlaps the current 10:00-11:00, but that's the same visit
    editModal($this)
        ->call('startReschedule')
        ->call('setDate', '2026-10-13')
        ->call('setHour', '10:30')
        ->call('reschedule')
        ->assertHasNoErrors();

    expect($this->reservation->fresh()->start_time->format('H:i'))->toBe('10:30');
});

test('moving a reservation onto someone else\'s slot is rejected', function () {
    makeReservation($this, User::factory()->create(['active' => 1]), '13:00:00', '14:00:00');

    editModal($this)
        ->call('startReschedule')
        ->call('setDate', '2026-10-13')
        ->call('setHour', '13:30')
        ->call('reschedule')
        ->assertHasErrors(['form.hour' => ReservationForm::TAKEN]);

    expect($this->reservation->fresh()->start_time->format('H:i'))->toBe('10:00');
});

test('client can cancel a reservation', function () {
    editModal($this)
        ->call('startCancel')
        ->assertSet('mode', 'cancel')
        ->call('cancelReservation')
        ->assertSet('mode', 'details')
        ->assertDispatched('reservationUpdated');

    expect($this->reservation->fresh()->status)->toBe('cancelled');
});

test('summary counters refresh after a cancel', function () {
    $summary = Livewire::actingAs($this->client)->test('reservations-summary')
        ->assertSeeInOrder(['1', 'Wszystkie', '1', 'Nadchodzące']);

    editModal($this)->call('startCancel')->call('cancelReservation');

    // html() instead of assertSee - after an update the response is JSON with escaped "ą"
    expect($summary->dispatch('reservationUpdated')->html())
        ->toMatch('/>1<\/h3>\s*<p[^>]*>Wszystkie/')
        ->toMatch('/>0<\/h3>\s*<p[^>]*>Nadchodzące/');
});

test('cancelled reservation can no longer be changed', function () {
    $this->reservation->update(['status' => 'cancelled']);

    editModal($this)->call('startReschedule')->assertForbidden();
    editModal($this)->call('startCancel')->assertForbidden();
});

test('past reservation can no longer be changed', function () {
    $this->reservation = makeReservation($this, $this->client, '10:00:00', '11:00:00', 'confirmed', '2026-10-06');

    editModal($this)->call('startReschedule')->assertForbidden();
});

test('client cannot open another user\'s reservation', function () {
    $this->reservation = makeReservation($this, User::factory()->create(['active' => 1]), '13:00:00', '14:00:00');

    // the lookup is scoped to the client's own reservations, so a foreign uuid ends in 404
    editModal($this)
        ->assertStatus(404)
        ->assertSet('reservation', null);
});

test('hour calendar shows the rescheduled visit\'s own slot as free', function () {
    Livewire::actingAs($this->client)
        ->test('employee-hour-calendar', [
            'selectedDay' => '2026-10-13',
            'selectedEmployee' => $this->employee,
            'duration' => 60,
            'ignoreReservation' => $this->reservation->uuid,
        ])
        ->assertViewHas('_slots', fn ($slots) => collect($slots)->firstWhere('time', '10:00')['isAvailable'] === true);
});

test('a visit can still be moved after its service was deactivated', function () {
    $this->service->update(['active' => 0]);

    editModal($this)
        ->call('startReschedule')
        ->call('setDate', '2026-10-13')
        ->call('setHour', '14:00')
        ->call('reschedule')
        ->assertHasNoErrors();

    expect($this->reservation->fresh()->start_time->format('H:i'))->toBe('14:00');
});

test('details and the reservations list show the price stored at booking', function () {
    DB::table('reservations')->where('uuid', $this->reservation->uuid)->update(['price' => 80]);
    $this->service->update(['price' => 150]);

    editModal($this)->assertSee('80,00 zł')->assertDontSee('150,00 zł');

    Livewire::actingAs($this->client)
        ->test('reservations-menu')
        ->assertSee('80,00 zł')
        ->assertDontSee('150,00 zł');
});

test('client reservations sort by the price stored at booking', function () {
    DB::table('reservations')->where('uuid', $this->reservation->uuid)->update(['price' => 120]);
    $cheaper = makeReservation($this, $this->client, '13:00:00', '14:00:00');
    DB::table('reservations')->where('uuid', $cheaper->uuid)->update(['price' => 90]);

    $order = Livewire::actingAs($this->client)
        ->test('reservations-menu')
        ->set('sort', 'price')
        ->get('reservations')
        ->pluck('uuid')
        ->all();

    expect($order)->toBe([$cheaper->uuid, $this->reservation->uuid]);
});

test('a client cannot confirm or complete their own reservation', function () {
    $this->reservation->update(['status' => 'pending']);

    editModal($this)
        ->assertDontSeeHtml('wire:click="confirm"')
        ->call('confirm')
        ->assertForbidden();

    $this->travelTo(Carbon::parse('2026-10-13 12:00'));
    editModal($this)->call('complete')->assertForbidden();

    expect($this->reservation->fresh()->status)->toBe('pending');
});
