<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

// Login route
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Dashboard route
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Users Management route
Route::get('/usuarios', function () {
    return view('usuarios.index');
})->name('usuarios.index');
