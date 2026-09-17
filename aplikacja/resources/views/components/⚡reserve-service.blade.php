<?php

use Livewire\Component;
use App\Models\Service;
new class extends Component {
    public $selectedService = [];
    public $employees = [];

    protected $listeners = ['serviceChosen'];

    public function serviceChosen($serviceID)
    {
        $this->selectedService = Service::with('employees')->find($serviceID);
        $this->employees = $this->selectedService->employees;
    }

    public function close()
    {
        $this->reset();
    }

    public function render()
    {
        return view('components.reserve-service', ['selectedService' => $this->selectedService, 'employees' => $this->employees]);
    }
};
?>

<div>
    {{-- Life is available only in the present moment. - Thich Nhat Hanh --}}
</div>