<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
->prefix('dashboard')
->name('dashboard.')
->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('index');

    Route::prefix('categories')->name('categories.')->group(function (){
        Route::get('/{category}', [DashboardController::class, 'show'])->name('show');
    });
});



