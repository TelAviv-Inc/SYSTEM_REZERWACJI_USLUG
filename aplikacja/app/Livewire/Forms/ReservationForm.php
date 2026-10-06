<?php

namespace App\Livewire\Forms;

use App\Models\Employee;
use App\Models\Service;
use Livewire\Attributes\Validate;
use Livewire\Form;

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
            "employee_id" => ['required', 'exists:employees,uuid'],
            "date" => ['required', 'date_format:Y-m-d', 'after_or_equal:today']
        ];
    }
    
}

