<x-layouts.dashboard title="Raporty" subtitle="Rezerwacje, przychody i obłożenie według kategorii, usług i pracowników.">
    <x-slot:actions>
        <a href="{{ route('dashboard.admin') }}"
            class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-5 py-2.5 rounded-lg border border-brand-border bg-white text-brand-text font-semibold transition hover:bg-brand-light-bg print:hidden">
            <i class="fa-solid fa-arrow-left text-sm"></i>
            Panel administratora
        </a>
    </x-slot:actions>

    <div class="w-full p-2.5 py-8">
        @livewire('admin-reports')
    </div>
</x-layouts.dashboard>
