<?php

namespace App\Livewire\Forms;

use App\Models\Employee;
use App\Models\Service;
use Livewire\Attributes\Validate;
use Livewire\Form;
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
            "date" => ['required', 'date_format:Y-m-d', 'after_or_equal:today']
        ];
    }
    
}

