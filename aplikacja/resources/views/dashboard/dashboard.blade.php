<x-layouts.dashboard title="Panel użytkownika" subtitle="Wybierz usługę, którą chcesz zarezerwować">
    <div class="py-8">
        @livewire('show-services')
        @livewire('reserve-service')

        <!-- Additional info -->
        <div class="mt-12 p-6 bg-white rounded-md shadow-md border border-gray-200">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Jak wybrać usługę?</h2>
            <ul class="space-y-2 text-gray-600">
                <li class="flex items-start">
                    <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Wybierz kategorię usług, której potrzebujesz</span>
                </li>
                <li class="flex items-start">
                    <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Kliknij "Wybierz usługę" i przejdź do wyboru konkretnych usług</span>
                </li>
                <li class="flex items-start">
                    <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Skonfiguruj termin rezerwacji i potwierdź</span>
                </li>
            </ul>
        </div>
    </div>
</x-layouts.dashboard>
