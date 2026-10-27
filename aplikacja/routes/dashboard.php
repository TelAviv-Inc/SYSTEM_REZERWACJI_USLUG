<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'active'])
->prefix('dashboard')
->name('dashboard.')
->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
    Route::get('/reservations', [DashboardController::class, 'reservations'])->name('reservations');
    Route::get('/admin', [DashboardController::class, 'admin'])->middleware('role:admin')->name('admin');
    Route::get('/admin/reports', [DashboardController::class, 'reports'])->middleware('role:admin')->name('admin.reports');
    Route::get('/employee', [DashboardController::class, 'employee'])->middleware('role:employee,admin')->name('employee');


});



