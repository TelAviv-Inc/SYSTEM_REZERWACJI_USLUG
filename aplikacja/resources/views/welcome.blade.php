<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Rezerwacji Usług</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F6F7F9] text-[#1E293B] min-h-screen flex flex-col justify-between font-sans antialiased">

    <!-- Navbar -->
    <header class="w-full bg-[#FFFFFF] border-b border-[#E2E8F0] px-6 py-4 shadow-sm">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <!-- Logo / Nazwa -->
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-[#2563EB] text-white rounded-lg flex items-center justify-center font-bold shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <span class="text-lg font-bold text-[#111C3D]">System Rezerwacji Usług</span>
            </div>

            <!-- Przyciski nawigacji -->
            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold text-[#2563EB] hover:bg-[#EEF1F6] rounded-lg transition-colors">
                    Zaloguj się
                </a>
                <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-semibold text-white bg-[#2563EB] hover:bg-[#16306F] rounded-lg shadow-sm transition-colors">
                    Zarejestruj się
                </a>
            </div>
        </div>
    </header>

    <!-- Główna zawartość -->
    <main class="flex-grow flex flex-col items-center justify-center px-4 py-12">
        <div class="max-w-4xl mx-auto text-center">
            
            <!-- Header -->
            <h1 class="text-4xl md:text-5xl font-extrabold text-[#111C3D] tracking-tight leading-tight mb-4">
                Umów wizytę, kiedy Ci<br />pasuje
            </h1>
            <p class="text-[#64748B] text-base md:text-lg max-w-2xl mx-auto mb-8">
                Przeglądaj usługi, wybieraj terminy i zarządzaj swoimi rezerwacjami w jednym miejscu.
            </p>

            <!-- Główne przyciski -->
            <div class="flex items-center justify-center gap-4 mb-16">
                <a href="{{ route('register') }}" class="px-6 py-3 text-sm font-semibold text-white bg-[#2563EB] hover:bg-[#16306F] rounded-lg shadow-md transition-colors">
                    Zarejestruj się
                </a>
                <a href="{{ route('login') }}" class="px-6 py-3 text-sm font-semibold text-[#111C3D] bg-[#FFFFFF] hover:bg-[#EEF1F6] border border-[#E2E8F0] rounded-lg shadow-sm transition-colors">
                    Mam już konto
                </a>
            </div>

            <!-- Karty Ról -->
            <div class="grid grid-cols-1 md:grid-cols-3 bg-white border border-[#E2E8F0] rounded-xl shadow-sm overflow-hidden text-left divide-y md:divide-y-0 md:divide-x divide-[#E2E8F0]">
                
                <!-- Klient -->
                <div class="p-6">
                    <span class="text-xs font-bold text-[#2563EB] uppercase tracking-wider block mb-2">Klient</span>
                    <h3 class="text-lg font-bold text-[#111C3D] mb-2">Rezerwuj wizyty</h3>
                    <p class="text-sm text-[#64748B] leading-relaxed">
                        Przeglądaj dostępne usługi i wybieraj dogodne terminy online.
                    </p>
                </div>

                <!-- Pracownik -->
                <div class="p-6">
                    <span class="text-xs font-bold text-[#2563EB] uppercase tracking-wider block mb-2">Pracownik</span>
                    <h3 class="text-lg font-bold text-[#111C3D] mb-2">Zarządzaj grafikiem</h3>
                    <p class="text-sm text-[#64748B] leading-relaxed">
                        Przeglądaj przypisane usługi i potwierdzaj rezerwacje klientów.
                    </p>
                </div>

                <!-- Admin -->
                <div class="p-6">
                    <span class="text-xs font-bold text-[#2563EB] uppercase tracking-wider block mb-2">Administrator</span>
                    <h3 class="text-lg font-bold text-[#111C3D] mb-2">Nadzoruj system</h3>
                    <p class="text-sm text-[#64748B] leading-relaxed">
                        Zarządzaj usługami, pracownikami i wszystkimi rezerwacjami.
                    </p>
                </div>

            </div>
        </div>
    </main>

    <x-footer />

</body>
</html>