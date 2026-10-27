@extends('layouts.guest')

@php
    $inputClass =
        'w-full rounded-lg border bg-white py-2.5 pl-10 pr-3 text-sm text-brand-text placeholder:text-brand-muted/60 transition duration-200 focus:outline-none focus:ring-2';
    $inputOk = 'border-brand-border focus:border-brand-accent focus:ring-brand-accent/20';
    $inputError = 'border-red-400 focus:border-red-500 focus:ring-red-500/20';
    $iconClass = 'pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-brand-muted';
@endphp

@section('content')
    <div class="relative">
        <span class="absolute inset-x-0 top-0 h-1 bg-brand-accent"></span>

        <div class="px-5 pt-8 pb-6 sm:px-8">
            <div class="flex flex-col items-center text-center">
                <div
                    class="flex size-12 items-center justify-center rounded-lg bg-brand-light-bg text-xl text-brand-accent sm:size-14 sm:text-2xl">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h1 class="mt-4 text-xl font-bold text-brand-navy sm:text-2xl">{{ __('Załóż konto') }}</h1>
                <p class="mt-1 text-sm font-medium text-brand-muted">{{ __('Zarejestruj się w Systemie Rezerwacji Usług') }}
                </p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4"
                onsubmit="this.querySelector('[type=submit]').disabled = true">
                @csrf

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <!-- Name -->
                    <div>
                        <label for="name" class="mb-1.5 block text-sm font-semibold text-brand-text">Imię</label>
                        <div class="relative">
                            <i class="fas fa-user {{ $iconClass }}"></i>
                            <input type="text" id="name" name="name" value="{{ old('name') }}"
                                placeholder="Maria" required autofocus autocomplete="given-name"
                                class="{{ $inputClass }} {{ $errors->has('name') ? $inputError : $inputOk }}">
                        </div>
                        @error('name')
                            <p class="mt-1.5 flex items-center gap-1.5 text-sm text-red-600">
                                <i class="fas fa-circle-exclamation text-xs"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Surname -->
                    <div>
                        <label for="surname" class="mb-1.5 block text-sm font-semibold text-brand-text">Nazwisko</label>
                        <div class="relative">
                            <i class="fas fa-user {{ $iconClass }}"></i>
                            <input type="text" id="surname" name="surname" value="{{ old('surname') }}"
                                placeholder="Patyk" required autocomplete="family-name"
                                class="{{ $inputClass }} {{ $errors->has('surname') ? $inputError : $inputOk }}">
                        </div>
                        @error('surname')
                            <p class="mt-1.5 flex items-center gap-1.5 text-sm text-red-600">
                                <i class="fas fa-circle-exclamation text-xs"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <!-- Email Address -->
                <div>
                    <label for="email" class="mb-1.5 block text-sm font-semibold text-brand-text">Adres email</label>
                    <div class="relative">
                        <i class="fas fa-envelope {{ $iconClass }}"></i>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            placeholder="mariapatyk@zawadki.com" required autocomplete="email"
                            class="{{ $inputClass }} {{ $errors->has('email') ? $inputError : $inputOk }}">
                    </div>
                    @error('email')
                        <p class="mt-1.5 flex items-center gap-1.5 text-sm text-red-600">
                            <i class="fas fa-circle-exclamation text-xs"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="mb-1.5 block text-sm font-semibold text-brand-text">Telefon</label>
                    <div class="relative">
                        <i class="fas fa-phone {{ $iconClass }}"></i>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                            placeholder="123456789" required autocomplete="tel"
                            class="{{ $inputClass }} {{ $errors->has('phone') ? $inputError : $inputOk }}">
                    </div>
                    @error('phone')
                        <p class="mt-1.5 flex items-center gap-1.5 text-sm text-red-600">
                            <i class="fas fa-circle-exclamation text-xs"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="mb-1.5 block text-sm font-semibold text-brand-text">Hasło</label>
                    <div class="relative">
                        <i class="fas fa-lock {{ $iconClass }}"></i>
                        <input type="password" id="password" name="password" required autocomplete="new-password"
                            placeholder="••••••••"
                            class="{{ $inputClass }} {{ $errors->has('password') ? $inputError : $inputOk }}">
                    </div>
                    @error('password')
                        <p class="mt-1.5 flex items-center gap-1.5 text-sm text-red-600">
                            <i class="fas fa-circle-exclamation text-xs"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="mb-1.5 block text-sm font-semibold text-brand-text">Potwierdź
                        hasło</label>
                    <div class="relative">
                        <i class="fas fa-lock {{ $iconClass }}"></i>
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                            autocomplete="new-password" placeholder="••••••••"
                            class="{{ $inputClass }} {{ $errors->has('password_confirmation') ? $inputError : $inputOk }}">
                    </div>
                    @error('password_confirmation')
                        <p class="mt-1.5 flex items-center gap-1.5 text-sm text-red-600">
                            <i class="fas fa-circle-exclamation text-xs"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <button type="submit"
                    class="group mt-2 flex w-full items-center justify-center gap-2 rounded-lg bg-brand-accent px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition duration-300 hover:-translate-y-0.5 hover:bg-brand-blue hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent/50 focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-75 disabled:hover:translate-y-0 disabled:hover:bg-brand-accent disabled:hover:shadow-sm">
                    <span class="group-disabled:hidden">{{ __('Zarejestruj się') }}</span>
                    <i
                        class="fas fa-arrow-right text-xs transition-transform duration-300 group-hover:translate-x-1 group-disabled:hidden"></i>
                    <span class="hidden group-disabled:inline"><i
                            class="fas fa-spinner fa-spin mr-2"></i>{{ __('Rejestracja...') }}</span>
                </button>
            </form>
        </div>

        <div class="border-t border-brand-border bg-brand-bg/60 px-5 py-4 text-center text-sm text-brand-muted sm:px-8">
            {{ __('Masz już konto?') }}
            <a href="{{ route('login') }}"
                class="font-semibold text-brand-accent transition duration-200 hover:text-brand-blue hover:underline">{{ __('Zaloguj się') }}</a>
        </div>
    </div>

    <script>
        // Re-enable the button when the page is restored from the back/forward cache
        window.addEventListener('pageshow', (e) => {
            if (e.persisted) document.querySelectorAll('button[type=submit]').forEach((b) => b.disabled = false);
        });
    </script>
@endsection
