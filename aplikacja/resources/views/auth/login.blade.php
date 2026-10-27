<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie do Systemu Rezerwacji Usług</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

@php
    $inputClass =
        'w-full rounded-lg border bg-white py-2.5 pl-10 pr-3 text-sm text-brand-text placeholder:text-brand-muted/60 transition duration-200 focus:outline-none focus:ring-2';
    $inputOk = 'border-brand-border focus:border-brand-accent focus:ring-brand-accent/20';
    $inputError = 'border-red-400 focus:border-red-500 focus:ring-red-500/20';
@endphp

<body class="bg-brand-bg min-h-screen flex items-center justify-center p-4">
    <div class="relative w-full max-w-md overflow-hidden rounded-xl border border-brand-border bg-white shadow-sm">
        <span class="absolute inset-x-0 top-0 h-1 bg-brand-accent"></span>

        <div class="px-5 pt-8 pb-6 sm:px-8">
            <div class="flex flex-col items-center text-center">
                <div
                    class="flex size-12 items-center justify-center rounded-lg bg-brand-light-bg text-xl text-brand-accent sm:size-14 sm:text-2xl">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h1 class="mt-4 text-xl font-bold text-brand-navy sm:text-2xl">Witaj ponownie</h1>
                <p class="mt-1 text-sm font-medium text-brand-muted">Zaloguj się do Systemu Rezerwacji Usług</p>
            </div>

            @if (session('status'))
                <div
                    class="mt-6 flex items-start gap-2 rounded-lg bg-employee-bg px-3 py-2.5 text-sm font-medium text-employee-text">
                    <i class="fas fa-circle-check mt-0.5"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4"
                onsubmit="this.querySelector('[type=submit]').disabled = true">
                @csrf

                <div>
                    <label for="email" class="mb-1.5 block text-sm font-semibold text-brand-text">Adres email</label>
                    <div class="relative">
                        <i
                            class="fas fa-envelope pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-brand-muted"></i>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                            autocomplete="email" autofocus placeholder="jan@przyklad.pl"
                            class="{{ $inputClass }} {{ $errors->has('email') ? $inputError : $inputOk }}">
                    </div>
                    @error('email')
                        <p class="mt-1.5 flex items-center gap-1.5 text-sm text-red-600">
                            <i class="fas fa-circle-exclamation text-xs"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-sm font-semibold text-brand-text">Hasło</label>
                    <div class="relative">
                        <i
                            class="fas fa-lock pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-brand-muted"></i>
                        <input type="password" id="password" name="password" required autocomplete="current-password"
                            placeholder="••••••••"
                            class="{{ $inputClass }} {{ $errors->has('password') ? $inputError : $inputOk }}">
                    </div>
                    @error('password')
                        <p class="mt-1.5 flex items-center gap-1.5 text-sm text-red-600">
                            <i class="fas fa-circle-exclamation text-xs"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="flex items-center justify-between gap-3 pt-1">
                    <label for="remember" class="flex cursor-pointer items-center gap-2 text-sm text-brand-text">
                        <input type="checkbox" id="remember" name="remember"
                            class="size-4 rounded border-brand-border accent-brand-accent">
                        Zapamiętaj mnie
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                            class="text-sm font-semibold text-brand-accent transition duration-200 hover:text-brand-blue hover:underline">
                            Zapomniałeś hasła?
                        </a>
                    @endif
                </div>

                <button type="submit"
                    class="group mt-2 flex w-full items-center justify-center gap-2 rounded-lg bg-brand-accent px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition duration-300 hover:-translate-y-0.5 hover:bg-brand-blue hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent/50 focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-75 disabled:hover:translate-y-0 disabled:hover:bg-brand-accent disabled:hover:shadow-sm">
                    <span class="group-disabled:hidden">Zaloguj się</span>
                    <i
                        class="fas fa-arrow-right text-xs transition-transform duration-300 group-hover:translate-x-1 group-disabled:hidden"></i>
                    <span class="hidden group-disabled:inline"><i
                            class="fas fa-spinner fa-spin mr-2"></i>Logowanie...</span>
                </button>
            </form>
        </div>

        <div class="border-t border-brand-border bg-brand-bg/60 px-5 py-4 text-center text-sm text-brand-muted sm:px-8">
            Nie masz konta?
            <a href="{{ route('register') }}"
                class="font-semibold text-brand-accent transition duration-200 hover:text-brand-blue hover:underline">Zarejestruj
                się</a>
        </div>
    </div>

    <script>
        // Re-enable the button when the page is restored from the back/forward cache
        window.addEventListener('pageshow', (e) => {
            if (e.persisted) document.querySelectorAll('button[type=submit]').forEach((b) => b.disabled = false);
        });
    </script>
</body>

</html>
