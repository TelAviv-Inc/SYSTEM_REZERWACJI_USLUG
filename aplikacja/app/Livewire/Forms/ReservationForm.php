<?php

namespace App\Livewire\Forms;

use App\Models\Employee;
use App\Models\Service;
use App\Models\Reservation;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class ReservationForm extends Form
{
    public ?string $service_id = null;
    public ?string $employee_id = null;
    public ?string $date = null;
    public ?string $hour = null;

    #[Validate]
    public function rules(): array 
    {
        return [
            'service_id' => ['required', 'exists:services,uuid'],
            "employee_id" => ['required', 'exists:employees,uuid', Rule::exists('employee_services', 'employee_id')->where('service_id', $this->service_id),],
            "date" => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],

            'hour' => ['required', 'date_format:H:i', 
                function (string $attribute, mixed $value, \Closure $fail)
                {
                    $service = Service::find($this->service_id);
                    if (!$service || !$this->employee_id || !$this->date) return;
                    [$start, $end] = $this->timeRange($service->duration);
                    $taken = Reservation::overlapping($this->employee_id, $this->date, $start, $end)->exists();

                    if ($taken) $fail('Ten termin jest juz zajety. Wybierz inna godzine.');
                },
            ],
        ];
    }

    public function timeRange(int $duration)
    {
        $start = Carbon::createFromFormat('H:i', $this->hour);
        return [$start->format('H:i:s'), $start->copy()->addMinutes($duration)->format('H:i:s')];
    }
    
}

