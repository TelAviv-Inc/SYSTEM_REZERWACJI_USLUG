<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Rezerwacji Usług — Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>


<body class="font-sans bg-gray-50 text-gray-900">

    <header class="border-b border-gray-200 bg-white">
        <x-navigation />
    </header>
    <main class="flex flex-grow flex-col px-4 py-4 max-w-7xl mx-auto sm:px-6 lg:px-8 ">
        <div class=" border-b border-gray-200 ">
            <div class="max-w-7xl mx-auto p-2.5">
                <h1 class="text-3xl font-bold text-gray-900">Moje konto</h1>
                <p class="mt-2 text-gray-600">Zarządzaj swoimi danymi, hasłem i ustawieniami konta.</p>
            </div>
        </div>
        <div class="mt-2 flex gap-4 bg-white p-2 items-center rounded-lg border border-brand-border">
            @php
                $role = auth()->user()->role;
                $mail = auth()->user()->email;
                $username = auth()->user()->name . " " . auth()->user()->surname;
                $roleClasses = match ($role) {
                    'admin' => 'text-admin-text bg-admin-bg',
                    'employee' => 'text-employee-text bg-employee-bg',
                    default => 'text-brand-accent bg-client-bg',
                };
            @endphp
            <p
                class="w-16 h-16 shrink-0 rounded-full {{ $roleClasses }} flex items-center justify-center font-bold text-2xl">
                {{ substr(auth()->user()->name ?? 'P', 0, 1) }} 
            </p>

            <div class="flex flex-col gap-1 justify-start items-start">
                <span class="text-md font-bold text-brand-navy truncate">{{$username}}</span>
                <span class="text-sm text-brand-muted">{{$mail}}</span>
            </div>

        </div>
        <div class="mt-4 grid grid-cols-2 gap-2 md:grid-cols-1">
            <div
                class="flex flex-col rounded-lg border border-brand-border bg-white px-3 py-2 items-start justify-start">
                <p class=" text-sm text-gray-600">Konto zalozone</p>
                <h3 class="text-lg font-bold">{{ auth()->user()->created_at->format("d M Y") }}</h3>
            </div>
            <div
                class="flex flex-col rounded-lg border border-brand-border bg-white px-3 py-2 items-start justify-start">
                <p class=" text-sm text-gray-600 md:text-md">Status konta</p>
                @php $active = auth()->user()->active @endphp
                <h3 class="text-lg font-bold {{ $active ? 'text-green-600' : 'text-red-600' }}">
                    &bull; {{ $active ? 'Aktywne' : 'Nieczynne' }}
                </h3>
            </div>
            <div
                class="flex flex-col rounded-lg border border-brand-border bg-white px-3 py-2 items-start justify-start">
                <p class=" text-sm text-gray-600 md:text-md">Ostatnia zmiana</p>
                <h3 class="text-lg font-bold">{{ auth()->user()->updated_at->format("d M Y") }}</h3>
            </div>
            <div
                class="flex flex-col rounded-lg border border-brand-border bg-white px-3 py-2 items-start justify-start">
                <p class=" text-sm text-gray-600 md:text-md">Rezerwacje</p>
                @if (!auth()->user()->reservations()->exists())
                    <h3 class="text-base text-gray-500 italic sm:col-span-2">Brak aktywnych rezerwacji</h3>
                @else
                    @php
                        $totalReservations = auth()->user()->reservations()->count();
                        $pendingReservations = auth()->user()->reservations()
                            ->confirmed()
                            ->where(function ($q) {
                                $q->whereDate('reservation_date', '>', today())
                                    ->orWhere(function ($q) {
                                        $q->whereDate('reservation_date', today())
                                            ->whereTime('start_time', '>', now()->format('H:i:s'));
                                    });
                            })
                            ->count();
                    @endphp
                    <h3 class="text-lg font-bold">{{ $totalReservations }} łącznie &middot; {{ $pendingReservations }}
                        nadchodzące</h3>
                @endif
            </div>
        </div>
        @livewire('user-profile-menu')
    </main>
    <x-footer />
</body>

</html>