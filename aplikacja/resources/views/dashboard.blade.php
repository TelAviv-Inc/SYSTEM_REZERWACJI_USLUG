<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Rezerwacji Usług — Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans bg-gray-50 text-gray-900">

    <header class="border-b border-gray-200 bg-white">
        <nav class="flex items-center justify-between px-6 py-4 max-w-7xl mx-auto">
            <div class="flex items-center gap-2.5 font-bold text-lg">
                <span class="w-8 h-8 rounded-lg flex items-center justify-center bg-brand-accent">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M12 12a4 4 0 100-8 4 4 0 000 8zM4 20c0-3.3 3.6-6 8-6s8 2.7 8 6" stroke="#fff"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
                System Rezerwacji Usług
            </div>
            <div class="flex items-center gap-2.5">
                <!-- User Dropdown -->
                <div class="relative group">
                    <button class="flex items-center gap-2 text-gray-700 font-medium cursor-pointer">
                        <i class="fas fa-user-circle text-xl"></i>
                        <span>Jan Kowalski</span>
                        <i class="fas fa-chevron-down ml-1"></i>
                    </button>
                    <div
                        class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block z-10 border border-gray-200">
                        <a href="#" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i
                                class="fas fa-user"></i>Profil</a>
                        <a href="#" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i
                                class="fas fa-sign-out-alt"></i>Wyloguj</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>


    <main class="flex-grow flex flex-col p-4">
        <div class=" border-b border-gray-200 ">
            <div class="max-w-7xl mx-auto p-2.5 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold text-gray-900">Panel użytkownika</h1>
                <p class="mt-2 text-gray-600">Wybierz usługę, którą chcesz zarezerwować</p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Service Category Cards -->
                @foreach ($categories as $category)
                    <x-service-category-card :name="$category->name" :description="$category->description"
                        :icon="$category->icon" />

                @endforeach

            </div>

            <!-- Additional info -->
            <div class="mt-12 p-6 bg-white rounded-xl shadow-md border border-gray-200">
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