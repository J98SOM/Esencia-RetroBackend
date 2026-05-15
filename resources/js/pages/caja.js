/* JS for Caja view: dynamic rows, totals, payments and save */

document.addEventListener('DOMContentLoaded', () => {
    const preload = window.CAJA_PRELOAD || {};
    const addBtn = document.getElementById('add-row');
    const body = document.getElementById('caja-body');
    const totalEl = document.getElementById('caja-total');
    const vueltasEl = document.getElementById('caja-vueltas');
    const recibidoEl = document.getElementById('caja-recibido');
    const pEfectivo = document.getElementById('p-efectivo');
    const pTarjeta = document.getElementById('p-tarjeta');
    const pQr = document.getElementById('p-qr');
    const guardarBtn = document.getElementById('btn-guardar-caja');

    // Autocomplete dropdown element (shared)
    const dd = document.createElement('div');
    dd.className = 'caja-dd';
    dd.style.display = 'none';
    document.body.appendChild(dd);

    function debounce(fn, wait = 250) {
        let t;
        return function (...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), wait);
        };
    }

    async function searchProducts(q) {
        if (!q || q.trim().length === 0) return [];
        try {
            const res = await fetch(`/api/productos?q=${encodeURIComponent(q)}`, { credentials: 'same-origin' });
            if (!res.ok) return [];
            return await res.json();
        } catch (e) {
            console.error('searchProducts error', e);
            return [];
        }
    }

    function showDropdownForInput(input, items, onSelect) {
        if (!items || items.length === 0) { dd.style.display = 'none'; return; }
        dd.innerHTML = '';
        items.forEach(it => {
            const div = document.createElement('div');
            div.className = 'item';
            div.textContent = `${it.id} — ${it.nombre} — ${it.precio}`;
            div.addEventListener('mousedown', (e) => {
                e.preventDefault();
                onSelect(it);
                dd.style.display = 'none';
            });
            dd.appendChild(div);
        });
        const rect = input.getBoundingClientRect();
        dd.style.minWidth = rect.width + 'px';
        dd.style.left = rect.left + window.scrollX + 'px';
        dd.style.top = rect.bottom + window.scrollY + 'px';
        dd.style.display = 'block';
    }

    function hideDropdown() { dd.style.display = 'none'; }

    // hide dropdown on outside click
    document.addEventListener('click', (e) => {
        if (!dd.contains(e.target)) hideDropdown();
    });

    // Adjust table wrapper max-height so page doesn't scroll — table scrolls instead
    function adjustTableHeight() {
        const wrap = document.querySelector('.caja-table-wrap');
        if (!wrap) return;
        const rect = wrap.getBoundingClientRect();
        const spaceBelow = window.innerHeight - rect.top - 24; // leave small gap
        if (spaceBelow > 120) {
            wrap.style.maxHeight = spaceBelow + 'px';
            wrap.style.overflowY = 'auto';
        } else {
            wrap.style.maxHeight = '200px';
            wrap.style.overflowY = 'auto';
        }
    }

    window.addEventListener('resize', debounce(() => adjustTableHeight(), 150));

    function formatCOP(n) { return '$' + Number(n || 0).toLocaleString('es-CO', {minimumFractionDigits:0}); }

    function recalc() {
        let total = 0;
        body.querySelectorAll('tr').forEach(tr => {
            const qty = parseFloat(tr.querySelector('.caja-cant').value) || 0;
            const priceEl = tr.querySelector('.caja-price');
            const price = parseFloat(priceEl?.dataset?.price) || 0;
            const sub = qty * price;
            tr.querySelector('.caja-sub').textContent = formatCOP(sub);
            total += sub;
        });
        totalEl.textContent = formatCOP(total);

        // sum fixed payment inputs
        const paid = (parseFloat(pEfectivo?.value) || 0) + (parseFloat(pTarjeta?.value) || 0) + (parseFloat(pQr?.value) || 0);
        // 'Total recibido' is the sum of payments
        if (recibidoEl) recibidoEl.textContent = formatCOP(paid);
        // Mostrar la resta: total recibido - total (puede ser negativo)
        const diff = paid - total;
        vueltasEl.textContent = formatCOP(diff);
    }

    function addRow(data = {}) {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input class="caja-id bg-transparent text-sm" value="${data.id ?? ''}"></td>
            <td><input class="caja-name w-full bg-transparent text-sm" value="${data.name ?? ''}"></td>
            <td><input type="number" min="0" step="1" class="caja-cant" value="${data.qty ?? 1}"></td>
            <td><div class="caja-price" data-price="${data.price ?? 0}">${formatCOP(data.price ?? 0)}</div></td>
            <td class="caja-sub">$0</td>
            <td><input type="button" class="remove-row" value="Quitar"></td>
        `;
        body.appendChild(tr);
        attachRowHandlers(tr);
        // adjust table height after adding a row
        adjustTableHeight();
    }

    function attachRowHandlers(tr) {
        if (!tr) return;
        // avoid attaching twice
        if (tr.__handlersAttached) return;
        tr.__handlersAttached = true;

        // input handler for row: recalc totals and auto-add next row when last row is filled
        function onRowInput() {
            recalc();
            // if this is the last row and has meaningful content, append a new empty row
            const rows = Array.from(body.querySelectorAll('tr'));
            const last = rows[rows.length - 1];
            if (last === tr) {
                const idVal = tr.querySelector('.caja-id')?.value?.trim();
                const nameVal = tr.querySelector('.caja-name')?.value?.trim();
                const qtyVal = parseFloat(tr.querySelector('.caja-cant')?.value) || 0;
                const priceVal = parseFloat(tr.querySelector('.caja-price')?.dataset?.price) || 0;
                const filled = Boolean(idVal || nameVal || qtyVal > 0 || priceVal > 0);
                if (filled && rows.length < 100) { // cap to avoid runaway
                    addRow();
                    updateRemoveButtons();
                }
            }
        }

        const inputs = Array.from(tr.querySelectorAll('input'));
        inputs.forEach(inp => {
            inp.addEventListener('input', onRowInput);
            inp.addEventListener('focus', () => {
                // if focused row is the last row, add a new empty row so user can continue entering
                const rows = Array.from(body.querySelectorAll('tr'));
                const last = rows[rows.length - 1];
                if (last === tr && rows.length < 100) {
                    addRow();
                    updateRemoveButtons();
                }
            });
        });

        // autocomplete for ID and Name
        const idInput = tr.querySelector('.caja-id');
        const nameInput = tr.querySelector('.caja-name');

        const debouncedSearch = debounce(async (q, inputEl) => {
            if (!q || q.trim().length === 0) { hideDropdown(); return; }
            const items = await searchProducts(q);
            showDropdownForInput(inputEl, items, (it) => {
                if (idInput) idInput.value = it.id;
                if (nameInput) nameInput.value = it.nombre;
                const priceEl = tr.querySelector('.caja-price');
                if (priceEl && (it.precio !== undefined)) {
                    priceEl.dataset.price = it.precio;
                    priceEl.textContent = formatCOP(it.precio);
                }
                recalc();
            });
        }, 220);

        // ID: fetch exact product by id (ID is fixed)
        if (idInput) {
            idInput.addEventListener('change', async () => {
                const id = idInput.value?.toString().trim();
                if (!id) return;
                try {
                    const res = await fetch(`/api/productos/${encodeURIComponent(id)}`, { credentials: 'same-origin' });
                    if (!res.ok) return;
                    const p = await res.json();
                    if (p) {
                        if (nameInput) nameInput.value = p.nombre ?? '';
                        const priceEl = tr.querySelector('.caja-price');
                        if (priceEl && (p.precio !== undefined)) {
                            priceEl.dataset.price = p.precio;
                            priceEl.textContent = formatCOP(p.precio);
                        }
                        recalc();
                    }
                } catch (e) { console.error('Error fetching product by id', e); }
            });
            idInput.addEventListener('keydown', (e) => { if (e.key === 'Escape') hideDropdown(); });
        }
        // Name: provide autocomplete dropdown
        if (nameInput) {
            nameInput.addEventListener('input', (e) => debouncedSearch(e.target.value, nameInput));
            nameInput.addEventListener('keydown', (e) => { if (e.key === 'Escape') hideDropdown(); });
        }

        // (autocomplete handlers defined below) avoid duplicate idInput declaration

        const removeBtn = tr.querySelector('.remove-row');
        if (removeBtn) removeBtn.addEventListener('click', () => { tr.remove(); recalc(); updateRemoveButtons(); adjustTableHeight(); });
        updateRemoveButtons();
        recalc();
        return tr;
    }

    function updateRemoveButtons() {
        const rows = Array.from(body.querySelectorAll('tr'));
        rows.forEach((r, idx) => {
            const btn = r.querySelector('.remove-row');
            if (!btn) return;
            if (idx === 0) btn.classList.add('hidden'); else btn.classList.remove('hidden');
        });
    }

    addBtn.addEventListener('click', (e) => { e.preventDefault(); addRow(); });

    // no dynamic payment rows: using fixed inputs (efectivo, tarjeta, qr)

    guardarBtn.addEventListener('click', async () => {
        // Build invoice payload
        const items = [];
        body.querySelectorAll('tr').forEach(tr => {
            const id = tr.querySelector('.caja-id').value || null;
            const name = tr.querySelector('.caja-name').value || '';
            const qty = parseFloat(tr.querySelector('.caja-cant').value) || 0;
            const price = parseFloat(tr.querySelector('.caja-price')?.dataset?.price) || 0;
            if ((name || id) && qty > 0 && price >= 0) items.push({ producto_id: id, desc: name, cant: qty, precio: price });
        });
        if (items.length === 0) { alert('Añade al menos un producto con cantidad válida'); return; }

        const total = items.reduce((s,it)=> s + (it.cant * it.precio), 0);

        const invoice = {
            fecha: new Date().toISOString().split('T')[0],
            cliente: { nombre: 'Caja', nit: null },
            empresa: (window.COMPANY || {}),
            numero_orden: (document.getElementById('caja-factura-no')?.value) || (window.NEXT_INVOICE_NO || null),
            tipo: 'pos',
            mesa_id: preload.mesa_id || null,
            factura_id: preload.factura_id || null,
            items: items,
            medio_pago: null,
            metodos: [],
            total: total,
            observaciones: 'Venta en caja'
        };

        // collect fixed payment methods
        const efectivo = parseFloat(pEfectivo?.value) || 0;
        const tarjeta = parseFloat(pTarjeta?.value) || 0;
        const qr = parseFloat(pQr?.value) || 0;
        if (efectivo > 0) invoice.metodos.push({ metodo: 'efectivo', valor: efectivo });
        if (tarjeta > 0) invoice.metodos.push({ metodo: 'tarjeta', valor: tarjeta });
        if (qr > 0) invoice.metodos.push({ metodo: 'qr', valor: qr });

        // send to dedicated storeCaja endpoint
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        try {
            guardarBtn.disabled = true;
            const res = await fetch('/alquiler/caja', {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf || '' }, body: JSON.stringify({ invoice })
            });
            // parse JSON safely: server may return empty or non-JSON body
            let data = {};
            try {
                const txt = await res.text();
                data = txt ? JSON.parse(txt) : {};
            } catch (err) {
                data = { message: 'Respuesta inválida del servidor' };
            }
            if (res.ok) {
                // prefer server-provided numero_orden if present
                const serverNo = (data && data.numero_orden) ? String(data.numero_orden) : (invoice.numero_orden || window.NEXT_INVOICE_NO || '');
                if (serverNo) {
                    const inp = document.getElementById('caja-factura-no'); if (inp) inp.value = serverNo;
                    // advance NEXT_INVOICE_NO
                    const parsed = parseInt(serverNo.replace(/\D/g, ''), 10);
                    if (!isNaN(parsed)) window.NEXT_INVOICE_NO = String(parsed + 1);
                }
                alert(data.message || 'Guardado');
                // open print preview with authoritative numero_orden
                try {
                    const printedInvoice = Object.assign({}, invoice, { numero_orden: serverNo });
                    const html = buildTicketHtml(printedInvoice);
                    openPrintWindow(html);
                } catch (e) { console.warn('No se pudo abrir ticket automáticamente', e); }
                // redirect shortly after allowing print to open
                setTimeout(() => { window.location.href = data.redirect || '/alquiler/list'; }, 1200);
            } else {
                alert(data.message || 'Error al guardar');
            }
        } catch (e) { console.error(e); alert('Error de red'); }
        finally { guardarBtn.disabled = false; }
    });

    // start with 1 product row; additional rows are added automatically when the last row is filled
    addRow();

    // attach handlers to any server-rendered rows (first row rendered in Blade)
    document.querySelectorAll('#caja-body tr').forEach(tr => attachRowHandlers(tr));

    // attach input listeners to fixed payment inputs
    [pEfectivo, pTarjeta, pQr].forEach(el => el && el.addEventListener('input', recalc));

        // Build printable ticket HTML and open print window
        function buildTicketHtml(invoice) {
                const itemsHtml = invoice.items.map(it => {
                        return `<tr>
                                <td style="padding:4px 0;">${it.cant} x ${escapeHtml(it.desc)}</td>
                                <td style="text-align:right;">${formatCOP(it.precio)}</td>
                                <td style="text-align:right;">${formatCOP(it.cant * it.precio)}</td>
                        </tr>`;
                }).join('');

                const metodosHtml = (invoice.metodos || []).map(m => `<div style="display:flex;justify-content:space-between;padding:2px 0"><div>${escapeHtml(m.metodo)}</div><div>${formatCOP(m.valor)}</div></div>`).join('');

                const html = `<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Ticket - ${invoice.fecha}</title>
<style>
body{font-family: Arial, Helvetica, sans-serif; font-size:12px; color:#000}
.ticket{width:320px; margin:0 auto}
.center{text-align:center}
.logo{width:64px;height:64px;margin:0 auto}
table{width:100%;border-collapse:collapse}
td{vertical-align:top}
.hr{border-top:1px dashed #000;margin:6px 0}
</style>
</head>
<body>
<div class="ticket">
                <div class="center">
            <img src="/img/logo.png" alt="logo" style="width:80px;height:auto;margin-bottom:6px">
    <div style="font-weight:700">${escapeHtml((invoice.empresa && invoice.empresa.name) || 'ESENCIA RETRO')}</div>
    <div style="font-size:11px">Factura POS</div>
    <div style="font-size:11px">${invoice.fecha} ${invoice.numero_orden ? (' - No. ' + escapeHtml(invoice.numero_orden)) : ''}</div>
    <div style="font-size:11px;margin-top:6px">NIT: ${escapeHtml((invoice.empresa && invoice.empresa.nit) || '')}</div>
        <div style="font-size:11px">${escapeHtml((invoice.empresa && invoice.empresa.address) || '')}</div>
        <div style="font-size:11px">Tel: ${escapeHtml((invoice.empresa && invoice.empresa.phone) || '')}</div>
        <div style="font-size:11px">${escapeHtml((invoice.empresa && invoice.empresa.email) || '')}</div>
    </div>
    <div class="hr"></div>
    <table>
        <thead>
            <tr><th style="text-align:left">Item</th><th style="text-align:right">P.U.</th><th style="text-align:right">Total</th></tr>
        </thead>
        <tbody>
        ${itemsHtml}
        </tbody>
    </table>
    <div class="hr"></div>
    <div style="display:flex;justify-content:space-between;font-weight:700">
        <div>Total</div><div>${formatCOP(invoice.total)}</div>
    </div>
    <div style="margin-top:6px">${metodosHtml}</div>
    <div class="hr"></div>
    <div style="display:flex;justify-content:space-between">
        <div>Recibido</div><div>${formatCOP((invoice.metodos||[]).reduce((s,m)=>s+(m.valor||0),0))}</div>
    </div>
    <div style="display:flex;justify-content:space-between">
        <div>Vueltas</div><div>${formatCOP(((invoice.metodos||[]).reduce((s,m)=>s+(m.valor||0),0) - invoice.total))}</div>
    </div>
    <div style="height:12px"></div>
    <div class="center" style="font-size:11px">Gracias por su compra</div>
</div>
</body>
</html>`;
                return html;
        }

        function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }

        function openPrintWindow(html) {
                const w = window.open('', '_blank', 'width=400,height=700');
                if (!w) { alert('Popup bloqueado. Permite ventanas emergentes para imprimir.'); return; }
                w.document.open();
                w.document.write(html);
                w.document.close();
                // wait for images to load before print
                w.onload = function(){
                        setTimeout(()=>{ w.focus(); w.print(); }, 300);
                };
        }

        // Print ticket handler
        const printBtn = document.getElementById('btn-imprimir-ticket');
        if (printBtn) printBtn.addEventListener('click', () => {
                // Build current invoice snapshot
                const items = [];
                body.querySelectorAll('tr').forEach(tr => {
                        const id = tr.querySelector('.caja-id')?.value || null;
                        const desc = tr.querySelector('.caja-name')?.value || '';
                        const qty = parseFloat(tr.querySelector('.caja-cant')?.value) || 0;
                        const price = parseFloat(tr.querySelector('.caja-price')?.dataset?.price) || 0;
                        if ((desc || id) && qty > 0) items.push({ producto_id: id, desc: desc, cant: qty, precio: price });
                });
                const total = items.reduce((s,it)=> s + (it.cant * it.precio), 0);
                const metodos = [];
                const efectivo = parseFloat(pEfectivo?.value) || 0; if (efectivo>0) metodos.push({metodo:'efectivo',valor:efectivo});
                const tarjeta = parseFloat(pTarjeta?.value) || 0; if (tarjeta>0) metodos.push({metodo:'tarjeta',valor:tarjeta});
                const qr = parseFloat(pQr?.value) || 0; if (qr>0) metodos.push({metodo:'qr',valor:qr});

                const invoice = { fecha: new Date().toLocaleString(), items, total, metodos, empresa: (window.COMPANY || {}), numero_orden: (document.getElementById('caja-factura-no')?.value) || (window.NEXT_INVOICE_NO || null), tipo: 'pos' };
                const html = buildTicketHtml(invoice);
                openPrintWindow(html);
        });
});
