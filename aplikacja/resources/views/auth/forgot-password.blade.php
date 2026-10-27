@extends('layouts.auth')

@section('title', 'Przypomnienie hasła — System Rezerwacji Usług')
@section('icon', 'fa-key')
@section('heading', 'Nie pamiętasz hasła?')
@section('subheading', 'Podaj adres e-mail, a wyślemy Ci link do ustawienia nowego hasła.')

@section('form')
    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4"
        onsubmit="this.querySelector('[type=submit]').disabled = true">
        @csrf

        <x-auth-input name="email" type="email" label="Adres email" icon="fa-envelope" :value="old('email')" required autofocus
            autocomplete="email" placeholder="jan@przyklad.pl" />

        <x-auth-submit loading="Wysyłanie...">Wyślij link</x-auth-submit>
    </form>
@endsection

@section('footer')
    Pamiętasz hasło?
    <a href="{{ route('login') }}"
        class="font-semibold text-brand-accent transition duration-200 hover:text-brand-blue hover:underline">Zaloguj
        się</a>
@endsection
