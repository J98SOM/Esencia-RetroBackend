<?php

use App\Http\Controllers\AlquilerController;
use App\Models\Factura;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\MesaController;
use Illuminate\Http\Request;
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
Route::middleware('auth')->get('/inventarios', [InventarioController::class, 'index'])->name('inventarios.index');

// Alquiler view
Route::middleware('auth')->get('/alquiler', function () {
    // Calculate next invoice number (consecutive) and pass to the view
    $max = DB::table('facturas')->select(DB::raw('MAX(CAST(numero_orden AS UNSIGNED)) as max'))->value('max');
    $next = $max ? intval($max) + 1 : 1;
    $nextStr = str_pad($next, 4, '0', STR_PAD_LEFT);

    return view('alquiler.index', ['nextInvoiceNo' => $nextStr]);
})->name('alquiler');

// Print template endpoint: recibe JSON con los datos de la factura y renderiza la plantilla lista para imprimir
Route::middleware('auth')->post('/alquiler/print', function (Request $request) {
    $invoiceInput = $request->input('invoice');
    $invoice = [];

    if (is_array($invoiceInput)) {
        $invoice = $invoiceInput;
    } elseif (is_string($invoiceInput) && $invoiceInput !== '') {
        $decoded = json_decode($invoiceInput, true);
        if (is_array($decoded)) {
            $invoice = $decoded;
        }
    } elseif ($request->isJson()) {
        $payload = $request->json()->all();
        if (is_array($payload) && ! empty($payload)) {
            $invoice = $payload;
        }
    }

    return view('alquiler.print', ['invoice' => $invoice]);
})->name('alquiler.print');

// Endpoint to persist factura (Alquiler) from the frontend
Route::middleware('auth')->post('/alquiler/store', [AlquilerController::class, 'store'])->name('alquiler.store');
