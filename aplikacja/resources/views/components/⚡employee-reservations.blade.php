<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use App\Models\Reservation;


new class extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'reservation_status')]
    public string $status = '';

    #[Url]
    public string $service = '';

    #[Url]
    public string $category = '';

    #[Url(as: 'reservations_from')]
    public string $from = '';

    #[Url(as: 'reservations_to')]
    public string $to = '';

    #[Url(as: 'reservations_order')]
    public string $direction = 'desc';



    //Compute only this employee reservations
    //Make this apply filters
    #[Computed]
    public function reservations()
    {
        return $this->employee->reservations()->latest('reservation_date')->paginate(10, pageName: 'reservations_page');
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'status', 'service', 'category', 'from', 'to', 'diraction']))
        {
            $this->resetPage('reservations_page');
        }
    }
    
};
?>

<div>
    {{-- Smile, breathe, and go slowly. - Thich Nhat Hanh --}}
</div>