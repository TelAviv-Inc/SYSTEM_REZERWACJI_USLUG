<?php

use Livewire\Component;
use App\Models\Service;
use Livewire\Attributes\On;
use App\Models\Employee;
new class extends Component {
    public $employees = [];
    public ?Service $selectedService = null;
    public ?Employee $selectedEmployee = null;
    public ?string $selectedDay = null;
    public ?string $selectedHour = null;
    public bool $showHourCalendar = false;

    #[On('serviceChosen')]
    public function serviceChosen($serviceID)
    {
        $this->selectedService = Service::with('employees.availability')->find($serviceID);
        $this->employees = $this->selectedService->employees;
    }

    public function selectEmployee($employeeUuid)
    {
        $this->reset('selectedDay', 'selectedHour');
        $this->selectedEmployee = $this->employees->firstWhere('uuid', $employeeUuid);
        $this->dispatch(
            'employee-selected',
            days: $this->selectedEmployee
                ->availability
                ->pluck('day_of_week')
                ->unique()
                ->values()
                ->all()
        );
    }
    #[On('daySelected')]
    public function setDate($date)
    {
        $this->selectedDay = $date;
        $this->reset('selectedHour');
        $this->showHourCalendar = true;
        $this->dispatch(
            'employee-work-time-data',
            working_hours: $this->selectedEmployee
                ->availability
                ->map(fn($a) => [
                    'start_time' => $a->start_time,
                    'end_time' => $a->end_time,
                ])
                ->unique()
                ->values()
                ->all()
        );
    }

    #[On('hourSelected')]
    public function setHour($time)
    {
        $this->selectedHour = $time;
    }

    public function reserve()
    {

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

    @if($showHourCalendar && $selectedDay)
        @livewire('employee-hour-calendar', ['selectedDay' => $selectedDay])
    @endif
</div>