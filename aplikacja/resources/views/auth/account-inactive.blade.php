@extends('layouts.guest')

@section('content')
    <div class="p-3 sm:p-6">
        <div class="rounded-lg bg-red-50 border border-red-300 px-4 py-3">
            <h2 class="text-lg font-bold text-red-800">Konto nieaktywne</h2>
            <p class="mt-1 text-sm text-red-700">
                To konto zostało dezaktywowane. Skontaktuj się z administratorem, aby je przywrócić.
            </p>
        </div>
        <a href="{{ route('login') }}" class="mt-4 font-bold inline-block text-sm text-brand-accent hover:underline">
            Wróć do logowania
        </a>
    </div>
@endsection