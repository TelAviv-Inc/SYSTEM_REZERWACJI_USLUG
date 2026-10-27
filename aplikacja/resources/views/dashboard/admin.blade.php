<x-layouts.dashboard title="Panel administratora" subtitle="Zarzadzaj systemem.">
    <x-slot:actions>
        <a href="{{ route('dashboard.admin.reports') }}"
            class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-5 py-2.5 rounded-lg bg-brand-accent text-white font-semibold shadow-md transition duration-200 hover:brightness-110 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-brand-accent focus:ring-offset-2">
            <i class="fa-solid fa-chart-column text-sm"></i>
            Raporty
        </a>
    </x-slot:actions>

    <div class="w-full p-2.5 py-8">
        @livewire('admin-stats')
        @livewire('admin-menu')
    </div>
</x-layouts.dashboard>
