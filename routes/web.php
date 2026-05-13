<?php

use App\Http\Controllers\AlquilerController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\CajaController;
use App\Models\Factura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

// Alquiler view (create / edit)
Route::middleware('auth')->get('/alquiler', function (Request $request) {
    // Calculate next invoice number (consecutive) for 'evento' type
    $max = DB::table('facturas')->where('tipo', 'evento')->select(DB::raw('MAX(CAST(numero_orden AS UNSIGNED)) as max'))->value('max');
    $next = $max ? intval($max) + 1 : 1;
    $nextStr = str_pad($next, 4, '0', STR_PAD_LEFT);

    $factura = null;
    $items = [];
    $metodos = [];

    if ($request->query('factura_id')) {
        $factura = Factura::with(['productos', 'metodosPago'])->find($request->query('factura_id'));
        if ($factura) {
            // Normalize items to the frontend shape
            foreach ($factura->productos as $it) {
                $items[] = [
                    'desc' => $it->descripcion ?? ($it->producto?->nombre ?? ''),
                    'cant' => $it->cantidad,
                    'precio' => $it->precio_unitario,
                    'producto_id' => $it->producto_id ?? null,
                ];
            }

            foreach ($factura->metodosPago as $m) {
                $metodos[] = ['metodo' => $m->metodo, 'valor' => $m->valor];
            }
        }
    }

    // Load products for description dropdown/autocomplete
    $products = App\Models\Producto::select('id','nombre','precio')->get();

    return view('alquiler.index', [
        'nextInvoiceNo' => $nextStr,
        'factura' => $factura,
        'items' => $items,
        'metodos' => $metodos,
        'products' => $products,
    ]);
})->name('alquiler');

// Caja view and store
Route::middleware('auth')->get('/alquiler/caja', [CajaController::class, 'index'])->name('alquiler.caja');

Route::middleware('auth')->post('/alquiler/caja', [CajaController::class, 'store'])->name('alquiler.caja.store');

// Alquiler - listado (tabla) para CRUD general (mostrar todos los tipos)
Route::middleware('auth')->get('/alquiler/list', function () {
    $facturas = Factura::orderBy('fecha', 'desc')->paginate(20);

    return view('alquiler.list', compact('facturas'));
})->name('alquiler.list');

// Alquiler edit view (dedicated route) -> reuse index view but with factura prefilled
Route::middleware('auth')->get('/alquiler/{id}/edit', function ($id) {
    // next number for editing alquiler (evento)
    $max = DB::table('facturas')->where('tipo', 'evento')->select(DB::raw('MAX(CAST(numero_orden AS UNSIGNED)) as max'))->value('max');
    $next = $max ? intval($max) + 1 : 1;
    $nextStr = str_pad($next, 4, '0', STR_PAD_LEFT);

    $factura = Factura::with(['productos', 'metodosPago'])->find($id);
    $items = [];
    $metodos = [];
    if ($factura) {
        foreach ($factura->productos as $it) {
            $items[] = [
                'desc' => $it->descripcion ?? ($it->producto?->nombre ?? ''),
                'cant' => $it->cantidad,
                'precio' => $it->precio_unitario,
                'producto_id' => $it->producto_id ?? null,
            ];
        }
        foreach ($factura->metodosPago as $m) {
            $metodos[] = ['metodo' => $m->metodo, 'valor' => $m->valor];
        }
    }

    // Load products for the description dropdown/autocomplete (same as create route)
    $products = App\Models\Producto::select('id','nombre','precio')->get();

    return view('alquiler.index', [
        'nextInvoiceNo' => $nextStr,
        'factura' => $factura,
        'items' => $items,
        'metodos' => $metodos,
        'products' => $products,
    ]);
})->name('alquiler.edit');

// Eliminar factura (alquiler) — acción simple para la vista de tabla
Route::middleware('auth')->delete('/alquiler/{id}', function ($id) {
    // Intentamos eliminar la factura; usar con precaución en producción
    Factura::destroy($id);

    return redirect()->route('alquiler.list')->with('status', 'Factura eliminada');
})->name('alquiler.delete');

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

