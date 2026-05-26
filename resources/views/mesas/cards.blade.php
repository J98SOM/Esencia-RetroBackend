@extends('layouts.app')

@section('title', 'Mesas - Tarjetas')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">Mesas</h1>
        <a href="{{ route('mesas.index') }}" class="text-sm px-4 py-2 rounded bg-surface-container-highest text-white">Ver lista</a>
    </div>

    @if($mesas->isEmpty())
        <div class="p-6 bg-surface-container-low rounded-lg text-white/70">No hay mesas registradas.</div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($mesas as $mesa)
                <div class="bg-gradient-to-br from-surface-container to-surface-container-low rounded-2xl p-6 shadow-[0_0_25px_rgba(0,0,0,0.7)] h-44 border border-primary-container/20 relative flex flex-col justify-between">
                    @php
                        $latest = $mesa->latestFactura ?? null;
                        $estatus = $latest ? trim(strtolower($latest->estatus)) : null;
                    @endphp
                    {{-- Status badge (inline next to ID) --}}
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-primary-container/30 flex items-center justify-center border border-primary-container/40">
                                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18"/></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white leading-tight">{{ $mesa->nombre }}</h3>
                                <p class="text-sm text-white/70">Capacidad: <strong class="text-white">{{ $mesa->capacidad }}</strong></p>
                            </div>
                        </div>
                        <div class="mesa-meta flex items-center gap-2">
                            <div class="text-sm text-white/60">#{{ $mesa->id }}</div>
                            @if($estatus === 'pendiente')
                                <span class="mesa-status-badge inline-flex items-center bg-amber-600 text-white text-[11px] px-3 py-1 rounded-full shadow-sm">Pendiente</span>
                            @elseif($estatus === 'pagado' || $estatus === 'pagada')
                                <span class="mesa-status-badge inline-flex items-center bg-emerald-600 text-white text-[11px] px-3 py-1 rounded-full shadow-sm">Disponible</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-end justify-between">
                        <div></div>
                        <div class="flex items-center">
                            <button type="button" data-mesa-id="{{ $mesa->id }}" data-mesa-name="{{ $mesa->nombre }}" class="open-invoice-modal px-3 py-2 rounded bg-emerald-600 text-white hover:bg-emerald-700 text-xs font-medium shadow">Generar facturación</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Invoice Modal -->
<div id="invoice-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-gray-800 border border-gray-700 rounded-lg w-11/12 md:w-3/4 lg:w-2/3 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-white">Generar Facturación</h3>
            <button id="invoice-close" class="text-white/70">Cerrar</button>
        </div>

        <div id="invoice-mesa" class="mb-4 text-sm text-white">Mesa: <strong id="invoice-mesa-name"></strong> (<span id="invoice-mesa-id"></span>)</div>

        <div class="mb-4">
            <div class="flex items-center gap-3 mb-2">
                <input id="invoice-search" type="search" placeholder="Buscar producto por id o nombre" class="flex-1 p-2 rounded bg-gray-700 text-white placeholder-gray-300 border border-gray-600" />
                <label class="inline-flex items-center gap-2 text-sm text-gray-200">
                    <input id="invoice-show-selected" type="checkbox" class="form-checkbox h-4 w-4 text-emerald-500" />
                    Mostrar seleccionados
                </label>
            </div>

            <div class="overflow-auto max-h-72 bg-gray-800 border border-gray-700 rounded">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-700 border-b border-gray-600">
                        <tr>
                            <th class="p-2 text-white">#</th>
                            <th class="p-2 text-white">Producto</th>
                            <th class="p-2 text-white">Precio</th>
                            <th class="p-2 text-white">Cantidad</th>
                            <th class="p-2 text-white">Seleccionar</th>
                        </tr>
                    </thead>
                    <tbody id="invoice-products-body" class="bg-gray-800"></tbody>
                </table>
            </div>
        </div>

            <div class="flex justify-between items-center gap-2">
                <div id="invoice-selected-count" class="text-sm text-gray-200">Seleccionados: 0</div>
                <div class="flex justify-end gap-2">
            <button id="invoice-cancel" class="px-4 py-2 rounded bg-surface-container-highest text-white">Cancelar</button>
            <button id="invoice-add" class="px-4 py-2 rounded bg-amber-600 text-white">Añadir a factura</button>
            <button id="invoice-create" class="px-4 py-2 rounded bg-emerald-600 text-white">Facturar</button>
                </div>
            </div>
    </div>
