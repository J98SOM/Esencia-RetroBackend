@php $isPartial = request()->query('partial'); @endphp

@unless($isPartial)
    <!doctype html>
    <html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Ticket - Caja</title>
        <style>
            /* Base print styles */
            body{font-family:Arial,Helvetica,sans-serif;padding:12px;color:#111;background:#fff}
            .print-container{max-width:800px;margin:0 auto}
            .small-muted{color:#666;font-size:12px}

            /* Caja view styles (copied to ensure printed output matches caja view) */
            .caja-top { display:flex; gap:1rem; align-items:center; justify-content:space-between; }
            .caja-logo { display:flex; gap:1rem; align-items:center; }
            .caja-summary { display:flex; gap:1rem; align-items:center; }
            .summary-box { background:transparent; padding:1rem; border-radius:.75rem; min-width:160px; }
            .payments { display:flex; gap:.75rem; flex-direction:column; }
            table.caja-table { width:100%; border-collapse: separate; border-spacing: 0; }
            table.caja-table thead th { background: transparent; color: #111; padding: .75rem; text-align: left; border-bottom: 1px solid rgba(0,0,0,0.06); }
            table.caja-table tbody tr { background: #fff; border-bottom: 1px solid rgba(0,0,0,0.03); }
            table.caja-table tbody tr:nth-child(odd) { background: #fff; }
            table.caja-table tbody tr:hover { background: #fff; }
            table.caja-table th, table.caja-table td { color: #111; padding:.5rem; }
            table.caja-table input, table.caja-table select {
                color: #111; background: transparent; border: 1px solid rgba(0,0,0,0.08); padding: .45rem .5rem; border-radius: .375rem;
            }
            .caja-dd { position: absolute; background: #fff; border: 1px solid rgba(0,0,0,0.06); color: #111; z-index: 1200; max-height: 220px; overflow-y: auto; border-radius: .375rem; }
            .remove-row { background: transparent; color: #111; border: 1px solid rgba(0,0,0,0.08); padding: .2rem .5rem; border-radius: .375rem; font-size: .9rem; }
            /* Table layout for print */
            .print-ticket { max-width:420px; margin:0 auto; }
            .print-ticket h3 { margin:0 0 6px 0; font-size:16px; font-weight:700 }
            .print-ticket .small-muted { font-size:12px; color:#666 }
            .print-ticket table { width:100%; font-size:13px; border-collapse:collapse }
            .print-ticket table td, .print-ticket table th { padding:6px 4px }
            .print-ticket .total-row { display:flex; justify-content:space-between; align-items:center; font-weight:700 }
        </style>
    </head>
    <body>
    <div class="print-container">
@else
    <div class="print-container">
@endunless

    <div style="background:#fff;padding:12px;border-radius:6px;">
        <div style="text-align:center;margin-bottom:8px;">
            <img src="{{ $invoice['company']['logo'] ?? asset('img/logo.png') }}" alt="logo" style="max-width:120px;display:block;margin:0 auto 6px" />
        </div>
        <h3 style="margin:0 0 6px 0;font-size:16px;font-weight:700">{{ $invoice['company']['name'] ?? 'ESENCIA RETRO' }}</h3>
        <div class="small-muted">NIT: {{ $invoice['company']['nit'] ?? '' }}</div>
        <hr style="margin:8px 0">
        <div><strong>No:</strong> {{ $invoice['no'] ?? '' }}</div>
        <div><strong>Fecha:</strong> {{ $invoice['fecha'] ?? now()->toDateString() }}</div>
        <div><strong>Cliente:</strong> {{ $invoice['cliente']['nombre'] ?? '' }}</div>
        <table style="width:100%;margin-top:10px;font-size:13px;border-collapse:collapse">
            <thead>
                <tr>
                    <th style="text-align:left">Descripción</th>
                    <th style="text-align:right;width:60px">Cant</th>
                    <th style="text-align:right;width:90px">Precio</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice['items'] as $it)
                    <tr>
                        <td style="padding:6px 4px">{{ $it['desc'] ?? '' }}</td>
                        <td style="text-align:right;padding:6px 4px">{{ $it['cant'] ?? '' }}</td>
                        <td style="text-align:right;padding:6px 4px">{{ isset($it['precio']) ? '$'.number_format($it['precio'],0,',','.') : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <hr style="margin:8px 0">
        <div style="display:flex;justify-content:space-between;align-items:center">
            <div>Total</div>
            <div style="font-weight:700">{{ isset($invoice['total']) ? '$'.number_format($invoice['total'],0,',','.') : '$0' }}</div>
        </div>
    </div>

@if(! $isPartial && request()->query('autoprint'))
    <script>window.onload = function(){ window.print(); };</script>
@endif

@unless($isPartial)
    </div>
    </body>
    </html>
@endunless
