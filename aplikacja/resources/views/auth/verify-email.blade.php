@extends('layouts.auth')

@section('title', 'Potwierdzenie adresu e-mail — System Rezerwacji Usług')
@section('icon', 'fa-envelope-open-text')
@section('heading', 'Potwierdź adres e-mail')
@section('subheading', 'Kliknij link, który wysłaliśmy na Twój adres e-mail. Jeśli wiadomość nie dotarła, wyślemy ją
    ponownie.')

@section('form')
    <form method="POST" action="{{ route('verification.send') }}" class="mt-6"
        onsubmit="this.querySelector('[type=submit]').disabled = true">
        @csrf

        <x-auth-submit loading="Wysyłanie...">Wyślij link ponownie</x-auth-submit>
    </form>
@endsection

@section('footer')
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"
            class="cursor-pointer font-semibold text-brand-accent transition duration-200 hover:text-brand-blue hover:underline">Wyloguj
            się</button>
    </form>
@endsection
