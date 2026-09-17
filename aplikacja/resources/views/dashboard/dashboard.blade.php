<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Rezerwacji Usług — Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans bg-gray-50 text-gray-900">

    <header class="border-b border-gray-200 bg-white">
        <x-navigation />
    </header>


    <main class="flex-grow flex flex-col p-4">
        <div class=" border-b border-gray-200 ">
            <div class="max-w-7xl mx-auto p-2.5 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold text-gray-900">Panel użytkownika</h1>
                <p class="mt-2 text-gray-600">Wybierz usługę, którą chcesz zarezerwować</p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">


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
    </main>


    <x-footer />

</body>

</html>