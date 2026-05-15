@extends('layouts.app')

@section('title', 'Esencia Retro - Caja')

@push('styles')
    @vite('resources/css/pages/alquiler.css')
    <style>
        .caja-top { display:flex; gap:1rem; align-items:center; justify-content:space-between; }
        .caja-logo { display:flex; gap:1rem; align-items:center; }
        .caja-summary { display:flex; gap:1rem; align-items:center; }
        .summary-box { background:rgba(255,255,255,0.03); padding:1rem; border-radius:.75rem; min-width:160px; }
        .payments { display:flex; gap:.75rem; flex-direction:column; }
        .payments input { width:180px; }
        /* Table inputs: high-contrast for dark background */
        table.caja-table { width:100%; border-collapse: separate; border-spacing: 0; }
        table.caja-table thead th { background: rgba(255,255,255,0.04) !important; color: #ffffff !important; padding: .75rem; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.06); }
        table.caja-table thead th:first-child { padding-left: .75rem; }
        table.caja-table tbody tr { background: rgba(255,255,255,0.01); border-bottom: 1px solid rgba(255,255,255,0.03); }
        table.caja-table tbody tr:nth-child(odd) { background: rgba(255,255,255,0.015); }
        table.caja-table tbody tr:hover { background: rgba(255,255,255,0.03); }
        table.caja-table th, table.caja-table td { color: #ffffff !important; }

        table.caja-table input, table.caja-table select {
            color: #ffffff !important;
            background: rgba(255,255,255,0.03) !important;
            border: 1px solid rgba(255,255,255,0.08) !important;
            padding: .45rem .5rem !important;
            border-radius: .375rem !important;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.01);
        }
        table.caja-table input::placeholder { color: rgba(255,255,255,0.55) !important; }
        table.caja-table input[type="number"] { text-align: right; }
        table.caja-table .caja-sub { color: #ffffff !important; font-weight: 700; }

        /* Make remove button visible and consistent */
        .remove-row { background: transparent; color: #fff; border: 1px solid rgba(255,255,255,0.08); padding: .2rem .5rem; border-radius: .375rem; font-size: .9rem; }
        .remove-row.hidden { display: none !important; }
        /* Ensure payment controls are readable on dark background */
        .payment-method, .payment-amount { color: #ffffff !important; }
        .payment-method option { color: #000000; }
        .payment-amount::placeholder { color: rgba(255,255,255,0.6); }
        /* Small legend for available payment methods */
        .payments-legend { color: #ffffff; font-size: 0.85rem; margin-bottom: .25rem; opacity: .9; }
        table.caja-table td, table.caja-table th { padding:.5rem; }
        /* Autocomplete dropdown */
        .caja-dd { position: absolute; background: #0b1220; border: 1px solid rgba(255,255,255,0.06); color: #fff; z-index: 1200; max-height: 220px; overflow-y: auto; border-radius: .375rem; box-shadow: 0 6px 18px rgba(0,0,0,0.6); }
        .caja-dd .item { padding: .45rem .6rem; cursor: pointer; border-bottom: 1px solid rgba(255,255,255,0.02); }
        .caja-dd .item:hover { background: rgba(255,255,255,0.02); }
        /* Table scroll area: limit height and scroll only the table body */
        .caja-table-wrap { max-height: calc(100vh - 360px); overflow-y: auto; }
        .caja-table thead th { position: sticky; top: 0; z-index: 5; }
    </style>
@endpush

@section('content')
<div class="p-6 lg:p-8 flex-1">
    <header class="mb-6 no-print">
        <h2 class="text-2xl font-extrabold text-white">Caja - Registrar Productos</h2>
    </header>

    <div class="bg-surface-container-low border border-white/5 rounded-2xl overflow-hidden shadow-2xl p-6">
        <div class="caja-top mb-6">
            <div class="caja-logo">
                <img src="{{ asset('img/logo.png') }}" alt="logo" class="w-20 h-20 object-contain">
                <div>
                    <h3 class="text-white font-black">Esencia Retro - Caja</h3>
                    <p class="text-on-surface-variant text-sm">Registra productos y pagos</p>
                    <div class="text-on-surface-variant text-xs mt-2">
                        <div>NIT: 1,007,450,540</div>
                        <div>Tel: 3162218491 - 3209180085</div>
                        <div>Ciudad: Bogotá</div>
                        <div>Correo: esenciaretro10@gmail.com</div>
                    </div>
                    <div class="mt-3">
                        <label class="text-on-surface-variant text-xs">No. Orden</label>
                        <input id="caja-factura-no" type="text" readonly data-auto-generated="1" class="mt-1 px-2 py-1 rounded bg-surface-container-highest text-white text-sm" value="{{ ($nextInvoiceNo ?? '') ?: '0001' }}">
                    </div>
                </div>
            </div>

            <div class="caja-summary">
                <div class="summary-box">
                    <div class="text-on-surface-variant text-xs">Total</div>
                    <div id="caja-total" class="text-white text-2xl font-black">$0</div>
                </div>
                <div class="summary-box">
                    <div class="text-on-surface-variant text-xs">Total recibido</div>
                    <div id="caja-recibido" class="text-white text-2xl font-black">$0</div>
                </div>
                <div class="summary-box">
                    <div class="text-on-surface-variant text-xs">Vueltas</div>
                    <div id="caja-vueltas" class="text-white text-xl font-bold">$0</div>
                </div>
            </div>

            <div class="payments">
                <div class="payments-legend">Ingrese montos por método</div>
                <div class="grid grid-cols-1 gap-2">
                    <div class="flex items-center gap-2">
                        <label class="text-on-surface-variant text-sm w-24">Efectivo</label>
                        <input id="p-efectivo" type="number" min="0" step="0.01" class="px-3 py-2 rounded-lg bg-surface-container-highest text-white" placeholder="0">
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-on-surface-variant text-sm w-24">Tarjeta</label>
                        <input id="p-tarjeta" type="number" min="0" step="0.01" class="px-3 py-2 rounded-lg bg-surface-container-highest text-white" placeholder="0">
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-on-surface-variant text-sm w-24">QR</label>
                        <input id="p-qr" type="number" min="0" step="0.01" class="px-3 py-2 rounded-lg bg-surface-container-highest text-white" placeholder="0">
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4 flex items-center justify-between">
            <div>
                <button id="add-row" class="px-4 py-2 rounded-lg bg-primary/10 border border-primary/20 text-primary">Agregar fila</button>
                <button id="btn-guardar-caja" class="ml-3 px-4 py-2 rounded-lg bg-surface-container-highest border border-white/10 text-white">Guardar</button>
                <button id="btn-imprimir-ticket" class="ml-3 px-4 py-2 rounded-lg bg-primary/10 border border-primary/20 text-primary">Imprimir Ticket</button>
            </div>
        </div>

        <div class="overflow-x-auto caja-table-wrap">
            <table class="w-full text-sm caja-table">
                <thead>
                    <tr class="bg-surface-container-high border-b border-white/10">
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="caja-body">
                    <!-- Fila inicial renderizada por servidor (visible si JS no corre) -->
                    <tr>
                        <td><input class="caja-id text-sm" value=""></td>
                        <td><input class="caja-name w-full text-sm" value="" placeholder="Nombre del producto"></td>
                        <td><input type="number" min="0" step="1" class="caja-cant" value="1"></td>
                        <td><div class="caja-price" data-price="0">$0</div></td>
                        <td class="caja-sub">$0</td>
                        <td><input type="button" class="remove-row hidden" value="Quitar"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        
    </div>
</div>
@endsection

@push('scripts')
    <script src="/js/pos-printer.js"></script>
    @php
        // Prefer inlining the local source when present to avoid Vite/dev-server 404s in development environments.
        // Prefer a pre-copied public file if present to avoid inlining issues
        if (file_exists(public_path('js/pages/caja.inline.js'))) {
            echo '<script src="' . asset('js/pages/caja.inline.js') . '"></script>';
        } elseif (file_exists(resource_path('js/pages/caja.js'))) {
            $__c = file_get_contents(resource_path('js/pages/caja.js'));
            // strip BOM if present and escape closing script tags to avoid truncation
            $__c = preg_replace('/^\xEF\xBB\xBF/', '', $__c);
            $__c = str_replace('</script>', '<\/script>', $__c);
            echo '<script>' . PHP_EOL . $__c . PHP_EOL . '</script>';
        } else {
            if (function_exists('vite')) {
                try {
                    echo vite('resources/js/pages/caja.js');
                } catch (\Illuminate\Foundation\ViteException $e) {
                    if (file_exists(public_path('build/manifest.json'))) {
                        echo '<script type="module" src="' . asset('js/pages/caja.js') . '"></script>';
                    } else {
                        echo '<script type="module" src="http://localhost:5173/resources/js/pages/caja.js"></script>';
                    }
                }
            } else {
                if (file_exists(public_path('build/manifest.json'))) {
                    echo '<script type="module" src="' . asset('js/pages/caja.js') . '"></script>';
                } else {
                    // Last resort: point to vite dev server
                    echo '<script type="module" src="http://localhost:5173/resources/js/pages/caja.js"></script>';
                }
            }
        }
    @endphp
@endpush
    <script>
        window.COMPANY = {
            name: 'ESENCIA RETRO',
            nit: '1,007,450,540',
            rut: '',
            phone: '3162218491 - 3209180085',
            city: 'Bogotá',
            email: 'esenciaretro10@gmail.com',
            address: ''
        };
        window.NEXT_INVOICE_NO = '{{ ($nextInvoiceNo ?? '') ?: '0001' }}';
        window.CAJA_PRELOAD = {!! json_encode($cajaPreload ?? null) !!};
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.CAJA_PRELOAD && Array.isArray(window.CAJA_PRELOAD.items) && window.CAJA_PRELOAD.items.length) {
                fetch('/productos/json')
                    .then(r => r.json())
                    .then(products => {
                        const byId = {};
                        products.forEach(p => byId[p.id] = p);
                        const tbody = document.getElementById('caja-body');
                        if (!tbody) return;
                        tbody.innerHTML = '';
                        let total = 0;
                        window.CAJA_PRELOAD.items.forEach(it => {
                            const prod = byId[it.producto_id] || { id: it.producto_id, nombre: '', precio: 0 };
                            const qty = parseInt(it.cant, 10) || 1;
                            const price = parseFloat(prod.precio) || 0;
                            const subtotal = price * qty;
                            total += subtotal;

                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td><input class="caja-id text-sm" value="${prod.id}"></td>
                                <td><input class="caja-name w-full text-sm" value="${prod.nombre}" placeholder="Nombre del producto"></td>
                                <td><input type="number" min="0" step="1" class="caja-cant" value="${qty}"></td>
                                <td><div class="caja-price" data-price="${price}">$${price.toFixed(2)}</div></td>
                                <td class="caja-sub">$${subtotal.toFixed(2)}</td>
                                <td><input type="button" class="remove-row" value="Quitar"></td>
                            `;
                            tbody.appendChild(tr);
                        });

                        const totalEl = document.getElementById('caja-total');
                        if (totalEl) totalEl.textContent = '$' + total.toFixed(2);
                        const recibidoEl = document.getElementById('caja-recibido');
                        if (recibidoEl) recibidoEl.textContent = '$0';
                    })
                    .catch(err => console.error('Error cargando productos para preload caja', err));
            }
        });
    </script>
