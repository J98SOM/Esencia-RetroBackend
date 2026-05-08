/* Public copy: same behavior as resources/js/pages/alquiler.js but defensive */

const MAX_ROWS = 15;
let activeRows = 5;

document.addEventListener('DOMContentLoaded', () => {
    try {
        const today = new Date().toISOString().split('T')[0];
        const fechaEl = document.getElementById('factura-fecha');
        if (fechaEl && (!fechaEl.value || fechaEl.value === '')) fechaEl.value = today;

        const itemsSelector = document.getElementById('items-count-selector');
        if (itemsSelector) activeRows = parseInt(itemsSelector.value, 10) || activeRows;
        aplicarCantidadFilas(activeRows);

        const medioSelect = document.getElementById('medio-pago');
        const displayMedio = document.getElementById('display-medio-pago');
        if (medioSelect && displayMedio) {
            displayMedio.textContent = medioSelect.options[medioSelect.selectedIndex]?.text?.toUpperCase() || '';
            medioSelect.addEventListener('change', () => {
                displayMedio.textContent = medioSelect.options[medioSelect.selectedIndex]?.text?.toUpperCase() || '';
            });
        }
    } catch (e) {
        console.error('Init alquiler public JS error', e);
    }
});

function onCantidadItemsChange(selectEl) { activeRows = parseInt(selectEl.value, 10); aplicarCantidadFilas(activeRows); }

function aplicarCantidadFilas(n) {
    for (let i = 1; i <= MAX_ROWS; i++) {
        const row = document.querySelector(`[data-row="${i}"]`);
        if (!row) continue;
        if (i <= n) row.classList.remove('hidden-row'); else {
            row.classList.add('hidden-row');
            row.querySelectorAll('input').forEach(inp => inp.value = '');
            const brutoEl = document.getElementById(`bruto_${i}`);
            if (brutoEl) brutoEl.textContent = '$0';
        }
    }
    recalcularTotales();
}

function formatCOP(num) { if (!num || isNaN(num)) return '$0'; return '$' + parseFloat(num).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 }); }

function calcularFila(rowNum) {
    const row = document.querySelector(`[data-row="${rowNum}"]`);
    if (!row) return;
    const cant = parseFloat(row.querySelector('.item-cant').value) || 0;
    const precio = parseFloat(row.querySelector('.item-precio').value) || 0;
    const bruto = cant * precio; const brutoEl = document.getElementById(`bruto_${rowNum}`);
    if (brutoEl) brutoEl.textContent = formatCOP(bruto > 0 ? bruto : 0);
    recalcularTotales();
}

function recalcularTotales() {
    let totalBruto = 0; let itemsCount = 0;
    for (let i = 1; i <= activeRows; i++) {
        const row = document.querySelector(`[data-row="${i}"]`); if (!row || row.classList.contains('hidden-row')) continue;
        const cant = parseFloat(row.querySelector('.item-cant').value) || 0; const precio = parseFloat(row.querySelector('.item-precio').value) || 0;
        if (cant > 0 || precio > 0) itemsCount++; const bruto = cant * precio; totalBruto += bruto > 0 ? bruto : 0;
    }
    const totalItemsEl = document.getElementById('total-items-count'); const totalBrutoEl = document.getElementById('total-bruto');
    const totalPagarEl = document.getElementById('total-pagar'); const valorLetrasEl = document.getElementById('valor-letras');
    if (totalItemsEl) totalItemsEl.textContent = itemsCount; if (totalBrutoEl) totalBrutoEl.textContent = formatCOP(totalBruto);
    if (totalPagarEl) totalPagarEl.textContent = formatCOP(totalBruto); if (valorLetrasEl) valorLetrasEl.textContent = totalBruto > 0 ? numeroALetras(totalBruto) : '—';
}

function numeroALetras(num) { num = Math.floor(num); if (num === 0) return 'cero pesos';
    const millones  = ['','un millón','dos millones','tres millones','cuatro millones','cinco millones','seis millones','siete millones','ocho millones','nueve millones'];
    const miles     = ['','mil','dos mil','tres mil','cuatro mil','cinco mil','seis mil','siete mil','ocho mil','nueve mil','diez mil','once mil','doce mil','trece mil','catorce mil','quince mil','dieciséis mil','diecisiete mil','dieciocho mil','diecinueve mil'];
    const centenas  = ['','cien','doscientos','trescientos','cuatrocientos','quinientos','seiscientos','setecientos','ochocientos','novecientos'];
    const decenas   = ['','diez','veinte','treinta','cuarenta','cincuenta','sesenta','setenta','ochenta','noventa'];
    const unidades  = ['','uno','dos','tres','cuatro','cinco','seis','siete','ocho','nueve','diez','once','doce','trece','catorce','quince','dieciséis','diecisiete','dieciocho','diecinueve'];
    if (num >= 1000000) { const m = Math.floor(num/1000000); const r = num % 1000000; return millones[m] + (r>0? ' ' + numeroALetras(r).replace(' pesos','') : '') + ' pesos'; }
    if (num >= 1000) { const m = Math.floor(num/1000); const r = num % 1000; let t; if (m < 20) t = miles[m]; else if (m<100) t = decenas[Math.floor(m/10)] + (m%10>0? ' y ' + unidades[m%10] : '') + ' mil'; else t = centenas[Math.floor(m/100)] + ' ' + (m%100>0? numeroALetras(m%100).replace(' pesos','') + ' ' : '') + 'mil'; return t + (r>0? ' ' + numeroALetras(r).replace(' pesos','') : '') + ' pesos'; }
    if (num >= 100) return centenas[Math.floor(num/100)] + (num%100>0? ' ' + numeroALetras(num%100).replace(' pesos','') : '') + ' pesos';
    if (num >= 20) return decenas[Math.floor(num/10)] + (num%10>0? ' y ' + unidades[num%10] : '') + ' pesos';
    return unidades[num] + ' pesos';
}

