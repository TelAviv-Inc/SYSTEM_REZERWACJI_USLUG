@props(['title', 'subtitle' => null])

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Rezerwacji Usług — {{ $title }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans bg-gray-50 text-gray-900">

    <header class="border-b border-gray-200 bg-white">
        <x-navigation />
    </header>

    <main class="flex grow flex-col px-4 py-4 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="border-b border-gray-200">
            <div class="max-w-7xl mx-auto p-2.5">
                <h1 class="text-3xl font-bold text-gray-900">{{ $title }}</h1>
                @if ($subtitle)
                    <p class="mt-2 text-gray-600">{{ $subtitle }}</p>
                @endif
            </div>
        </div>

        {{ $slot }}
    </main>

    <x-footer />
</body>

</html>
