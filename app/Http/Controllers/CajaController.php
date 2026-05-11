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
        return view('alquiler.caja', ['nextInvoiceNo' => $nextStr]);
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
            if (is_array($decoded)) $invoice = $decoded;
        } elseif ($request->isJson()) {
            $payload = $request->json()->all();
            if (is_array($payload) && ! empty($payload)) $invoice = $payload;
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
}
