<?php

namespace App\Providers;

use App\Http\Middleware\EnsureUserIsActive;
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
    }
}
