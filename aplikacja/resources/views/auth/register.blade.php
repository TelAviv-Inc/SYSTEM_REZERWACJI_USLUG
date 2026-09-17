@extends('layouts.guest')

@section('content')
    <div class="bg-brand-accent text-white p-6 text-center border-b border-gray-200">
        <h2 class="text-2xl font-bold mb-1"><i class="fas fa-user-lock mr-2"></i>{{__('System Rezerwacji Usług')}}</h2>
        <p class="text-blue-100">{{ __('Zarejestruj się w systemie') }}</p>
    </div>

    <div class="p-6">
        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name -->
            <div class="mb-5">
                <label for="name" class="block text-gray-700 font-medium mb-2">Imię</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Maria" required autofocus
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                @error('name')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Surname -->
            <div class="mb-5">
                <label for="surname" class="block text-gray-700 font-medium mb-2">Nazwisko</label>
                <input type="text" id="surname" name="surname" value="{{ old('surname') }}" placeholder="Patyk" required
                    autofocus
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                @error('surname')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Email Address -->
            <div class="mb-5">
                <label for="email" class="block text-gray-700 font-medium mb-2">Adres email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="mariapatyk@zawadki.com"
                    required autocomplete="email"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                @error('email')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Phone -->
            <div class="mb-5">
                <label for="phone" class="block text-gray-700 font-medium mb-2">Telefon</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}" placeholder="123456789" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                @error('phone')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-5">
                <label for="password" class="block text-gray-700 font-medium mb-2">Hasło</label>
                <input type="password" id="password" name="password" required autocomplete="new-password"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                @error('password')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="mb-5">
                <label for="password_confirmation" class="block text-gray-700 font-medium mb-2">Potwierdź hasło</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                    autocomplete="new-password"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                @error('password_confirmation')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="flex items-center justify-between mt-4">
                <a class="text-primary hover:text-blue-800 font-medium hover:underline transition duration-200"
                    href="{{ route('login') }}">
                    {{ __('Masz już konto?') }}
                </a>

                <button type="submit"
                    class="ml-4 bg-brand-accent text-white py-3 px-4 rounded-lg font-medium hover:from-primary hover:to-secondary transition duration-300 transform hover:-translate-y-0.5 shadow-md hover:shadow-lg">
                    {{ __('Zarejestruj się') }}
                </button>
            </div>
        </form>
    </div>

@endsection