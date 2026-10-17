<?php


use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->middleware('guest')->name('home');

require __DIR__.'/auth.php';
require __DIR__.'/dashboard.php';
