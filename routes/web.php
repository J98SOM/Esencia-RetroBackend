<?php

use App\Http\Controllers\MesaController;
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

// Mesas CRUD (Web) - Protected by auth
Route::middleware('auth')->resource('mesas', MesaController::class);
// Productos web view
Route::middleware('auth')->get('/productos', function () {
    return view('productos.index');
})->name('productos.index');

// Inventarios web view
Route::middleware('auth')->get('/inventarios', [\App\Http\Controllers\InventarioController::class, 'index'])->name('inventarios.index');
