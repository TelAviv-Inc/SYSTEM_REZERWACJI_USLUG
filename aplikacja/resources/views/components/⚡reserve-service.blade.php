<?php

use Livewire\Component;
use App\Models\Service;
use App\Models\Employee;
new class extends Component {
    public $employees = [];
    public ?Service $selectedService = null;
    public ?Employee $selectedEmployee = null;
    public ?string $selectedDate = null;

    protected $listeners = ['serviceChosen'];

    public function serviceChosen($serviceID)
    {
        $this->selectedService = Service::with('employees.availability')->find($serviceID);
        $this->employees = $this->selectedService->employees;
    }

    public function selectEmployee($employeeUuid)
    {
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
    public function close()
    {
        $this->reset();
    }

    public function render()
    {
        return view('components.reserve-service', ['selectedService' => $this->selectedService, 'employees' => $this->employees, 'selectedEmployee' => $this->selectedEmployee]);
    }
};
?>

<div>
    {{-- Life is available only in the present moment. - Thich Nhat Hanh --}}
</div>