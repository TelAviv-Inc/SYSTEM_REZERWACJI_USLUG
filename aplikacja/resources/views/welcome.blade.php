<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Rezerwacji Usług</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-brand-bg text-brand-text min-h-screen flex flex-col justify-between">

    <!-- NAGŁÓWEK / NAVBAR -->
    <header class="bg-white border-b border-brand-border w-full">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">

            <!-- Logo + Nazwa -->
            <a href="{{ url('/') }}"
                class="flex items-center gap-2.5 text-brand-navy font-bold text-base hover:opacity-90 transition-opacity">
                <div
                    class="w-8 h-8 bg-gradient-to-r from-primary to-secondary rounded-lg flex items-center justify-center text-white">
                    <i class="fa-regular fa-user"></i>
                </div>
                <span>System Rezerwacji Usług</span>
            </a>

            <!-- Przyciski po prawej -->
            <div class="flex items-center gap-2">
                <a href="{{ route('login') }}"
                    class="px-4 py-2 text-sm font-semibold text-brand-accent hover:bg-brand-light-bg rounded-md transition-colors">
                    Zaloguj się
                </a>
                <a href="{{ route('register') }}"
                    class="px-4 py-2 text-sm font-semibold text-white bg-brand-accent hover:bg-brand-hover rounded-md transition-colors shadow-sm">
                    Zarejestruj się
                </a>
            </div>

        </div>
    </header>

    <!-- GŁÓWNA SEKCJA (HERO & KARTY) -->
    <main class="flex-grow flex flex-col items-center justify-center px-4 py-12">

        <!-- Sekcja nagłówkowa -->
        <div class="max-w-xl text-center mb-10">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-brand-navy tracking-tight mb-3">
                Umów wizytę, kiedy Ci pasuje
            </h1>
            <p class="text-brand-muted text-sm sm:text-base leading-relaxed mb-6">
                Przeglądaj usługi, wybieraj terminy i zarządzaj swoimi rezerwacjami w jednym miejscu.
            </p>

            <!-- Przyciski akcji głównej -->
            <div class="flex items-center justify-center gap-3">
                <a href="{{ route('register') }}"
                    class="px-6 py-2.5 text-sm font-semibold text-white bg-brand-accent hover:bg-brand-hover rounded-md transition-colors shadow-sm">
                    Zarejestruj się
                </a>
                <a href="{{ route('login') }}"
                    class="px-6 py-2.5 text-sm font-semibold text-brand-text bg-white border border-brand-border hover:bg-brand-light-bg rounded-md transition-colors shadow-sm">
                    Mam już konto
                </a>
            </div>
        </div>

        <!-- Karty informacji (Klient / Pracownik / Administrator) -->
        <div
            class="w-full max-w-4xl bg-white rounded-xl border border-brand-border shadow-sm grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-brand-border overflow-hidden">

            <!-- Klient -->
            <div class="p-6 flex flex-col">
                <span
                    class="text-xs font-bold text-brand-accent bg-[#eff6ff] uppercase tracking-wider mb-2 py-1.5 px-2 rounded-lg self-start">
                    Klient
                </span>
                <h3 class="text-base font-bold text-brand-navy mb-2">
                    Rezerwuj wizyty
                </h3>
                <p class="text-xs text-brand-muted leading-relaxed">
                    Przeglądaj dostępne usługi i wybieraj dogodne terminy online.
                </p>
            </div>

            <!-- Pracownik -->
            <div class="p-6 flex flex-col">
                <span
                    class="text-xs font-bold text-brand-accent bg-[#eff6ff] uppercase tracking-wider mb-2 py-1.5 px-2 rounded-lg self-start">
                    Pracownik
                </span>
                <h3 class="text-base font-bold text-brand-navy mb-2">
                    Zarządzaj grafikiem
                </h3>
                <p class="text-xs text-brand-muted leading-relaxed">
                    Przeglądaj przypisane usługi i potwierdzaj rezerwacje klientów.
                </p>
            </div>

            <!-- Administrator -->
            <div class="p-6 flex flex-col">
                <span
                    class="text-xs font-bold text-brand-accent bg-[#eff6ff] uppercase tracking-wider mb-2 py-1.5 px-2 rounded-lg self-start">
                    Administrator
                </span>
                <h3 class="text-base font-bold text-brand-navy mb-2">
                    Nadzoruj system
                </h3>
                <p class="text-xs text-brand-muted leading-relaxed">
                    Zarządzaj usługami, pracownikami i wszystkimi rezerwacjami.
                </p>
            </div>

        </div>

    </main>

    <!-- KOMPONENT STOPKI -->
    <x-footer />

</body>

</html>