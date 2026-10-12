@extends('layouts.guest')

@section('content')
    <div class="bg-brand-accent text-white px-3 py-4 text-center border-b border-gray-200 sm:p-6">
        <h2 class="text-xl font-bold mb-1 sm:text-2xl"><i class="fas fa-screwdriver-wrench mr-2"></i>503</h2>
        <p class="text-blue-100">Przerwa techniczna</p>
    </div>

    <div class="p-3 sm:p-6">
        <div class="rounded-lg bg-red-50 border border-red-300 px-4 py-3">
            <h2 class="text-lg font-bold text-red-800">Serwis tymczasowo niedostępny</h2>
            <p class="mt-1 text-sm text-red-700">
                Prowadzimy prace serwisowe. Wróć za chwilę.
            </p>
        </div>
        <a href="{{ url('/') }}" class="mt-4 font-bold inline-block text-sm text-brand-accent hover:underline">
            <i class="fas fa-arrow-left mr-1"></i>Wróć na stronę główną
        </a>
    </div>
@endsection
