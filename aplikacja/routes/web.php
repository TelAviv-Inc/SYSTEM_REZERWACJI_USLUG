<?php

use App\Http\Controllers\PreviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TestController;
use App\Models\ServiceCategory;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/dashboard', function () {
    $categories = ServiceCategory::all(['name', 'description', 'icon']);
    return view('dashboard.dashboard' , ['categories' => $categories]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/preview-login', [PreviewController::class, 'loginPage'])->name('preview.login');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
