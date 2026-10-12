@extends('layouts.guest')

@section('content')
    <div class="bg-brand-accent text-white px-3 py-4 text-center border-b border-gray-200 sm:p-6">
        <h2 class="text-xl font-bold mb-1 sm:text-2xl"><i class="fas fa-map-signs mr-2"></i>404</h2>
        <p class="text-blue-100">Strona nie została znaleziona</p>
    </div>

    <div class="p-3 sm:p-6">
        <div class="rounded-lg bg-red-50 border border-red-300 px-4 py-3">
            <h2 class="text-lg font-bold text-red-800">Nie znaleziono strony</h2>
            <p class="mt-1 text-sm text-red-700">
                Strona, którą próbujesz otworzyć, nie istnieje lub została przeniesiona.
            </p>
        </div>
        <a href="{{ url('/') }}" class="mt-4 font-bold inline-block text-sm text-brand-accent hover:underline">
            <i class="fas fa-arrow-left mr-1"></i>Wróć na stronę główną
        </a>
    </div>
@endsection
