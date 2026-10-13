@extends('layouts.guest')

@section('content')
    <div class="bg-brand-accent text-white px-3 py-4 text-center border-b border-gray-200 sm:p-6">
        <h2 class="text-xl font-bold mb-1 sm:text-2xl"><i
                class="fas fa-user-lock mr-2"></i>{{__('System Rezerwacji Usług')}}</h2>
        <p class="text-blue-100">{{ __('Zarejestruj się w systemie') }}</p>
    </div>

    <div class="p-3 sm:p-6">
        <form method="POST" action="{{ route('register') }}" onsubmit="this.querySelector('[type=submit]').disabled = true">
            @csrf

            <!-- Name -->
            <div class="mb-3 sm:mb-5">
                <label for="name" class="block text-gray-700 font-medium mb-2">Imię</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Maria" required autofocus
                    class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200 sm:px-4">
                @error('name')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Surname -->
            <div class="mb-3 sm:mb-5">
                <label for="surname" class="block text-gray-700 font-medium mb-2">Nazwisko</label>
                <input type="text" id="surname" name="surname" value="{{ old('surname') }}" placeholder="Patyk" required
                    autofocus
                    class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200 sm:px-4">
                @error('surname')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Email Address -->
            <div class="mb-3 sm:mb-5">
                <label for="email" class="block text-gray-700 font-medium mb-2">Adres email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="mariapatyk@zawadki.com"
                    required autocomplete="email"
                    class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200 sm:px-4">
                @error('email')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Phone -->
            <div class="mb-3 sm:mb-5">
                <label for="phone" class="block text-gray-700 font-medium mb-2">Telefon</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}" placeholder="123456789" required
                    class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200 sm:px-4">
                @error('phone')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-3 sm:mb-5">
                <label for="password" class="block text-gray-700 font-medium mb-2">Hasło</label>
                <input type="password" id="password" name="password" required autocomplete="new-password"
                    class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200 sm:px-4">
                @error('password')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="mb-3 sm:mb-5">
                <label for="password_confirmation" class="block text-gray-700 font-medium mb-2">Potwierdź hasło</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                    autocomplete="new-password"
                    class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200 sm:px-4">
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
                    class="group ml-4 bg-brand-accent text-white px-2 py-3 rounded-lg font-medium hover:from-primary hover:to-secondary transition duration-300 transform hover:-translate-y-0.5 shadow-md hover:shadow-lg disabled:opacity-75 disabled:cursor-wait disabled:hover:translate-y-0 disabled:hover:shadow-md sm:py-3 sm:px-4">
                    <span class="group-disabled:hidden">{{ __('Zarejestruj się') }}</span>
                    <span class="hidden group-disabled:inline"><i class="fas fa-spinner fa-spin mr-2"></i>{{ __('Rejestracja...') }}</span>
                </button>
            </div>
        </form>
    </div>

    <script>
        // Re-enable the button when the page is restored from the back/forward cache
        window.addEventListener('pageshow', (e) => {
            if (e.persisted) document.querySelectorAll('button[type=submit]').forEach((b) => b.disabled = false);
        });
    </script>

@endsection