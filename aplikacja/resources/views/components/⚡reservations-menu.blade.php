<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
new class extends Component {
    public string $option = 'upcoming';
    public string $sort = 'nearest';

    // reservation-edit dispatches this after a reschedule or cancel
    protected $listeners = ['reservationUpdated' => '$refresh'];

    #[Computed]
    public function reservations()
    {
        $query = auth()
            ->user()
            ->reservations()
            ->select('reservations.*')
            ->with(['service.category', 'employee.user']);

        if ($this->option === 'archived') {
            $query->where(function ($q) {
                $q->past()->orWhereIn('status', ['completed', 'cancelled']);
            });
        } else {
            $query->upcoming();
        }

        if (in_array($this->sort, ['category', 'price'])) {
            $query->join('services', 'services.uuid', '=', 'reservations.service_id');
        }

        match ($this->sort) {
            'category' => $query->leftJoin('service_categories', 'service_categories.uuid', '=', 'services.category_id')->orderBy('service_categories.name'),
            'price' => $query->orderBy('services.price'),
            'furthest' => $query->orderByDesc('reservation_date')->orderByDesc('start_time'),
            default => $query->orderBy('reservation_date')->orderBy('start_time'),
        };

        return $query->get();
    }
};
?>

<div class="flex flex-col mt-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex justify-start">
            <button type="button" wire:click="$set('option', 'upcoming')" @class([
                'font-semibold text-base cursor-pointer border-b-2 p-2 transition',
                'text-brand-accent border-brand-accent' => $option === 'upcoming',
                'text-brand-text border-brand-border' => $option !== 'upcoming',
            ])>Nadchodzące
            </button>
            <button type="button" wire:click="$set('option', 'archived')" @class([
                'font-semibold text-base cursor-pointer border-b-2 p-2 transition',
                'text-brand-accent border-brand-accent' => $option === 'archived',
                'text-brand-text border-brand-border' => $option !== 'archived',
            ])>Archiwalne
            </button>
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-600">
            Sortuj:
            <select wire:model.live="sort"
                class="rounded-lg border border-brand-border bg-white px-3 py-1.5 text-sm text-brand-text focus:outline-none focus:ring-2 focus:ring-brand-accent">
                <option value="nearest">Najbliższe</option>
                <option value="furthest">Najdalsze</option>
                <option value="category">Kategoria</option>
                <option value="price">Cena</option>
            </select>
        </label>
    </div>

    <div class="mt-4 flex flex-col gap-3">
        @forelse ($this->reservations as $reservation)
            <div wire:key="{{ $reservation->uuid }}"
                class="flex flex-col gap-2 rounded-lg border border-brand-border bg-white px-3 py-2 sm:flex-row sm:items-center sm:justify-between sm:px-5 sm:py-4">
                <div class="flex flex-col">
                    <h3 class="font-bold">{{ $reservation->service->name }}</h3>
                    <p class="text-sm text-gray-600">
                        {{ $reservation->service->category?->name }}
                        · {{ $reservation->employee->user?->name }} {{ $reservation->employee->user->surname }}
                    </p>
                </div>
                <div class="flex flex-col gap-8 lg:flex-row sm:items-end">
                    <div class="flex flex-col sm:items-end">
                        <p class="font-semibold">{{ $reservation->full_date_time }}</p>
                        <p class="text-sm text-gray-600">
                            {{ number_format($reservation->service->price, 2, ',', ' ') }} zł
                            · {{ $reservation->status_label }}
                        </p>
                    </div>
                    <button type="button" wire:key="{{ $reservation->uuid }}"
                        wire:click="$dispatch('editReservation', { uuid: '{{ $reservation->uuid }}' })"
                        aria-label="Szczegóły rezerwacji"
                        class="inline-flex items-center justify-center cursor-pointer gap-2 w-full sm:w-auto px-5 py-2.5 rounded-lg bg-brand-accent text-white font-semibold shadow-md transition duration-200 hover:brightness-110 hover:shadow-lg hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-brand-accent focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:translate-y-0">
                        <i class="fa-regular fa-pen-to-square"></i>
                    </button>
                </div>

            </div>
        @empty
            <p class="py-4 text-gray-500 italic">Brak rezerwacji.</p>
        @endforelse
    </div>
</div>
