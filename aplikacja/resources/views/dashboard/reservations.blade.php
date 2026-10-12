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
            <div class="max-w-7xl flex flex-col mx-auto p-2.5 sm:px-6 lg:px-8 ">
                <h1 class="text-3xl font-bold text-gray-900">Moje rezerwacje</h1>
                <p class="mt-2 text-gray-600">Przeglądaj, zmieniaj termin lub anuluj swoje wizyty.</p>
                <div>
                    <button></button>
                </div>
            </div>
        </div>
    </main>
    <x-footer />
</body>

</html>