</div>

@push('scripts')
<script>
@php
    $facturaSelections = [];
    foreach ($mesas as $m) {
        $items = [];
        $latestFactura = $m->latestFactura ?? null;
        $latestStatus = $latestFactura ? trim(strtolower($latestFactura->estatus)) : null;
        if ($latestFactura && ! in_array($latestStatus, ['pagado', 'pagada'], true) && isset($latestFactura->productos)) {
            foreach ($latestFactura->productos as $pxf) {
                // $pxf is a ProductoXFactura model: use producto_id and cantidad
                $prodId = $pxf->producto_id ?? ($pxf->producto->id ?? null);
                $qtyRaw = $pxf->cantidad ?? 1;
                // cast to integer to avoid preloading decimals like 1.00
                $qty = intval(round($qtyRaw));
                if ($prodId) $items[intval($prodId)] = $qty;
            }
        }
        $facturaSelections[$m->id] = $items;
    }
@endphp

const facturaSelections = {!! json_encode($facturaSelections) !!};

document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('invoice-modal');
    const body = document.getElementById('invoice-products-body');
    const mesaNameEl = document.getElementById('invoice-mesa-name');
    const mesaIdEl = document.getElementById('invoice-mesa-id');
    const searchInput = document.getElementById('invoice-search');
    let selectedMesaId = null;
    let productsCache = null;
    let currentFilter = '';
    // store selections per mesa: { [mesaId]: { [productoId]: qty } }
    // initialize from existing factura products (if any)
    let selectedProductsByMesa = facturaSelections || {};
    const showSelectedCheckbox = document.getElementById('invoice-show-selected');
    const selectedCountEl = document.getElementById('invoice-selected-count');

    function openModal(mesaId, mesaName) {
        selectedMesaId = mesaId;
        mesaNameEl.textContent = mesaName;
        mesaIdEl.textContent = mesaId;
        // ensure selection map exists for this mesa
        if (!selectedProductsByMesa[selectedMesaId]) selectedProductsByMesa[selectedMesaId] = {};
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        if (!productsCache) {
            fetch('/productos/json')
                .then(r => r.json())
                .then(data => {
                    productsCache = data;
                    renderProducts(filteredProducts());
                })
                .catch(err => {
                    body.innerHTML = '<tr><td colspan="5" class="p-4 text-red-400">Error cargando productos.</td></tr>';
                });
        } else {
            renderProducts(filteredProducts());
        }
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function filterProducts(q) {
        if (!q || q.trim() === '') return productsCache || [];
        const term = q.toString().toLowerCase();
        return (productsCache || []).filter(p => {
            return p.id.toString() === term || p.id.toString().includes(term) || (p.nombre && p.nombre.toLowerCase().includes(term));
        });
    }

    function filteredProducts() {
        // If show-selected is checked, return only selected products
        const currentSelected = (selectedProductsByMesa[selectedMesaId] || {});
        if (showSelectedCheckbox && showSelectedCheckbox.checked) {
            if (!productsCache) return [];
            return productsCache.filter(p => currentSelected[p.id]);
        }
        return filterProducts(currentFilter);
    }

    function renderProducts(products) {
        body.innerHTML = '';
        const currentSelected = (selectedProductsByMesa[selectedMesaId] || {});
        products.forEach((p, idx) => {
            const tr = document.createElement('tr');
                tr.className = 'border-b border-gray-700';
            const qty = currentSelected[p.id] || 1;
            const checked = currentSelected[p.id] ? 'checked' : '';
            tr.innerHTML = `
                <td class="p-2 text-white">${idx+1}</td>
                <td class="p-2 text-white">${p.nombre}</td>
                <td class="p-2 text-white">${p.precio}</td>
                <td class="p-2"><input type="number" min="1" value="${qty}" data-product-qty="${p.id}" class="w-20 rounded bg-gray-700 border border-gray-600 p-1 text-white text-sm"/></td>
                <td class="p-2 text-center"><input type="checkbox" data-product-id="${p.id}" ${checked}/></td>
            `;
            body.appendChild(tr);
            // attach handlers to inputs/checkboxes
            const cb = tr.querySelector('[data-product-id]');
            const qtyInput = tr.querySelector('[data-product-qty]');
            if (cb) {
                cb.addEventListener('change', function () {
                    const id = parseInt(this.dataset.productId, 10);
                    const q = parseInt(qtyInput.value) || 1;
                    const map = selectedProductsByMesa[selectedMesaId] || (selectedProductsByMesa[selectedMesaId] = {});
                    if (this.checked) {
                        map[id] = q;
                    } else {
                        delete map[id];
                    }
                    updateSelectedCount();
                });
            }
            if (qtyInput) {
                qtyInput.addEventListener('change', function () {
                    const id = parseInt(this.dataset.productQty, 10);
                    const q = parseInt(this.value) || 1;
                    const map = selectedProductsByMesa[selectedMesaId] || (selectedProductsByMesa[selectedMesaId] = {});
                    // Always set the quantity so previous quantities are preserved and editable
                    map[id] = q;
                    // Ensure the checkbox is checked when user edits quantity
                    if (cb && !cb.checked) cb.checked = true;
                    updateSelectedCount();
                });
            }
        });
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const map = selectedProductsByMesa[selectedMesaId] || {};
        const count = Object.keys(map).length;
        if (selectedCountEl) selectedCountEl.textContent = `Seleccionados: ${count}`;
    }

    // Debounced search handler
    function debounce(fn, wait) {
        let t;
        return function (...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), wait);
        };
    }

    const handleSearch = debounce(function (e) {
        currentFilter = e.target.value || '';
        renderProducts(filteredProducts());
    }, 200);

    if (searchInput) searchInput.addEventListener('input', handleSearch);
    if (showSelectedCheckbox) showSelectedCheckbox.addEventListener('change', function () {
        renderProducts(filteredProducts());
    });

    document.querySelectorAll('.open-invoice-modal').forEach(btn => {
        btn.addEventListener('click', function () {
            const mesaId = this.dataset.mesaId;
            const mesaName = this.dataset.mesaName;
            openModal(mesaId, mesaName);
        });
    });

    document.getElementById('invoice-close').addEventListener('click', closeModal);
    document.getElementById('invoice-cancel').addEventListener('click', closeModal);

    // Invoice create: open Caja with preloaded items ready to finalize the factura
    document.getElementById('invoice-create').addEventListener('click', function (e) {
        e.preventDefault();
        const map = selectedProductsByMesa[selectedMesaId] || {};
        const selected = Object.keys(map).map(id => ({ id: parseInt(id, 10), qty: map[id] }));

        if (selected.length === 0) {
            Swal.fire('Atención', 'Selecciona al menos un producto para facturar.', 'warning');
            return;
        }

        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch('/alquiler/caja/preload', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ products: selected, mesa_id: selectedMesaId })
        }).then(r => r.json()).then(resp => {
            if (resp && resp.success && resp.redirect) {
                // clear selections for this mesa so we don't carry duplicates if user returns
                if (selectedProductsByMesa && selectedProductsByMesa[selectedMesaId]) {
                    selectedProductsByMesa[selectedMesaId] = {};
                }
                closeModal();
                // navigate to caja where the payload will be loaded from session
                window.location = resp.redirect;
            } else {
                Swal.fire('Error', resp && resp.message ? resp.message : 'No se pudo abrir caja.', 'error');
            }
        }).catch(err => {
            console.error(err);
            Swal.fire('Error', 'Error de red al abrir caja.', 'error');
        });
    });

    // Handler: Añadir a factura -> append selected products to existing open factura for the mesa
    document.getElementById('invoice-add').addEventListener('click', function () {
        const map = selectedProductsByMesa[selectedMesaId] || {};
        const selected = Object.keys(map).map(id => ({ id: parseInt(id, 10), qty: map[id] }));

        if (selected.length === 0) {
            Swal.fire('Atención', 'Selecciona al menos un producto para añadir.', 'warning');
            return;
        }

        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch('/mesas/' + selectedMesaId + '/add-to-factura', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ products: selected })
        }).then(r => r.json()).then(resp => {
            if (resp && resp.success) {
                // update card badge to Pendiente
                const btn = document.querySelector('[data-mesa-id="' + selectedMesaId + '"]');
                if (btn) {
                    const card = btn.closest('.relative');
                    if (card) {
                        const meta = card.querySelector('.mesa-meta');
                        if (meta) {
                            const old = meta.querySelector('.mesa-status-badge');
                            if (old) old.remove();
                            const span = document.createElement('span');
                            span.className = 'mesa-status-badge inline-flex items-center bg-amber-600 text-white text-[11px] px-3 py-1 rounded-full shadow-sm';
                            span.textContent = 'Pendiente';
                            meta.appendChild(span);
                        }
                    }
                }
                // clear in-memory selections for this mesa to avoid duplicates
                if (selectedProductsByMesa && selectedProductsByMesa[selectedMesaId]) {
                    selectedProductsByMesa[selectedMesaId] = {};
                }
                // close modal and reload to reflect saved factura immediately
                closeModal();
                Swal.fire('Añadido', 'Productos añadidos a la factura (o creada) correctamente.', 'success')
                    .then(() => { location.reload(); });
            } else {
                Swal.fire('Error', resp && resp.message ? resp.message : 'No se pudo añadir a la factura.', 'error');
            }
        }).catch(err => {
            console.error(err);
            Swal.fire('Error', 'Error de red al añadir a la factura.', 'error');
        });
    });
});
</script>
<script>
// Realtime: connect to SSE stream and update mesa cards status
(function () {
    function getToken() {
        try { return window.getApiAuthToken ? window.getApiAuthToken() : window.__AUTH_TOKEN__ || ''; } catch (e) { return window.__AUTH_TOKEN__ || ''; }
    }

    function updateMesaCard(mesaId, estatus) {
        if (!mesaId) return;
        const btn = document.querySelector('[data-mesa-id="' + mesaId + '"]');
        if (!btn) return;
        const card = btn.closest('.relative');
        if (!card) return;
        const meta = card.querySelector('.mesa-meta');
        if (!meta) return;

        const old = meta.querySelector('.mesa-status-badge');
        if (old) old.remove();

        const status = (estatus || '').toString().toLowerCase();
        if (status === 'pendiente') {
            const span = document.createElement('span');
            span.className = 'mesa-status-badge inline-flex items-center bg-amber-600 text-white text-[11px] px-3 py-1 rounded-full shadow-sm';
            span.textContent = 'Pendiente';
            meta.appendChild(span);
        } else if (status === 'pagado' || status === 'pagada') {
            const span = document.createElement('span');
            span.className = 'mesa-status-badge inline-flex items-center bg-emerald-600 text-white text-[11px] px-3 py-1 rounded-full shadow-sm';
            span.textContent = 'Disponible';
            meta.appendChild(span);
        } else {
            // Other statuses: show pending as default
            const span = document.createElement('span');
            span.className = 'mesa-status-badge inline-flex items-center bg-amber-600 text-white text-[11px] px-3 py-1 rounded-full shadow-sm';
            span.textContent = (status || 'Pendiente');
            meta.appendChild(span);
        }
    }

    function connectRealtime() {
        const token = getToken();
        if (!token) return;
        const params = new URLSearchParams();
        params.set('token', token);
        // no mesa_id so we receive all mesa events
        const url = '/api/realtime/stream?' + params.toString();

        try {
            const es = new EventSource(url);

            es.onopen = function () { console.log('Realtime connected'); };
            es.onerror = function (e) { console.warn('Realtime error', e); es.close(); setTimeout(connectRealtime, 2000); };

            // listen to named events (server emits event: <event_key>)
            const eventsToHandle = ['mesa.factura.created', 'mesa.factura.updated', 'factura.pagada', 'factura.creada'];
            eventsToHandle.forEach(evName => {
                es.addEventListener(evName, function (ev) {
                    try {
                        const data = JSON.parse(ev.data || '{}');
                        const mesaId = data.mesa_id || (data.payload && data.payload.mesa_id) || null;
                        const estatus = data.estatus || (data.payload && data.payload.estatus) || null;
                        if (mesaId) updateMesaCard(mesaId, estatus);
                    } catch (err) { console.error('Realtime parse error', err); }
                });
            });

            // fallback: generic message handler in case server uses default message events
            es.onmessage = function (ev) {
                try {
                    const data = JSON.parse(ev.data || '{}');
                    const mesaId = data.mesa_id || (data.payload && data.payload.mesa_id) || null;
                    const estatus = data.estatus || (data.payload && data.payload.estatus) || null;
                    if (mesaId) updateMesaCard(mesaId, estatus);
                } catch (err) { /* ignore */ }
            };
        } catch (e) {
            console.error('Realtime not supported', e);
        }
    }

    // start connection after DOM ready
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        connectRealtime();
    } else {
        document.addEventListener('DOMContentLoaded', connectRealtime);
    }
})();
</script>
@endpush
@endsection
