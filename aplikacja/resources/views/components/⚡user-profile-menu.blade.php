<?php

use Livewire\Component;
use Livewire\Attributes\On;
new class extends Component {
    public ?string $option = 'info';

};
?>

<div class="flex flex-col mt-4 ">
    <div class="flex justify-start">
        <button type="button" wire:click="$set('option', 'personal')"
            class="font-semibold text-base text-brand-text cursor-pointer border-b-2 border-brand-border p-2 transition delay-300 focus:text-brand-accent focus:border-brand-accent">Dane
            osobowe
        </button>
        <button type="button" wire:click="$set('option', 'changePassword')"
            class="font-semibold text-base text-brand-text cursor-pointer border-b-2 border-brand-border p-2 transition delay-300 focus:text-brand-accent focus:border-brand-accent">Zmiana
            hasla
        </button>
        <button type="button" wire:click="$set('option', 'info')"
            class="font-semibold text-base text-brand-text cursor-pointer border-b-2 border-brand-border p-2 transition delay-300 focus:text-brand-accent focus:border-brand-accent">Informacje
            o koncie
        </button>
        <button type="button" wire:click="$set('option', 'settings')"
            class="font-semibold text-base text-brand-text cursor-pointer border-b-2 border-brand-border p-2 transition delay-300 focus:text-brand-accent focus:border-brand-accent">Ustawienia
            
        </button>
    </div>

    <div class="mt-4">
        @switch($option)
            @case('info')
                @livewire('profile-information', [], key('info'))
                @break
            
            @case('personal')
                @livewire('profile-data', [], key('personal'))
                @break
            @default <p>huj</p>
        @endswitch
    </div>

</div>