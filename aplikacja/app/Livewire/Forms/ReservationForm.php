<?php

namespace App\Livewire\Forms;

use App\Models\Employee;
use App\Models\EmployeeAvailability;
use App\Models\Reservation;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ReservationForm extends Form
{
    public ?string $service_id = null;

    public ?string $employee_id = null;

    public ?string $date = null;

    public ?string $hour = null;

    // set when rescheduling - the reservation being moved must not block its own new slot
    public ?string $reservation_id = null;

    public const TAKEN = 'Ten termin jest już zajęty. Wybierz inną godzinę.';

    #[Validate]
    public function rules(): array
    {
        return [
            // a booked visit can still be moved after its service was deactivated, a new one can't be made
            'service_id' => ['required', $this->reservation_id ? Rule::exists('services', 'uuid') : Rule::exists('services', 'uuid')->where('active', 1)],
            'employee_id' => ['required', 'exists:employees,uuid', Rule::exists('employee_services', 'employee_id')->where('service_id', $this->service_id)],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],

            'hour' => ['bail', 'required', 'date_format:H:i',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $service = Service::find($this->service_id);

                    if (! $service || ! $this->employee_id || ! $this->date || ! Carbon::canBeCreatedFromFormat($this->date, 'Y-m-d')) {
                        return;
                    }
                    [$start, $end] = $this->timeRange($service->duration);

                    if (Carbon::parse("{$this->date} {$this->hour}")->isPast()) {
                        return $fail('Nie można zarezerwować terminu w przeszłości');
                    }

                    $fits = EmployeeAvailability::where('employee_id', $this->employee_id)
                        ->where('day_of_week', Carbon::parse($this->date)->isoWeekday())
                        ->where('start_time', '<=', $start)
                        ->where('end_time', '>=', $end)
                        ->exists();

                    if (! $fits) {
                        return $fail('Pracownik nie jest dostępny w tym terminie.');
                    }

                    $taken = Reservation::overlapping($this->employee_id, $this->date, $start, $end, $this->reservation_id)->exists();

                    if ($taken) {
                        $fail(self::TAKEN);
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'service_id.exists' => 'Ta usługa nie jest już dostępna.',
        ];
    }

    public function timeRange(int $duration)
    {
        $start = Carbon::createFromFormat('H:i', $this->hour);

        return [$start->format('H:i:s'), $start->copy()->addMinutes($duration)->format('H:i:s')];
    }

    public function reserve()
    {
        $service = Service::findOrFail($this->service_id);
        [$start, $end] = $this->timeRange($service->duration);

        return DB::transaction(function () use ($service, $start, $end) {
            Employee::whereKey($this->employee_id)->lockForUpdate()->first();

            if (Reservation::overlapping($this->employee_id, $this->date, $start, $end)->exists()) {
                throw ValidationException::withMessages([
                    'form.hour' => self::TAKEN,
                ]);
            }
            Reservation::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => auth()->id(),
                'employee_id' => $this->employee_id,
                'service_id' => $this->service_id,
                'reservation_date' => $this->date,
                'start_time' => substr($start, 0, 5),
                'end_time' => substr($end, 0, 5),
                'price' => $service->price,

            ]);
        });

    }

    public function reschedule(Reservation $reservation)
    {
        $service = Service::findOrFail($this->service_id);
        [$start, $end] = $this->timeRange($service->duration);

        return DB::transaction(function () use ($reservation, $start, $end) {
            Employee::whereKey($this->employee_id)->lockForUpdate()->first();

            if (Reservation::overlapping($this->employee_id, $this->date, $start, $end, $reservation->uuid)->exists()) {
                throw ValidationException::withMessages([
                    'form.hour' => self::TAKEN,
                ]);
            }

            // a new date has to be confirmed again by the employee
            $reservation->update([
                'reservation_date' => $this->date,
                'start_time' => substr($start, 0, 5),
                'end_time' => substr($end, 0, 5),
                'status' => 'pending',
            ]);
        });
    }
}
