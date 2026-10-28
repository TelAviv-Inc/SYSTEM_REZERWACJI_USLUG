<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Support\AdminStats;
new class extends Component {
    public int $weeks = 12;

    public function updatedWeeks(): void
    {
        $this->weeks = AdminStats::period($this->weeks);
    }

    #[Computed]
    public function reservations(): array
    {
        return AdminStats::weeklyReservations($this->weeks);
    }

    #[Computed]
    public function income(): array
    {
        return AdminStats::weeklyIncome($this->weeks);
    }
};
?>

<div class="flex flex-col mt-4">
    <div class="flex sm:justify-end">
        <label class="flex w-full flex-col gap-1 text-sm text-gray-600 sm:w-auto sm:flex-row sm:items-center sm:gap-2">
            Okres podsumowania:
            <select wire:model.live="weeks"
                class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-sm text-brand-text focus:outline-none focus:ring-2 focus:ring-brand-accent sm:w-auto sm:py-1.5">
                @foreach (AdminStats::PERIODS as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>
    </div>

    <div class="grid gap-6 mt-6 lg:grid-cols-2">
        <div class="h-64 min-w-0 sm:h-72" wire:key="reservations-{{ $weeks }}" x-data="statsChart({ type: 'bar', label: 'Liczba rezerwacji', ...@js($this->reservations) })">
            <canvas x-ref="canvas" wire:ignore></canvas>
        </div>

        <div class="h-64 min-w-0 sm:h-72" wire:key="income-{{ $weeks }}" x-data="statsChart({ type: 'line', label: 'Przychód', money: true, ...@js($this->income) })">
            <canvas x-ref="canvas" wire:ignore></canvas>
        </div>
    </div>
</div>