function limpiarFormulario() { document.querySelectorAll('#items-tabla input').forEach(inp => inp.value = ''); for (let i=1;i<=MAX_ROWS;i++){ const el=document.getElementById(`bruto_${i}`); if(el) el.textContent='$0'; } ['cliente-nombre','cliente-nit','cliente-direccion','cliente-telefono','cliente-ciudad','observaciones','orden-compra'].forEach(id=>{const el=document.getElementById(id); if(el) el.value='';}); recalcularTotales(); }

// guardarFactura: builds invoice and chooses update/store based on factura-id or global flags
function guardarFactura() {
    const invoice = {
        no: document.getElementById('factura-no')?.value || '',
        fecha: document.getElementById('factura-fecha')?.value || '',
        cliente: {
            nombre: document.getElementById('cliente-nombre')?.value || '',
            nit: document.getElementById('cliente-nit')?.value || '',
            direccion: document.getElementById('cliente-direccion')?.value || '',
            telefono: document.getElementById('cliente-telefono')?.value || '',
            ciudad: document.getElementById('cliente-ciudad')?.value || '',
        },
        items: [],
        observaciones: document.getElementById('observaciones')?.value || '',
        total: 0,
        medio_pago: document.getElementById('medio-pago')?.value || '',
        orden_compra: document.getElementById('orden-compra')?.value || ''
    };

    const rows = document.querySelectorAll('#items-tabla tr.item-row');
    rows.forEach((row) => {
        if (row.classList.contains('hidden-row')) return;
        const desc = row.querySelector('.item-desc')?.value || '';
        const cant = parseFloat(row.querySelector('.item-cant')?.value) || 0;
        const precio = parseFloat(row.querySelector('.item-precio')?.value) || 0;
        if (!desc && cant === 0 && precio === 0) return;
        invoice.items.push({ desc: desc, cant: cant, precio: precio });
        invoice.total += cant * precio;
    });

    const facturaIdEl = document.getElementById('factura-id');
    const isEditing = (facturaIdEl && facturaIdEl.dataset.editing === '1') || window.ALQUILER_EDITING === true || !!window.ALQUILER_FACTURA_ID;
    if (isEditing) invoice.factura_id = facturaIdEl?.value || window.ALQUILER_FACTURA_ID || null;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const url = isEditing ? '/alquiler/update' : '/alquiler/store';
    fetch(url, {
        method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf || '' }, body: JSON.stringify({ invoice })
    }).then(r => r.json()).then(data => {
        const toast = document.createElement('div'); toast.className = 'alquiler-toast'; toast.innerHTML = '<span class="material-symbols-outlined" style="font-variation-settings:\'FILL\' 1;font-size:1.2rem">check_circle</span> ' + (data.message || 'Factura guardada'); document.body.appendChild(toast); setTimeout(()=>toast.remove(),2500); if (data.redirect) setTimeout(()=>{ window.location.href = data.redirect; },800);
    }).catch(err => { console.error('Error guardando factura', err); const toast = document.createElement('div'); toast.className='alquiler-toast'; toast.style.background='#ef4444'; toast.innerText='Error al guardar factura'; document.body.appendChild(toast); setTimeout(()=>toast.remove(),3000); });
}

function imprimirFactura(){ try{ const invoice = { no: document.getElementById('factura-no')?.value || '', fecha: document.getElementById('factura-fecha')?.value || '', cliente:{ nombre: document.getElementById('cliente-nombre')?.value || '', nit: document.getElementById('cliente-nit')?.value || '', direccion: document.getElementById('cliente-direccion')?.value || '', telefono: document.getElementById('cliente-telefono')?.value || '', ciudad: document.getElementById('cliente-ciudad')?.value || '' }, items: [], observaciones: document.getElementById('observaciones')?.value || '', total: 0 }; const rows = document.querySelectorAll('#items-tabla tr.item-row'); rows.forEach((row)=>{ if(row.classList.contains('hidden-row')) return; const desc = row.querySelector('.item-desc')?.value || ''; const cant = parseFloat(row.querySelector('.item-cant')?.value) || 0; const precio = parseFloat(row.querySelector('.item-precio')?.value) || 0; if(!desc && cant===0 && precio===0) return; invoice.items.push({ desc: desc, cant: cant, precio: precio }); invoice.total += cant * precio; }); invoice.medio_pago = document.getElementById('medio-pago')?.value || ''; invoice.orden_compra = document.getElementById('orden-compra')?.value || ''; const form = document.createElement('form'); form.method='POST'; form.action='/alquiler/print'; form.target='_blank'; const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'); if(csrf){ const csrfInput=document.createElement('input'); csrfInput.type='hidden'; csrfInput.name='_token'; csrfInput.value=csrf; form.appendChild(csrfInput);} const input=document.createElement('input'); input.type='hidden'; input.name='invoice'; input.value=JSON.stringify(invoice); form.appendChild(input); document.body.appendChild(form); form.submit(); form.remove(); }catch(e){ console.error('Error al preparar impresión:', e); window.print(); } }

window.onCantidadItemsChange = onCantidadItemsChange; window.calcularFila = calcularFila; window.limpiarFormulario = limpiarFormulario; window.guardarFactura = guardarFactura; window.imprimirFactura = imprimirFactura;
// Alias for edit button
window.editarFactura = function() { guardarFactura(); };
