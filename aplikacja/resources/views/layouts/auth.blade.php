{{-- Card for the account pages (forgot/reset password, verify email), styled like login and register --}}
@extends('layouts.guest')

@section('content')
    <div class="relative">
        <span class="absolute inset-x-0 top-0 h-1 bg-brand-accent"></span>

        <div class="px-5 pt-8 pb-6 sm:px-8">
            <div class="flex flex-col items-center text-center">
                <div
                    class="flex size-12 items-center justify-center rounded-lg bg-brand-light-bg text-xl text-brand-accent sm:size-14 sm:text-2xl">
                    <i class="fas @yield('icon')"></i>
                </div>
                <h1 class="mt-4 text-xl font-bold text-brand-navy sm:text-2xl">@yield('heading')</h1>
                <p class="mt-1 text-sm font-medium text-brand-muted">@yield('subheading')</p>
            </div>

            @if (session('status'))
                <div
                    class="mt-6 flex items-start gap-2 rounded-lg bg-employee-bg px-3 py-2.5 text-sm font-medium text-employee-text">
                    <i class="fas fa-circle-check mt-0.5"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @yield('form')
        </div>

        @hasSection('footer')
            <div class="border-t border-brand-border bg-brand-bg/60 px-5 py-4 text-center text-sm text-brand-muted sm:px-8">
                @yield('footer')
            </div>
        @endif
    </div>

    <script>
        // Re-enable the button when the page is restored from the back/forward cache
        window.addEventListener('pageshow', (e) => {
            if (e.persisted) document.querySelectorAll('button[type=submit]').forEach((b) => b.disabled = false);
        });
    </script>
@endsection
