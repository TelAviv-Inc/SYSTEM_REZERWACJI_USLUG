<?php

use Livewire\Component;

new class extends Component {
    public string $option = 'employees';
};
?>
<div class="flex flex-col mt-4">
    <div class="flex justify-start">
        <button type="button" wire:click="$set('option', 'employees')" @class([
            'font-semibold text-base cursor-pointer border-b-2 p-2 transition',
            'text-brand-accent border-brand-accent' => $option === 'employees',
            'text-brand-text border-brand-border' => $option !== 'employees',
        ])>Pracownicy
        </button>
        <button type="button" wire:click="$set('option', 'users')" @class([
            'font-semibold text-base cursor-pointer border-b-2 p-2 transition',
            'text-brand-accent border-brand-accent' => $option === 'users',
            'text-brand-text border-brand-border' => $option !== 'users',
        ])>Uzytkownicy
        </button>
        <button type="button" wire:click="$set('option', 'categories')" @class([
            'font-semibold text-base cursor-pointer border-b-2 p-2 transition',
            'text-brand-accent border-brand-accent' => $option === 'categories',
            'text-brand-text border-brand-border' => $option !== 'categories',
        ])>Kategorie
        </button>
        <button type="button" wire:click="$set('option', 'services')" @class([
            'font-semibold text-base cursor-pointer border-b-2 p-2 transition',
            'text-brand-accent border-brand-accent' => $option === 'services',
            'text-brand-text border-brand-border' => $option !== 'services',
        ])>Uslugi
        </button>
    </div>

    @if ($option === 'users')
        @livewire('admin-users')
    @elseif ($option === 'employees')
        @livewire('admin-employees')
    @endif

</div>
