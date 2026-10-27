<?php

use Livewire\Component;
use App\Models\Service;
use App\Models\Reservation;
use Livewire\Attributes\On;
use App\Models\Employee;
use App\Livewire\Forms\ReservationForm;
new class extends Component {
    public $employees = [];
    public ?Service $selectedService = null;
    public ?Employee $selectedEmployee = null;
    public ?string $selectedDay = null;
    public ?string $selectedHour = null;
    public bool $showHourCalendar = false;
    public ReservationForm $form;
    public bool $reserved = false;

    #[On('serviceChosen')]
    public function serviceChosen($serviceID)
    {
        $this->selectedService = Service::with([
            'employees' => fn($q) => $q->where('employees.active', 1),
            'employees.availability',
        ])
            ->where('active', 1)
            ->findOrFail($serviceID);
        $this->employees = $this->selectedService->employees;
    }

    public function selectEmployee($employeeUuid)
    {
        $employee = $this->selectedService?->employees->firstWhere('uuid', $employeeUuid);
        if (!$employee) {
            return;
        }
        $this->reset('selectedDay', 'selectedHour');
        $this->selectedEmployee = $employee;
        $this->dispatch('employee-selected', days: $this->selectedEmployee->availability->pluck('day_of_week')->unique()->values()->all());
    }
    #[On('daySelected')]
    public function setDate($date)
    {
        if (!$this->selectedEmployee) {
            return;
        }

        $this->selectedDay = $date;
        $this->reset('selectedHour');
        $this->showHourCalendar = true;
        $this->dispatch(
            'employee-work-time-data',
            working_hours: $this->selectedEmployee->availability
                ->map(
                    fn($a) => [
                        'start_time' => $a->start_time,
                        'end_time' => $a->end_time,
                    ],
                )
                ->unique()
                ->values()
                ->all(),
        );
    }

    #[On('hourSelected')]
    public function setHour($time)
    {
        $this->selectedHour = $time;
    }

    public function reserve()
    {
        //Sprawdzanie godziny musi byc w transakcji zeby nie bylo race condition DB::transaction()
        $this->form->fill([
            'service_id' => $this->selectedService?->uuid,
            'employee_id' => $this->selectedEmployee?->uuid,
            'date' => $this->selectedDay,
            'hour' => $this->selectedHour,
        ]);
        $this->form->validate();
        $this->authorize('create', [Reservation::class, $this->selectedEmployee]);
        $this->form->reserve();
        $this->reserved = true;
        session()->flash('status', 'Rezerwacja dokonana pomyslnie');
        $this->js('setTimeout(() => $wire.close(), 1000)');
    }

    public function close()
    {
        $this->reset();
    }

    public function render()
    {
        return view('components.reserve-service', ['selectedService' => $this->selectedService, 'employees' => $this->employees, 'selectedEmployee' => $this->selectedEmployee, 'selectedDay' => $this->selectedDay]);
    }
};
?>

<div>
    {{-- Life is available only in the present moment. - Thich Nhat Hanh --}}

    @if ($showHourCalendar && $selectedDay)
        @livewire('employee-hour-calendar', ['selectedDay' => $selectedDay])
    @endif
</div>
