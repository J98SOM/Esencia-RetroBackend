<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Factura - Imprimir</title>
    @vite('resources/css/pages/alquiler.css')
    <style>
        /* Pequeños ajustes específicos para la plantilla de impresión */
        body { background: #fff; padding: 10mm; }
        .print-container { max-width: 190mm; margin: 0 auto; }
        .meta { font-size: 9pt; color: #555; }
        .company-name { font-weight: 900; font-size: 12pt; color: #111827; }
        .right-col { text-align: right; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 8pt; }
        table.items th, table.items td { padding: 6px 8px; font-size: 9pt; }
        table.items th { border-bottom: 1px solid #ddd; }
        .totals { margin-top: 8pt; }
        .small-muted { color: #777; font-size: 8pt; }
        .total-value { font-weight:900; font-size:14pt; color:#c8a84b; }
        .label-muted { font-size:8pt; color:#666; }
    </style>
</head>
<body>
<div class="print-container">
    <div style="display:grid;grid-template-columns:1fr 220px;gap:8px;align-items:start;border-bottom:1px solid #ddd;padding-bottom:6px;margin-bottom:6px;">
        <div style="display:flex;gap:8px;align-items:center;">
            <div style="width:56px;height:56px;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;overflow:hidden;background:transparent;">
                <img src="{{ asset('img/logo.png') }}" alt="logo" style="max-width:100%;max-height:100%;object-fit:contain;">
            </div>
            <div>
                <div class="company-name">{{ $invoice['company']['name'] ?? 'ESENCIA RETRO' }}</div>
                <div class="meta">NIT: {{ $invoice['company']['nit'] ?? '1,007,450,540' }}</div>
                <div class="meta">Tel: {{ $invoice['company']['tel'] ?? '3162218491' }}</div>
                <div class="meta">Ciudad: {{ $invoice['company']['city'] ?? 'Bogotá' }}</div>
                <div class="meta">Correo: {{ $invoice['company']['email'] ?? 'esenciaretro10@gmail.com' }}</div>
            </div>
        </div>

        <div class="right-col">
            <div style="font-weight:900;color:#c8a84b;letter-spacing:0.06em;">FACTURA DE VENTA</div>
            <div class="meta" style="margin-top:6px;text-align:right;">No. <strong>{{ $invoice['no'] ?? '-' }}</strong></div>
            <div class="meta" style="text-align:right;">Fecha: <strong>{{ $invoice['fecha'] ?? '-' }}</strong></div>
            
            <div class="meta" style="text-align:right; margin-top:6px;">Orden de compra: <strong>{{ $invoice['orden_compra'] ?? '—' }}</strong></div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:6px;">
        <div>
            <div class="small-muted">SEÑORES</div>
            <div style="font-weight:700">{{ $invoice['cliente']['nombre'] ?? '—' }}</div>
            <div class="small-muted">NIT</div>
            <div>{{ $invoice['cliente']['nit'] ?? '—' }}</div>
            <div class="small-muted">DIRECCIÓN</div>
            <div>{{ $invoice['cliente']['direccion'] ?? '—' }}</div>
        </div>
        <div>
            <div class="small-muted">TELÉFONO</div>
            <div>{{ $invoice['cliente']['telefono'] ?? '—' }}</div>
            <div class="small-muted">CIUDAD</div>
            <div>{{ $invoice['cliente']['ciudad'] ?? '—' }}</div>
        </div>
    </div>

    <!-- Orden de compra / Medio de pago (encima de la tabla de ítems) -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;margin-bottom:6px;gap:12px;">
        <div style="font-size:9pt;">
            <strong>Orden de compra:</strong>
            <span style="margin-left:6px;">{{ $invoice['orden_compra'] ?? '—' }}</span>
        </div>
        <div style="font-size:9pt;text-align:right;">
            <strong>Medio de pago:</strong>
            <span style="margin-left:6px;">{{ strtoupper($invoice['medio_pago'] ?? '—') }}</span>
        </div>
    </div>

    <table class="items" aria-label="items">
        <thead>
            <tr style="background:#f4f4f4;color:#333;font-weight:700;">
                <th style="width:30px;text-align:center;">Ítem</th>
                <th>Descripción</th>
                <th style="width:60px;text-align:center;">Cant.</th>
                <th style="width:90px;text-align:right;">Vr. Unitario</th>
                <th style="width:90px;text-align:right;">Vr. Bruto</th>
            </tr>
        </thead>
        <tbody>
        @php $items = $invoice['items'] ?? []; @endphp
        @forelse($items as $index => $it)
            <tr>
                <td style="text-align:center;vertical-align:top;">{{ $index+1 }}</td>
                <td style="vertical-align:top;">{{ $it['desc'] ?? '' }}</td>
                <td style="text-align:center;vertical-align:top;">{{ $it['cant'] ?? '' }}</td>
                <td style="text-align:right;vertical-align:top;">{{ isset($it['precio']) ? '$'.number_format($it['precio'],0,',','.') : '-' }}</td>
                <td style="text-align:right;vertical-align:top;">{{ isset($it['cant'],$it['precio']) ? '$'.number_format(($it['cant']*$it['precio']),0,',','.') : '$0' }}</td>
            </tr>
        @empty
            @for($i=0;$i<5;$i++)
            <tr>
                <td style="text-align:center;vertical-align:top;">{{ $i+1 }}</td>
                <td style="vertical-align:top;"></td>
                <td style="text-align:center;vertical-align:top;"></td>
                <td style="text-align:right;vertical-align:top;"></td>
                <td style="text-align:right;vertical-align:top;"></td>
            </tr>
            @endfor
        @endforelse
        </tbody>
    </table>

    <div class="totals" style="display:flex;justify-content:space-between;align-items:flex-start;margin-top:8pt; gap:12px;">
        <div style="max-width:60%;">
            <div class="small-muted">OBSERVACIONES</div>
            <div style="min-height:24pt;white-space:pre-wrap;">{{ $invoice['observaciones'] ?? '—' }}</div>
        </div>
        <div style="width:220px;text-align:right;">
            <div class="small-muted">TOTAL BRUTO</div>
            <div class="total-value">{{ isset($invoice['total']) ? '$'.number_format($invoice['total'],0,',','.') : '$0' }}</div>
        </div>
    </div>

</div>
<script>window.onload = function(){ window.print(); };</script>
</body>
</html>
