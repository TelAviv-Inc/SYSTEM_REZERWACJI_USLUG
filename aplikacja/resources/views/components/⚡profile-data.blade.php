<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>


<div class="mt-2">
    <div class="rounded-lg bg-white border border-brand-border px-3 py-2">
        <div class="flex flex-col gap-1.5 ">
            <h2 class="text-lg font-bold text-brand-text">Dane osobiste</h2>
            <p class="text-sm italic text-brand-muted">Dane tylko do odczytu.</p>
        </div>

        <form action="" method="post" class="grid grid-cols-1 gap-4 mt-3 pb-2 sm:grid-cols-2">
            <div class="flex flex-col gap-1.5">
                <label for="email" class="text-sm font-medium text-brand-muted">Adres e-mail</label>
                <input type="email" name="email" id="email" value="{{ auth()->user()->email }}"
                    autocomplete="email"
                    class="w-full rounded-lg border border-brand-border bg-brand-bg px-3 py-2.5 text-brand-text font-semibold placeholder:text-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-accent focus:border-transparent transition duration-200">
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="phone" class="text-sm font-medium text-brand-muted">Numer telefonu</label>
                <input type="tel" name="phone" id="phone" value="{{ auth()->user()->phone }}"
                    autocomplete="tel"
                    class="w-full rounded-lg border border-brand-border bg-brand-bg px-3 py-2.5 text-brand-text font-semibold placeholder:text-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-accent focus:border-transparent transition duration-200">
            </div>
        </form>
    </div>

</div>