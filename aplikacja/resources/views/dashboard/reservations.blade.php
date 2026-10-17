<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Rezerwacji Usług — Rezerwacje</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans bg-gray-50 text-gray-900">
    <header class="border-b border-gray-200 bg-white">
        <x-navigation />
    </header>
    <main class="flex-grow flex flex-col p-4">
        <div class=" border-b border-gray-200 ">
            <div class="max-w-7xl flex flex-col gap-2 justify-between mx-auto p-2.5 sm:px-6 lg:px-8 md:flex-row">
                <div class="flex flex-col justify-start items-start">
                    <h1 class="text-3xl font-bold text-gray-900">Moje rezerwacje</h1>
                    <p class="mt-2 text-gray-600">Przeglądaj, zmieniaj termin lub anuluj swoje wizyty.</p>
                </div>
                <div class="flex items-center">
                    <a href="{{ route('dashboard.index') }}"
                        class="inline-flex items-center justify-center cursor-pointer gap-2 w-full sm:w-auto px-5 py-2.5 rounded-lg bg-brand-accent text-white font-semibold shadow-md transition duration-200 hover:brightness-110 hover:shadow-lg hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-brand-accent focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:translate-y-0">
                        <i class="fa-solid fa-plus text-sm"></i>
                        Nowa rezerwacja
                    </a>
                </div>
            </div>
        </div>
        <!-- Podsumowanie Rezerwacji -->
        <div class="w-full max-w-7xl mx-auto p-2.5 sm:px-6 lg:px-8 py-8">
            @php
                $user = auth()->user();
                $totalReservations = $user->reservations()->count();
                $upcomingReservations = $user->reservations()->confirmed()->upcoming()->count();
            @endphp
            @if ($totalReservations)
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4">
                    <div
                        class="flex flex-col rounded-lg border border-brand-border bg-white px-3 py-2 items-start justify-start sm:px-5 sm:py-4">
                        <h3 class="text-lg font-bold sm:text-2xl">{{ $totalReservations }}</h3>
                        <p class="text-sm text-gray-600">Wszystkie</p>
                    </div>
                    <div
                        class="flex flex-col rounded-lg border border-brand-border bg-white px-3 py-2 items-start justify-start sm:px-5 sm:py-4">
                        <h3 class="text-lg font-bold sm:text-2xl">{{ $upcomingReservations }}</h3>
                        <p class="text-sm text-gray-600">Nadchodzące</p>
                    </div>
                </div>
                @livewire('reservations-menu')
            @else
                <div class="justify-start py-4 items-start">
                    <h2 class="text-md text-gray-500 italic sm:col-span-2">Najpierw dokonaj rezerwacji</h2>
                </div>
            @endif


        </div>
    </main>
    <x-footer />
</body>

</html>