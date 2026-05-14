<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\MetodoPago;
use App\Models\ProductoXFactura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CajaController extends Controller
{
    /**
     * Show caja view with next invoice number precomputed for 'pos' tipo.
     */
    public function index()
    {
        $max = DB::table('facturas')->where('tipo', 'pos')->select(DB::raw('MAX(CAST(numero_orden AS UNSIGNED)) as max'))->value('max');
        $next = $max ? intval($max) + 1 : 1;
        $nextStr = str_pad($next, 4, '0', STR_PAD_LEFT);

        // If the modal sent a preload payload, pass it to the view and clear it from session
        $cajaPreload = session('caja_preload', null);
        if ($cajaPreload) {
            session()->forget('caja_preload');
        }

        return view('alquiler.caja', ['nextInvoiceNo' => $nextStr, 'cajaPreload' => $cajaPreload]);
    }

    /**
     * Store a simple caja sale from the caja view.
     */
    public function store(Request $request)
    {
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

        if (empty($invoice)) {
            return response()->json(['message' => 'Invoice payload missing'], 422);
        }

        DB::beginTransaction();
        try {
            $tipo = $invoice['tipo'] ?? 'pos';
            $max = DB::table('facturas')->where('tipo', $tipo)->select(DB::raw('MAX(CAST(numero_orden AS UNSIGNED)) as max'))->lockForUpdate()->value('max');
            $next = $max ? intval($max) + 1 : 1;
            $numeroOrden = str_pad($next, 4, '0', STR_PAD_LEFT);

            $factura = Factura::create([
                'tipo' => $tipo,
                'numero_orden' => $numeroOrden,
                'fecha' => $invoice['fecha'] ?? now()->toDateString(),
                'persona' => $invoice['cliente']['nombre'] ?? 'Caja',
                'nit' => $invoice['cliente']['nit'] ?? null,
                'direccion' => $invoice['cliente']['direccion'] ?? null,
                'telefono' => $invoice['cliente']['telefono'] ?? null,
                'ciudad' => $invoice['cliente']['ciudad'] ?? null,
                'orden_compra' => $invoice['orden_compra'] ?? null,
                'observaciones' => $invoice['observaciones'] ?? null,
                'mesa_id' => null,
                'estatus' => 'pagada',
                'monto_total' => $invoice['total'] ?? 0,
                'cambio' => 0,
            ]);

            if (! empty($invoice['items']) && is_array($invoice['items'])) {
                foreach ($invoice['items'] as $it) {
                    $data = [
                        'producto_id' => $it['producto_id'] ?? null,
                        'factura_id' => $factura->id,
                        'cantidad' => $it['cant'] ?? ($it['cantidad'] ?? 0),
                        'precio_unitario' => $it['precio'] ?? ($it['precio_unitario'] ?? 0),
                    ];
                    if (Schema::hasColumn('productosxfactura', 'descripcion')) {
                        $data['descripcion'] = $it['desc'] ?? ($it['descripcion'] ?? null);
                    }
                    ProductoXFactura::create($data);
                }
            }

            if (! empty($invoice['metodos']) && is_array($invoice['metodos'])) {
                foreach ($invoice['metodos'] as $mp) {
                    MetodoPago::create(['factura_id' => $factura->id, 'metodo' => $mp['metodo'] ?? null, 'valor' => $mp['valor'] ?? 0]);
                }
            } elseif (! empty($invoice['medio_pago'])) {
                MetodoPago::create(['factura_id' => $factura->id, 'metodo' => $invoice['medio_pago'], 'valor' => $invoice['total'] ?? 0]);
            }

            DB::commit();

            return response()->json(['message' => 'Venta guardada', 'factura_id' => $factura->id, 'numero_orden' => $factura->numero_orden, 'redirect' => route('alquiler.list')]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error creando venta caja: '.$e->getMessage(), ['exception' => $e]);

            return response()->json(['message' => 'Error creando venta', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update an existing caja factura (tipo 'pos').
     */
    public function update(Request $request, $id = null)
    {
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
                if (array_key_exists('invoice', $payload) && is_array($payload['invoice'])) {
                    $invoice = $payload['invoice'];
                } else {
                    $invoice = $payload;
                }
            }
        }

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
                'monto_total' => $invoice['total'] ?? $factura->monto_total,
            ]);

            // Recreate items
            ProductoXFactura::where('factura_id', $factura->id)->delete();
            if (! empty($invoice['items']) && is_array($invoice['items'])) {
                $hasDescripcion = Schema::hasColumn('productosxfactura', 'descripcion');
                foreach ($invoice['items'] as $it) {
                    $data = [
                        'producto_id' => $it['producto_id'] ?? null,
                        'factura_id' => $factura->id,
                        'cantidad' => $it['cant'] ?? ($it['cantidad'] ?? 0),
                        'precio_unitario' => $it['precio'] ?? ($it['precio_unitario'] ?? 0),
                    ];
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
                    MetodoPago::create(['factura_id' => $factura->id, 'metodo' => $mp['metodo'] ?? null, 'valor' => $mp['valor'] ?? 0]);
                }
            } elseif (! empty($invoice['medio_pago'])) {
                MetodoPago::create(['factura_id' => $factura->id, 'metodo' => $invoice['medio_pago'], 'valor' => $invoice['total'] ?? 0]);
            }

            DB::commit();

            return response()->json(['message' => 'Venta actualizada', 'factura_id' => $factura->id]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error actualizando venta caja: '.$e->getMessage(), ['exception' => $e]);

            return response()->json(['message' => 'Error actualizando venta', 'error' => $e->getMessage()], 500);
        }
    }
}
