<?php

use App\Http\Controllers\AlquilerController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\MesaController;
use App\Models\Factura;
use App\Models\Mesa;
use App\Models\Producto;
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

// Mesas view as cards (specific routes first to avoid resource wildcard capture)
Route::middleware('auth')->get('/mesas/cards', [MesaController::class, 'cards'])->name('mesas.cards');
// Public (no auth) variant to allow quick testing without login
Route::get('/mesas/cards/public', [MesaController::class, 'cards'])->name('mesas.cards.public');

// Add a factura to a mesa (create invoice and associate products) — used by modal
Route::middleware('auth')->post('/mesas/{mesa}/add-factura', function (Illuminate\Http\Request $request, App\Models\Mesa $mesa) {
    $data = $request->validate([
        'products' => 'required|array|min:1',
        'products.*.id' => 'required|integer|exists:productos,id',
        'products.*.qty' => 'required|integer|min:1',
    ]);

    $productIds = collect($data['products'])->pluck('id')->all();
    $products = App\Models\Producto::whereIn('id', $productIds)->get()->keyBy('id');

    $total = 0;
    foreach ($data['products'] as $p) {
        $prod = $products->get($p['id']);
        if ($prod) {
            $total += (float) $prod->precio * (int) $p['qty'];
        }
    }

    $factura = null;
    $factura = Illuminate\Support\Facades\DB::transaction(function () use ($mesa, $data, $products, $total) {
        $factura = App\Models\Factura::create([
        'tipo' => 'mesa',
        'numero_orden' => null,
        'fecha' => now(),
        'persona' => null,
        'nit' => null,
        'direccion' => null,
        'telefono' => null,
        'ciudad' => null,
        'orden_compra' => null,
        'observaciones' => 'Factura generada desde tarjeta de mesa',
        'mesa_id' => $mesa->id,
        'estatus' => 'pendiente',
        'monto_total' => $total,
        'cambio' => 0,
    ]);

        foreach ($data['products'] as $p) {
            $prod = $products->get($p['id']);
            if ($prod) {
                App\Models\ProductoXFactura::create([
                    'producto_id' => $prod->id,
                    'factura_id' => $factura->id,
                    'cantidad' => (int) $p['qty'],
                    'precio_unitario' => $prod->precio,
                    'descripcion' => $prod->nombre,
                ]);
            }
        }

        return $factura;
    });

    return response()->json(['success' => true, 'factura_id' => $factura->id, 'estatus' => $factura->estatus]);
})->name('mesas.add_factura');

// Add products to the existing open factura for a mesa (does not create a new factura)
// NOTE: made public to allow adding from the cards view when auth is not required for testing
Route::post('/mesas/{mesa}/add-to-factura', function (Illuminate\Http\Request $request, App\Models\Mesa $mesa) {
    $payload = $request->input('products');

    if (! is_array($payload) || count($payload) === 0) {
        return response()->json(['success' => false, 'message' => 'No se recibieron productos.'], 400);
    }

    // Normalize entries and validate basic shape
    $normalized = [];
    foreach ($payload as $i => $it) {
        $id = isset($it['id']) ? (int) $it['id'] : null;
        $qty = isset($it['qty']) ? (int) $it['qty'] : null;
        if (! $id || $id <= 0 || ! $qty || $qty <= 0) {
            return response()->json(['success' => false, 'message' => "Producto inválido en posición {$i}."], 400);
        }
        $normalized[] = ['id' => $id, 'qty' => $qty];
    }

    // ensure there is an open (not paid) factura for this mesa; if none, create one
    $factura = $mesa->latestFactura ?? null;
    $shouldCreate = false;
    if (! $factura || in_array(strtolower($factura->estatus), ['pagado', 'pagada'])) {
        $shouldCreate = true;
    }

    $productIds = collect($normalized)->pluck('id')->all();
    $products = App\Models\Producto::whereIn('id', $productIds)->get()->keyBy('id');

    // check all products exist
    $missing = [];
    foreach ($productIds as $pid) {
        if (! $products->has($pid)) $missing[] = $pid;
    }
    if (! empty($missing)) {
        return response()->json(['success' => false, 'message' => 'Algunos productos no existen: ' . implode(',', $missing)], 400);
    }

    $addedTotal = 0;

    $factura = Illuminate\Support\Facades\DB::transaction(function () use ($factura, $normalized, $products, &$addedTotal, $mesa, $shouldCreate) {
        // create factura if needed
        if ($shouldCreate) {
            $totalForCreation = 0;
            foreach ($normalized as $p) {
                $prod = $products->get($p['id']);
                if ($prod) $totalForCreation += (float) $prod->precio * (int) $p['qty'];
            }

            $factura = App\Models\Factura::create([
                'tipo' => 'mesa',
                'numero_orden' => null,
                'fecha' => now(),
                'persona' => null,
                'nit' => null,
                'direccion' => null,
                'telefono' => null,
                'ciudad' => null,
                'orden_compra' => null,
                'observaciones' => 'Factura generada desde tarjeta de mesa',
                'mesa_id' => $mesa->id,
                'estatus' => 'pendiente',
                'monto_total' => $totalForCreation,
                'cambio' => 0,
            ]);

            // create initial product lines
            foreach ($normalized as $p) {
                $prod = $products->get($p['id']);
                if (! $prod) continue;
                App\Models\ProductoXFactura::create([
                    'producto_id' => $prod->id,
                    'factura_id' => $factura->id,
                    'cantidad' => (int) $p['qty'],
                    'precio_unitario' => $prod->precio,
                    'descripcion' => $prod->nombre,
                ]);
                $addedTotal += (float) $prod->precio * (int) $p['qty'];
            }
        } else {
            // append to existing factura
            foreach ($normalized as $p) {
                $prod = $products->get($p['id']);
                if (! $prod) continue;

                $qty = (int) $p['qty'];
                $pxf = App\Models\ProductoXFactura::where('factura_id', $factura->id)->where('producto_id', $prod->id)->first();
                if ($pxf) {
                    $pxf->cantidad = ((int) $pxf->cantidad) + $qty;
                    $pxf->precio_unitario = $prod->precio;
                    $pxf->save();
                } else {
                    App\Models\ProductoXFactura::create([
                        'producto_id' => $prod->id,
                        'factura_id' => $factura->id,
                        'cantidad' => $qty,
                        'precio_unitario' => $prod->precio,
                        'descripcion' => $prod->nombre,
                    ]);
                }

                $addedTotal += (float) $prod->precio * $qty;
            }

            // update factura total
            $factura->monto_total = ((float) $factura->monto_total) + $addedTotal;
            $factura->save();
        }

        return $factura;
    });

    return response()->json(['success' => true, 'factura_id' => $factura->id, 'estatus' => $factura->estatus, 'added_total' => $addedTotal]);
})->name('mesas.add_to_factura');

