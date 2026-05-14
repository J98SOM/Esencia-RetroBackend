<?php

use App\Http\Controllers\AlquilerController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InventarioController;
use App\Http\Controllers\Api\MesaController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\CajaController;
use App\Models\Factura;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // User CRUD routes
    Route::get('/users', [AuthController::class, 'index']);
    Route::post('/users', [AuthController::class, 'store']);
    Route::get('/users/{user}', [AuthController::class, 'show']);
    Route::put('/users/{user}', [AuthController::class, 'update']);
    Route::delete('/users/{user}', [AuthController::class, 'destroy']);

    // Role CRUD routes
    Route::get('/roles', [RoleController::class, 'index']);
    Route::get('/roles/{role}', [RoleController::class, 'show']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::put('/roles/{role}', [RoleController::class, 'update']);
    Route::delete('/roles/{role}', [RoleController::class, 'destroy']);

    // Mesa CRUD API
    Route::apiResource('/mesas', MesaController::class)->names('api.mesas');
    // Productos CRUD API
    Route::apiResource('/productos', ProductoController::class)->names('api.productos');
    // Inventarios CRUD API
    Route::apiResource('/inventarios', InventarioController::class)->names('api.inventarios');

    // Alquiler API endpoints (JSON) for frontend
    Route::get('/alquiler', function () {
        $facturas = Factura::where('tipo', 'evento')->with(['productos', 'metodosPago'])->orderBy('fecha', 'desc')->paginate(20);

        return response()->json($facturas);
    })->name('api.alquiler.index');

    Route::get('/alquiler/{id}', function ($id) {
        $factura = Factura::with(['productos', 'metodosPago'])->find($id);
        if (! $factura) {
            return response()->json(['message' => 'Factura no encontrada'], 404);
        }

        return response()->json($factura);
    })->name('api.alquiler.show');

    // Use existing controller methods (they already accept JSON payloads and return JSON)
    Route::post('/alquiler', [AlquilerController::class, 'store'])->name('api.alquiler.store');
    Route::put('/alquiler/{id}', [AlquilerController::class, 'update'])->name('api.alquiler.update');
    Route::delete('/alquiler/{id}', function ($id) {
        $deleted = Factura::destroy($id);

        return response()->json(['deleted' => (bool) $deleted]);
    })->name('api.alquiler.delete');

    // Caja endpoints (POS) - separate behavior from alquiler
    Route::get('/caja', function () {
        $facturas = Factura::where('tipo', 'pos')->with(['productos', 'metodosPago'])->orderBy('fecha', 'desc')->paginate(20);

        return response()->json($facturas);
    })->name('api.caja.index');

    Route::get('/caja/{id}', function ($id) {
        $factura = Factura::with(['productos', 'metodosPago'])->find($id);
        if (! $factura) {
            return response()->json(['message' => 'Factura no encontrada'], 404);
        }

        return response()->json($factura);
    })->name('api.caja.show');

    Route::post('/caja', [CajaController::class, 'store'])->name('api.caja.store');
    Route::put('/caja/{id}', [CajaController::class, 'update'])->name('api.caja.update');
    Route::delete('/caja/{id}', function ($id) {
        $deleted = Factura::destroy($id);

        return response()->json(['deleted' => (bool) $deleted]);
    })->name('api.caja.delete');

    // PDF download via controller (returns binary/pdf) - keep behind auth
    Route::get('/alquiler/{id}/pdf', [AlquilerController::class, 'pdf'])->name('api.alquiler.pdf');
});
