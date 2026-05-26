<?php

use App\Http\Controllers\AlquilerController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InventarioController;
use App\Http\Controllers\Api\MesaController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\CajaController;
use App\Models\Factura;
use App\Models\RealtimeEvent;
use App\Support\SanctumTokenResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Server-Sent Events stream (public route) — authenticates using query token via SanctumTokenResolver
Route::get('/realtime/stream', function (Request $request) {
    $user = SanctumTokenResolver::resolveUser($request->query('token'));
    if (! $user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $afterId = (int) $request->integer('after_id', 0);
    $mesaId = $request->integer('mesa_id') ?: null;
    $eventKey = $request->string('event_key')->trim()->toString();

    return response()->stream(function () use ($afterId, $mesaId, $eventKey): void {
        ignore_user_abort(true);
        set_time_limit(0);

        echo "retry: 3000\n\n";
        @ob_flush();
        @flush();

        $lastId = $afterId;

        while (! connection_aborted()) {
            $events = RealtimeEvent::query()
                ->when($lastId > 0, fn ($query) => $query->where('id', '>', $lastId))
                ->when($mesaId, fn ($query) => $query->where('mesa_id', $mesaId))
                ->when($eventKey !== '', fn ($query) => $query->where('event_key', $eventKey))
                ->orderBy('id')
                ->limit(50)
                ->get();

            foreach ($events as $event) {
                $lastId = $event->id;
                echo 'id: '.$event->id."\n";
                echo 'event: '.$event->event_key."\n";
                echo 'data: '.json_encode($event->toRealtimeArray(), JSON_UNESCAPED_UNICODE)."\n\n";
            }

            if ($events->isEmpty()) {
                echo ": ping\n\n";
            }

            @ob_flush();
            @flush();
            sleep(2);
        }
    }, 200, [
        'Content-Type' => 'text/event-stream',
        'Cache-Control' => 'no-cache, no-transform',
        'Connection' => 'keep-alive',
        'X-Accel-Buffering' => 'no',
    ]);
})->name('api.realtime.stream');

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

    Route::get('/realtime/events', function (Request $request) {
        $afterId = $request->integer('after_id') ?: 0;
        $mesaId = $request->integer('mesa_id') ?: null;
        $eventKey = $request->string('event_key')->trim()->toString();

        $events = RealtimeEvent::query()
            ->when($afterId > 0, fn ($query) => $query->where('id', '>', $afterId))
            ->when($mesaId, fn ($query) => $query->where('mesa_id', $mesaId))
            ->when($eventKey !== '', fn ($query) => $query->where('event_key', $eventKey))
            ->orderBy('id')
            ->limit(200)
            ->get();

        return response()->json([
            'data' => $events->map(fn (RealtimeEvent $event) => $event->toRealtimeArray())->values(),
            'last_id' => $events->last()?->id,
        ]);
    })->name('api.realtime.events');

    // NOTE: realtime/stream is defined outside the auth group to allow token-in-query authentication

    // PDF download via controller (returns binary/pdf) - keep behind auth
    Route::get('/alquiler/{id}/pdf', [AlquilerController::class, 'pdf'])->name('api.alquiler.pdf');
});