// Print view for a factura id (openable in new tab for printing / save-as-pdf)
Route::middleware('auth')->get('/alquiler/{id}/print', function ($id) {
    $factura = Factura::with(['productos', 'metodosPago'])->find($id);
    $invoice = ['company' => [
        'name' => 'ESENCIA RETRO',
        'nit' => '1,007,450,540',
        'tel' => '3162218491 - 3209180085',
        'city' => 'Bogotá',
        'email' => 'esenciaretro10@gmail.com',
        'address' => ''
    ], 'cliente' => [], 'items' => []];
    if ($factura) {
        $invoice = [
            'no' => $factura->numero_orden,
            'fecha' => $factura->fecha,
            'orden_compra' => $factura->orden_compra,
            'medio_pago' => optional($factura->metodosPago->first())->metodo ?? null,
            'cliente' => [
                'nombre' => $factura->persona,
                'nit' => $factura->nit,
                'direccion' => $factura->direccion,
                'telefono' => $factura->telefono,
                'ciudad' => $factura->ciudad,
            ],
            'items' => [],
            'observaciones' => $factura->observaciones,
            'total' => $factura->monto_total,
        ];

        foreach ($factura->productos as $it) {
            $invoice['items'][] = [
                'desc' => $it->descripcion ?? ($it->producto?->nombre ?? ''),
                'cant' => $it->cantidad,
                'precio' => $it->precio_unitario,
            ];
        }
    }

    // Choose template by factura type
    $tipo = $factura->tipo ?? 'evento';
    $tipo = strtolower($tipo);
    if (in_array($tipo, ['pos', 'venta', 'caja'])) {
        if (view()->exists('caja.print')) {
            return view('caja.print', ['invoice' => $invoice]);
        }
    }

    return view('alquiler.print', ['invoice' => $invoice]);
})->name('alquiler.printview');

    // JSON endpoint for invoice data (used by JS printers)
    Route::middleware('auth')->get('/alquiler/{id}/json', function ($id) {
        $factura = App\Models\Factura::with(['productos', 'metodosPago'])->findOrFail($id);
        $data = [
            'id' => $factura->id,
            'numero' => $factura->numero_orden ?? null,
            'tipo' => $factura->tipo ?? null,
            'company' => [
                'name' => 'ESENCIA RETRO',
                'nit' => '1,007,450,540',
                'tel' => '3162218491 - 3209180085',
                'city' => 'Bogotá',
                'email' => 'esenciaretro10@gmail.com',
                'logo' => asset('img/logo.png'),
            ],
            'cliente' => [
                'nombre' => $factura->persona,
                'nit' => $factura->nit,
                'telefono' => $factura->telefono,
                'direccion' => $factura->direccion,
                'ciudad' => $factura->ciudad,
            ],
            'fecha' => $factura->fecha,
            'items' => collect($factura->productos)->map(function ($it) {
                return [
                    'desc' => $it->descripcion ?? ($it->producto?->nombre ?? ''),
                    'cant' => $it->cantidad,
                    'precio' => $it->precio_unitario,
                    'subtotal' => ($it->cantidad * $it->precio_unitario),
                ];
            })->values(),
            'pagos' => collect($factura->metodosPago)->map(function ($m) {
                return ['metodo' => $m->metodo, 'monto' => $m->valor ?? $m->monto ?? 0];
            })->values(),
            'totales' => ['subtotal' => $factura->subtotal ?? 0, 'total' => $factura->monto_total ?? 0],
        ];

        return response()->json($data);
    });

// Server-side generated PDF download
Route::middleware('auth')->get('/alquiler/{id}/pdf', [AlquilerController::class, 'pdf'])->name('alquiler.pdf');

// Endpoint to persist factura (Alquiler) from the frontend
Route::middleware('auth')->post('/alquiler/store', [AlquilerController::class, 'store'])->name('alquiler.store');

// Endpoint to update an existing factura
Route::middleware('auth')->post('/alquiler/update', [AlquilerController::class, 'update'])->name('alquiler.update');
