<?php

namespace App\Livewire\Forms;

use App\Models\Employee;
use App\Models\EmployeeAvailability;
use App\Models\Service;
use App\Models\Reservation;
use Dotenv\Exception\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

            'hour' => ['bail' ,'required', 'date_format:H:i', 
                function (string $attribute, mixed $value, \Closure $fail)
                {
                    $service = Service::find($this->service_id);
                    [$start, $end] = $this->timeRange($service->duration);  

                    if (!$service || !$this->employee_id || !$this->date || !Carbon::canBeCreatedFromFormat($this->date, 'Y-m-d')) return;
                    
                    if (Carbon::parse("{$this->date} {$this->hour}")->isPast())
                    {
                        return $fail('Nie mozna zarezerwowac terminu w przeszlosci');
                    }

                    $fits = EmployeeAvailability::where('employee_id', $this->employee_id)
                        ->where('day_of_week', Carbon::parse($this->date)->isoWeekday())
                        ->where('start_time', '<=', $start)
                        ->where('end_time', '>=', $end)
                        ->exists();
                        
                    if (!$fits) {
                        return $fail('Pracownik nie jest dostępny w tym terminie.');
                    }
                    
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

    public function reserve() 
    {
        $service = Service::findOrFail($this->service_id);
        [$start, $end] = $this->timeRange($service->duration);
        
        return DB::transaction(function () use ($start,$end) {
            Employee::whereKey($this->employee_id)->lockForUpdate()->first();
            $taken = Reservation::overlapping($this->employee_id, $this->date, $start, $end)->exists();

            if (Reservation::overlapping($this->employee_id, $this->date, $start, $end)->exists()) {
                throw ValidationException::withMessages([
                    'form.hour' => 'Ten termin jest juz zajety. Wybierz inna godzine.',
                ]);
            }
            Reservation::create([
               'uuid' => (string) Str::uuid(),
               'user_id' => auth()->id(),
               'employee_id' => $this->employee_id,
               'service_id' => $this->service_id,
               'reservation_date' => $this->date,
               'start_time' => substr($start, 0,5),
               'end_time' => substr($end, 0,5),
    
           ]);
        });

    }
    
}

