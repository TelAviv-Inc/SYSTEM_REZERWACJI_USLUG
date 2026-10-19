<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
new class extends Component
{
    public int $weeks = 12; 

    public function updatedWeeks(): void
    {
        if (!in_array($this->weeks, [4,12,52], true)) {
            $this->weeks = 12;
        }
    }
};
?>

<div class="flex flex-col mt-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <label class="flex items-center gap-2 text-sm text-gray-600">
            Okres podsumowania:
            <select wire:model.live="weeks"
                class="rounded-lg border border-brand-border bg-white px-3 py-1.5 text-sm text-brand-text focus:outline-none focus:ring-2 focus:ring-brand-accent">
                <option value="4">4 tygodnie</option>
                <option value="12">12 tygodni</option>
                <option value="52">52 tygodnie</option>
            </select>
        </label>
    </div>
</div>