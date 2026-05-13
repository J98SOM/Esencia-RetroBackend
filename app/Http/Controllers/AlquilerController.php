<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\MetodoPago;
use App\Models\Producto;
use App\Models\ProductoXFactura;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AlquilerController extends Controller
{
    /**
     * Store a new invoice (factura) with items and payment methods.
     * Expects a JSON `invoice` payload (same structure as the frontend).
     */
    public function store(Request $request)
    {
        $invoiceInput = $request->input('invoice');
        $invoice = [];

        // Accept either a JSON string or an already-decoded array from the frontend.
        if (is_array($invoiceInput)) {
            $invoice = $invoiceInput;
        } elseif (is_string($invoiceInput) && $invoiceInput !== '') {
            $decoded = json_decode($invoiceInput, true);
            if (is_array($decoded)) {
                if (array_key_exists('invoice', $decoded) && is_array($decoded['invoice'])) {
                    $invoice = $decoded['invoice'];
                } else {
                    $invoice = $decoded;
                }
            }
        } elseif ($request->isJson()) {
            // In case the client sent a JSON body. Accept both formats:
            // { "invoice": { ... } }  OR  { ...items... } (raw invoice)
            $payload = $request->json()->all();
            if (is_array($payload) && ! empty($payload)) {
                if (array_key_exists('invoice', $payload) && is_array($payload['invoice'])) {
                    $invoice = $payload['invoice'];
                } else {
                    $invoice = $payload;
                }
            }
        }

        if (empty($invoice)) {
            return response()->json(['message' => 'Invoice payload missing'], 422);
        }

        // If frontend sent factura_id, delegate to update() to avoid creating duplicates
        if (! empty($invoice['factura_id'])) {
            return $this->update($request);
        }

        DB::beginTransaction();
        try {
            // Determine and reserve the next consecutive invoice number inside the transaction
            $tipo = $invoice['tipo'] ?? 'evento';
            $max = DB::table('facturas')->where('tipo', $tipo)->select(DB::raw('MAX(CAST(numero_orden AS UNSIGNED)) as max'))->lockForUpdate()->value('max');
            $next = $max ? intval($max) + 1 : 1;
            $numeroOrden = str_pad($next, 4, '0', STR_PAD_LEFT);

            // Create factura with the specified tipo (defaults to 'evento')
            $factura = Factura::create([
                'tipo' => $tipo,
                'numero_orden' => $numeroOrden,
                'fecha' => $invoice['fecha'] ?? now()->toDateString(),
                'persona' => $invoice['cliente']['nombre'] ?? null,
                'nit' => $invoice['cliente']['nit'] ?? null,
                'direccion' => $invoice['cliente']['direccion'] ?? null,
                'telefono' => $invoice['cliente']['telefono'] ?? null,
                'ciudad' => $invoice['cliente']['ciudad'] ?? null,
                'orden_compra' => $invoice['orden_compra'] ?? null,
                'observaciones' => $invoice['observaciones'] ?? null,
                'mesa_id' => $invoice['mesa_id'] ?? null,
                'estatus' => $invoice['estatus'] ?? 'pagada',
                'monto_total' => $invoice['total'] ?? 0,
                'cambio' => $invoice['cambio'] ?? 0,
            ]);

            // Items
            // Items
            if (! empty($invoice['items']) && is_array($invoice['items'])) {
                Log::info('AlquilerController: items payload received', ['items' => $invoice['items']]);
                // Determine if producto_id column allows NULL in this DB
                $productoIdAllowsNull = true;
                try {
                    $col = DB::select("SHOW COLUMNS FROM productosxfactura WHERE Field = 'producto_id'");
                    if (! empty($col)) {
                        $col = (array) $col[0];
                        $productoIdAllowsNull = (strtoupper($col['Null'] ?? 'YES') === 'YES');
                    }
                } catch (\Throwable$e) {
                    // ignore and assume nullable
                }

                foreach ($invoice['items'] as $it) {
                    // If frontend doesn't provide producto_id, skip or fallback
                    $data = [
                        'producto_id' => $it['producto_id'] ?? null,
                        'factura_id' => $factura->id,
                        'cantidad' => $it['cant'] ?? ($it['cantidad'] ?? 0),
                        'precio_unitario' => $it['precio'] ?? ($it['precio_unitario'] ?? 0),
                    ];
                    // Normalize producto_id: treat empty string or non-numeric as null
                    if (array_key_exists('producto_id', $it)) {
                        $pid = $it['producto_id'];
                        if ($pid === '' || $pid === null) {
                            $data['producto_id'] = null;
                        } elseif (! is_numeric($pid)) {
                            $data['producto_id'] = null;
                        } else {
                            $data['producto_id'] = intval($pid);
                        }
                    }
                    // Do NOT fallback to a first product here. If producto_id is null,
                    // we will store NULL so that manually-entered descriptions do not
                    // get associated to an arbitrary product.
                    // Add descripcion only if the column exists in the schema
                    if (Schema::hasColumn('productosxfactura', 'descripcion')) {
                        $data['descripcion'] = $it['desc'] ?? ($it['descripcion'] ?? null);
                    }
                    $created = ProductoXFactura::create($data);
                    Log::info('ProductoXFactura created', ['id' => $created->id, 'data' => $created->toArray()]);
                }
            } else {
                Log::info('AlquilerController: no items found in payload');
            }

            // Metodos de pago
            if (! empty($invoice['metodos']) && is_array($invoice['metodos'])) {
                foreach ($invoice['metodos'] as $mp) {
                    MetodoPago::create([
                        'factura_id' => $factura->id,
                        'metodo' => $mp['metodo'] ?? null,
                        // Some DB schemas may not allow NULL here; fallback to 0
                        'valor' => isset($mp['valor']) ? $mp['valor'] : 0,
                    ]);
                }
            } else {
                // fallback: use single medio_pago field — save only the method, valor=NULL
                if (! empty($invoice['medio_pago'])) {
                    MetodoPago::create([
                        'factura_id' => $factura->id,
                        'metodo' => $invoice['medio_pago'],
                        'valor' => null,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Factura creada',
                'factura_id' => $factura->id,
                'redirect' => route('alquiler.list'),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error creando factura: '.$e->getMessage(), ['exception' => $e]);

            return response()->json(['message' => 'Error creando factura', 'error' => $e->getMessage()], 500);
        }
    }



    /**
     * Generate and return a PDF for the given factura id.
     */
    public function pdf($id)
    {
        $factura = Factura::with(['productos', 'metodosPago'])->find($id);
        if (! $factura) {
            abort(404);
        }

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
            'company' => [
                'name' => 'ESENCIA RETRO',
                'nit' => '1,007,450,540',
                'tel' => '3162218491 - 3209180085',
                'city' => 'Bogotá',
                'email' => 'esenciaretro10@gmail.com',
                'address' => ''
            ],
        ];

        foreach ($factura->productos as $it) {
            $invoice['items'][] = [
                'desc' => $it->descripcion ?? ($it->producto?->nombre ?? ''),
                'cant' => $it->cantidad,
                'precio' => $it->precio_unitario,
            ];
        }

        $pdf = Pdf::loadView('alquiler.print', ['invoice' => $invoice]);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('factura-'.$invoice['no'].'.pdf');
    }

    /**
     * Update an existing factura and its items/metodos.
     */
    public function update(Request $request, $id = null)
    {
        $invoiceInput = $request->input('invoice');
        $invoice = [];

        if (is_string($invoiceInput)) {
            $decoded = json_decode($invoiceInput, true);
            if (is_array($decoded)) {
                $invoice = $decoded;
            } else {
                $invoice = [];
            }
        } elseif (is_array($invoiceInput)) {
            $invoice = $invoiceInput;
        }

        // If the route provided an id (API clients using /api/alquiler/{id}),
        // accept it when the payload doesn't include factura_id.
        if ((empty($invoice) || empty($invoice['factura_id'])) && $id) {
            $invoice['factura_id'] = $id;
        }

        if (empty($invoice) || empty($invoice['factura_id'])) {
            return response()->json(['message' => 'Invoice or factura_id missing'], 422);
        }

        $factura = Factura::find($invoice['factura_id']);
        if (! $factura) {
            return response()->json(['message' => 'Factura no encontrada'], 404);
        }

        Log::debug('AlquilerController.update payload', ['invoice' => $invoice]);
        DB::beginTransaction();
        try {
            $factura->update([
                'fecha' => $invoice['fecha'] ?? $factura->fecha,
                'persona' => $invoice['cliente']['nombre'] ?? $factura->persona,
                'nit' => $invoice['cliente']['nit'] ?? $factura->nit,
                'direccion' => $invoice['cliente']['direccion'] ?? $factura->direccion,
                'telefono' => $invoice['cliente']['telefono'] ?? $factura->telefono,
                'ciudad' => $invoice['cliente']['ciudad'] ?? $factura->ciudad,
                'orden_compra' => $invoice['orden_compra'] ?? $factura->orden_compra,
                'observaciones' => $invoice['observaciones'] ?? $factura->observaciones,
                'estatus' => $invoice['estatus'] ?? $factura->estatus,
                'monto_total' => $invoice['total'] ?? $factura->monto_total,
            ]);

            // Recreate items (preserve fallback logic if producto_id missing)
            ProductoXFactura::where('factura_id', $factura->id)->delete();
            if (! empty($invoice['items']) && is_array($invoice['items'])) {
                $hasDescripcion = Schema::hasColumn('productosxfactura', 'descripcion');
                // determine if producto_id allows NULL
                $productoIdAllowsNull = true;
                try {
                    $col = DB::select("SHOW COLUMNS FROM productosxfactura WHERE Field = 'producto_id'");
                    if (! empty($col)) {
                        $col = (array) $col[0];
                        $productoIdAllowsNull = (strtoupper($col['Null'] ?? 'YES') === 'YES');
                    }
                } catch (\Throwable$e) {
                    // ignore and assume nullable
                }

                foreach ($invoice['items'] as $it) {
                    $data = [
                        'producto_id' => $it['producto_id'] ?? null,
                        'factura_id' => $factura->id,
                        'cantidad' => $it['cant'] ?? ($it['cantidad'] ?? 0),
                        'precio_unitario' => $it['precio'] ?? ($it['precio_unitario'] ?? 0),
                    ];
                    // Normalize producto_id: treat empty string or non-numeric as null
                    if (array_key_exists('producto_id', $it)) {
                        $pid = $it['producto_id'];
                        if ($pid === '' || $pid === null) {
                            $data['producto_id'] = null;
                        } elseif (! is_numeric($pid)) {
                            $data['producto_id'] = null;
                        } else {
                            $data['producto_id'] = intval($pid);
                        }
                    }
                    // Intentionally do not fallback here either; preserve NULL.
                    if ($hasDescripcion) {
                        $data['descripcion'] = $it['desc'] ?? ($it['descripcion'] ?? null);
                    }
                    ProductoXFactura::create($data);
                }
            }

            // Recreate metodos
            MetodoPago::where('factura_id', $factura->id)->delete();
            if (! empty($invoice['metodos']) && is_array($invoice['metodos'])) {
                foreach ($invoice['metodos'] as $mp) {
                    MetodoPago::create([
                        'factura_id' => $factura->id,
                        'metodo' => $mp['metodo'] ?? null,
                        'valor' => isset($mp['valor']) ? $mp['valor'] : 0,
                    ]);
                }
            } else {
                if (! empty($invoice['medio_pago'])) {
                    MetodoPago::create([
                        'factura_id' => $factura->id,
                        'metodo' => $invoice['medio_pago'],
                        'valor' => null,
                    ]);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Factura actualizada', 'factura_id' => $factura->id, 'redirect' => route('alquiler.list')]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error actualizando factura: '.$e->getMessage(), ['exception' => $e]);

            return response()->json(['message' => 'Error actualizando factura', 'error' => $e->getMessage()], 500);
        }
    }
}
