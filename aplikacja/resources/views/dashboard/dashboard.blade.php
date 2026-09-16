<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Rezerwacji Usług — Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans bg-brand-bg text-brand-text min-h-screen flex flex-col justify-between" x-data="{ 
        selectedCategory: null, 
        bookingService: null,
        selectedDate: '',
        selectedTime: ''
    }">

    <!-- NAGŁÓWEK -->
    <header class="border-b border-brand-border bg-white">
        <x-navigation />
    </header>

    <!-- GŁÓWNA ZAWARTOŚĆ -->
    <main class="flex-grow flex flex-col p-4">
        <div class="border-b border-brand-border">
            <div class="max-w-7xl mx-auto p-2.5 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold text-brand-navy">Panel użytkownika</h1>
                <p class="mt-2 text-brand-muted">Wybierz usługę, którą chcesz zarezerwować</p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">


            @livewire('show-services')


        </div>

        <!-- SEKCJA POMOCNICZA -->
        <div class="p-6 bg-white rounded-xl shadow-sm border border-brand-border">
            <h2 class="text-lg font-bold text-brand-navy mb-4">Jak wybrać usługę?</h2>
            <ul class="space-y-2 text-brand-muted text-sm">
                <li class="flex items-start">
                    <svg class="h-5 w-5 text-green-500 mr-2 shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Kliknij w powyższą kartę kategorii usług, której potrzebujesz.</span>
                </li>
                <li class="flex items-start">
                    <svg class="h-5 w-5 text-green-500 mr-2 shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Wybierz konkretny zabieg/usługę z listy i kliknij przycisk "Rezerwuj".</span>
                </li>
                <li class="flex items-start">
                    <svg class="h-5 w-5 text-green-500 mr-2 shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Wybierz dogodną datę oraz godzinę w oknie rezerwacji i zatwierdź.</span>
                </li>
            </ul>
        </div>

        </div>
    </main>

    <!-- MODAL REZERWACJI -->
    <x-modal name="booking-modal" maxWidth="md">
        <div class="p-6 space-y-6">
            <div class="flex items-center justify-between border-b border-brand-border pb-3">
                <h3 class="font-bold text-lg text-brand-navy">Termin rezerwacji</h3>
                <button @click="$dispatch('close-modal', 'booking-modal')"
                    class="text-brand-muted hover:text-brand-navy text-xl font-bold">&times;</button>
            </div>

            <div class="bg-brand-light-bg p-4 rounded-lg flex items-center justify-between">
                <div>
                    <span class="text-xs text-brand-muted block">Wybrana usługa:</span>
                    <span class="font-bold text-sm text-brand-navy" x-text="bookingService?.name"></span>
                </div>
                <div class="text-right">
                    <span class="font-bold text-sm text-brand-navy block" x-text="bookingService?.price"></span>
                    <span class="text-xs text-brand-muted" x-text="bookingService?.duration"></span>
                </div>
            </div>

            <form action="#" method="POST" class="space-y-4"
                @submit.prevent="$dispatch('close-modal', 'booking-modal')">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-brand-navy mb-1">Wybierz datę</label>
                    <input type="date" x-model="selectedDate"
                        class="w-full text-sm border-brand-border rounded-md shadow-sm focus:border-brand-accent focus:ring-brand-accent"
                        required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-brand-navy mb-1">Dostępne godziny</label>
                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="time in ['09:00', '10:30', '12:00', '13:30', '15:00', '16:30']">
                            <button type="button" @click="selectedTime = time"
                                :class="selectedTime === time ? 'bg-brand-accent text-white border-brand-accent' : 'bg-white text-brand-navy border-brand-border hover:bg-brand-light-bg'"
                                class="py-2 text-xs font-semibold border rounded-md transition-colors text-center"
                                x-text="time">
                            </button>
                        </template>
                    </div>
                </div>

                <div class="pt-4 border-t border-brand-border flex items-center justify-end gap-3">
                    <button type="button" @click="$dispatch('close-modal', 'booking-modal')"
                        class="px-4 py-2 text-xs font-semibold text-brand-navy bg-white border border-brand-border rounded-md hover:bg-brand-light-bg">
                        Anuluj
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-xs font-semibold text-white bg-brand-accent hover:bg-brand-hover rounded-md shadow-sm">
                        Potwierdzam rezerwację
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- STOPKA -->
    <x-footer />

</body>

</html>