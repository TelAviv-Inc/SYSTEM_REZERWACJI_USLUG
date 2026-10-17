<?php

use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    // reservation-edit dispatches this after a reschedule or cancel
    protected $listeners = ['reservationUpdated' => '$refresh'];

    #[Computed]
    public function total()
    {
        return auth()->user()->reservations()->count();
    }

    #[Computed]
    public function upcoming()
    {
        return auth()->user()->reservations()->upcoming()->count();
    }
};
?>

<div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4">
    <div
        class="flex flex-col rounded-lg border border-brand-border bg-white px-3 py-2 items-start justify-start sm:px-5 sm:py-4">
        <h3 class="text-lg font-bold sm:text-2xl">{{ $this->total }}</h3>
        <p class="text-sm text-gray-600">Wszystkie</p>
    </div>
    <div
        class="flex flex-col rounded-lg border border-brand-border bg-white px-3 py-2 items-start justify-start sm:px-5 sm:py-4">
        <h3 class="text-lg font-bold sm:text-2xl">{{ $this->upcoming }}</h3>
        <p class="text-sm text-gray-600">Nadchodzące</p>
    </div>
</div>
