<x-layouts.dashboard title="Moje rezerwacje" subtitle="Przeglądaj, zmieniaj termin lub anuluj swoje wizyty.">
    <x-slot:actions>
        <a href="{{ route('dashboard.index') }}"
            class="inline-flex items-center justify-center cursor-pointer gap-2 w-full sm:w-auto px-5 py-2.5 rounded-lg bg-brand-accent text-white font-semibold shadow-md transition duration-200 hover:brightness-110 hover:shadow-lg hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-brand-accent focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:translate-y-0">
            <i class="fa-solid fa-plus text-sm"></i>
            Nowa rezerwacja
        </a>
    </x-slot:actions>

    <div class="w-full p-2.5 py-8">
        @if (auth()->user()->reservations()->exists())
            @livewire('reservations-summary')
            @livewire('reservations-menu')
            @livewire('reservation-edit')
        @else
            <div class="justify-start py-4 items-start">
                <h2 class="text-md text-gray-500 italic sm:col-span-2">Najpierw dokonaj rezerwacji</h2>
            </div>
        @endif
    </div>
</x-layouts.dashboard>
