<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\MetodoPago;
use App\Models\ProductoXFactura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\Producto;

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
                $invoice = $decoded;
            }
        } elseif ($request->isJson()) {
            // In case the client sent a JSON body without wrapping in `invoice`
            $payload = $request->json()->all();
            if (is_array($payload) && ! empty($payload)) {
                $invoice = $payload;
            }
        }

        if (empty($invoice)) {
            return response()->json(['message' => 'Invoice payload missing'], 422);
        }

        DB::beginTransaction();
        try {
            // Determine and reserve the next consecutive invoice number inside the transaction
            $max = DB::table('facturas')->select(DB::raw('MAX(CAST(numero_orden AS UNSIGNED)) as max'))->lockForUpdate()->value('max');
            $next = $max ? intval($max) + 1 : 1;
            $numeroOrden = str_pad($next, 4, '0', STR_PAD_LEFT);

            // Create factura; force tipo = 'evento' for this interface
            $factura = Factura::create([
                'tipo' => 'evento',
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
                    // If producto_id is null but DB doesn't allow NULL, try to use a fallback product
                    if (is_null($data['producto_id']) && ! $productoIdAllowsNull) {
                        $firstProduct = Producto::first();
                        if ($firstProduct) {
                            $data['producto_id'] = $firstProduct->id;
                        } else {
                            // Create a placeholder product
                            $p = Producto::create(['nombre' => 'Servicio (generado)', 'precio' => $data['precio_unitario']]);
                            $data['producto_id'] = $p->id;
                        }
                    }
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
                'redirect' => route('alquiler'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error creando factura: '.$e->getMessage(), ['exception' => $e]);

            return response()->json(['message' => 'Error creando factura', 'error' => $e->getMessage()], 500);
        }
    }
}
