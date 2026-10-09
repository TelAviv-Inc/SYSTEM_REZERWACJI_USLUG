<?php

use Livewire\Component;

new class extends Component {

};
?>

<div class="mt-2">
    <div class="rounded-lg bg-white border border-brand-border px-3 py-2">
        <div class="flex flex-col gap-1.5 ">
            <h2 class="text-lg font-bold text-brand-text">Informacje o koncie</h2>
            <p class="text-sm italic text-brand-muted">Dane tylko do odczytu.</p>
        </div>

        <div class="flex justify-start items-start gap-4">
            <p class="w-20 shrink-0 text-brand-muted">ID</p>
            <span class="font-semibold">{{ auth()->user()->uuid }}</span>
        </div>
        <div class="flex justify-start items-start gap-4">
            <p class="w-20 shrink-0 text-brand-muted">Typ Konta</p>
            <span class="uppercase font-semibold">{{ auth()->user()->role }}</span>
        </div>

    </div>

</div>