// Mesas CRUD (Web) - Protected by auth (resource registered after specific routes)
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
    $products = Producto::select('id', 'nombre', 'precio')->get();

    return view('alquiler.index', [
        'nextInvoiceNo' => $nextStr,
        'factura' => $factura,
        'items' => $items,
        'metodos' => $metodos,
        'products' => $products,
        'mesa_id' => $request->query('mesa_id'),
    ]);
})->name('alquiler');

// Preload caja with products selected from the modal and redirect to caja view
Route::middleware('auth')->post('/alquiler/caja/preload', function (Request $request) {
    $payload = $request->all();
    $products = $payload['products'] ?? null;
    $mesaId = $payload['mesa_id'] ?? null;

    if (! is_array($products) || empty($products)) {
        return response()->json(['success' => false, 'message' => 'No se recibieron productos.'], 400);
    }

    $normalized = [];
    foreach ($products as $i => $it) {
        $id = isset($it['id']) ? (int) $it['id'] : null;
        $qty = isset($it['qty']) ? (int) $it['qty'] : null;
        if (! $id || $id <= 0 || ! $qty || $qty <= 0) {
            return response()->json(['success' => false, 'message' => "Producto inválido en posición {$i}.",], 400);
        }
        $normalized[] = ['producto_id' => $id, 'cant' => $qty];
    }

    // Prepare a minimal caja payload to be consumed by the Caja view
    $caja = [
        'tipo' => 'pos',
        'items' => $normalized,
        'mesa_id' => $mesaId,
        'cliente' => ['nombre' => $mesaId ? "Mesa {$mesaId}" : 'Caja']
    ];

    session(['caja_preload' => $caja]);

    return response()->json(['success' => true, 'redirect' => route('alquiler.caja')]);
})->name('alquiler.caja.preload');

// Caja view and store
Route::middleware('auth')->get('/alquiler/caja', [CajaController::class, 'index'])->name('alquiler.caja');

Route::middleware('auth')->post('/alquiler/caja', [CajaController::class, 'store'])->name('alquiler.caja.store');

// Alquiler - listado (tabla) para CRUD general (mostrar todos los tipos)
Route::middleware('auth')->get('/alquiler/list', function () {
    $facturas = Factura::orderBy('fecha', 'desc')->paginate(20);

    // Load mesas to allow creating orders by mesa from the list view
    $mesas = Mesa::orderBy('nombre')->get();

    return view('alquiler.list', compact('facturas', 'mesas'));
})->name('alquiler.list');

// Provide mesas as JSON for AJAX requests from the web UI
Route::middleware('auth')->get('/mesas/json', function () {
    $mesas = Mesa::orderBy('nombre')->get(['id', 'nombre', 'capacidad']);

    return response()->json($mesas);
})->name('mesas.json');

// Productos as JSON for product selection in modals
Route::middleware('auth')->get('/productos/json', function () {
    $productos = Producto::orderBy('nombre')->get(['id', 'nombre', 'precio']);

    return response()->json($productos);
})->name('productos.json');

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
    $products = Producto::select('id', 'nombre', 'precio')->get();

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
        'address' => '',
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
    $factura = Factura::with(['productos', 'metodosPago'])->findOrFail($id);
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
