<?php

namespace App\Providers;

use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Password::defaults(fn () => app()->isProduction() ? Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised() : Password::min(8));

        Livewire::addPersistentMiddleware([
            EnsureUserIsActive::class
        ]);

        // Livewire update requests get their own, looser bucket so interactive
        // components don't eat into the page limit. Matched by route name, not by
        // the X-Livewire header, so it can't be spoofed on regular pages.
        RateLimiter::for('web', function (Request $request) {
            $key = $request->user()?->getAuthIdentifier() ?? $request->ip();

            if ($request->routeIs('*livewire.update')) {
                return Limit::perMinute(300)->by('livewire|'.$key);
            }

            return Limit::perMinute(120)->by('web|'.$key);
        });

        // Stricter limit for auth form submissions (register, login, password
        // reset/confirm/change). Keyed by path so each form has its own counter.
        RateLimiter::for('auth', function (Request $request) {
            $key = $request->user()?->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(10)->by('auth|'.$request->path().'|'.$key);
        });
    }
}
