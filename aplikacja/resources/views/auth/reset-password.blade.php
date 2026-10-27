@extends('layouts.auth')

@section('title', 'Nowe hasło — System Rezerwacji Usług')
@section('icon', 'fa-lock')
@section('heading', 'Ustaw nowe hasło')
@section('subheading', 'Wpisz swój adres e-mail i nowe hasło do konta.')

@section('form')
    <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4"
        onsubmit="this.querySelector('[type=submit]').disabled = true">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-auth-input name="email" type="email" label="Adres email" icon="fa-envelope" :value="old('email', $request->email)" required autofocus
            autocomplete="username" placeholder="jan@przyklad.pl" />

        <x-auth-input name="password" type="password" label="Nowe hasło" icon="fa-lock" required autocomplete="new-password"
            placeholder="••••••••" />

        <x-auth-input name="password_confirmation" type="password" label="Potwierdź nowe hasło" icon="fa-lock" required
            autocomplete="new-password" placeholder="••••••••" />

        <x-auth-submit loading="Zapisywanie...">Zmień hasło</x-auth-submit>
    </form>
@endsection

@section('footer')
    <a href="{{ route('login') }}"
        class="font-semibold text-brand-accent transition duration-200 hover:text-brand-blue hover:underline">Wróć do
        logowania</a>
@endsection
