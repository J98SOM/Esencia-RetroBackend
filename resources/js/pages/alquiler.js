/* ============================================================
   Alquiler de Salón — Lógica JavaScript dedicada (moved to pages folder)
   ============================================================ */

/** Número máximo de filas disponibles en la tabla */
const MAX_ROWS = 15;

/** Número de filas actualmente visibles */
let activeRows = 5;

// ----------------------------------------------------------------
// Inicialización
// ----------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    // Fecha de hoy
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('factura-fecha').value = today;

    // Número de factura automático: el valor lo proporciona el servidor en la vista (readonly)

    // Aplica la cantidad de filas por defecto
    aplicarCantidadFilas(activeRows);

    // Inicializar display del medio de pago si existe
    const medioSelect = document.getElementById('medio-pago');
    const displayMedio = document.getElementById('display-medio-pago');
    if (medioSelect && displayMedio) {
        // mostrar texto de la opción seleccionada
        displayMedio.textContent = medioSelect.options[medioSelect.selectedIndex]?.text?.toUpperCase() || '';
        medioSelect.addEventListener('change', () => {
            displayMedio.textContent = medioSelect.options[medioSelect.selectedIndex]?.text?.toUpperCase() || '';
        });
    }
});

// ----------------------------------------------------------------
// Control de cantidad de ítems
// ----------------------------------------------------------------

/**
 * Llamado cuando cambia el selector de cantidad de ítems.
 * @param {HTMLSelectElement} selectEl
 */
function onCantidadItemsChange(selectEl) {
    activeRows = parseInt(selectEl.value, 10);
    aplicarCantidadFilas(activeRows);
}

/**
 * Muestra las primeras `n` filas y oculta el resto.
 * @param {number} n
 */
function aplicarCantidadFilas(n) {
    for (let i = 1; i <= MAX_ROWS; i++) {
        const row = document.querySelector(`[data-row="${i}"]`);
        if (!row) continue;
        if (i <= n) {
            row.classList.remove('hidden-row');
        } else {
            row.classList.add('hidden-row');
            // Limpia valores de filas ocultas para no afectar totales
            row.querySelectorAll('input').forEach(inp => inp.value = '');
            const brutoEl = document.getElementById(`bruto_${i}`);
            if (brutoEl) brutoEl.textContent = '$0';
        }
    }
    recalcularTotales();
}

// ----------------------------------------------------------------
// Cálculos
// ----------------------------------------------------------------

/** Formatea un número como moneda COP */
function formatCOP(num) {
    if (!num || isNaN(num)) return '$0';
    return '$' + parseFloat(num).toLocaleString('es-CO', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
}

/**
 * Recalcula el Vr. Bruto de una fila específica.
 * Fórmula: cant × precio = bruto
 * @param {number} rowNum
 */
function calcularFila(rowNum) {
    const row = document.querySelector(`[data-row="${rowNum}"]`);
    if (!row) return;

    const cant   = parseFloat(row.querySelector('.item-cant').value)   || 0;
    const precio = parseFloat(row.querySelector('.item-precio').value) || 0;

    const bruto = cant * precio;
    const brutoEl = document.getElementById(`bruto_${rowNum}`);
    if (brutoEl) brutoEl.textContent = formatCOP(bruto > 0 ? bruto : 0);

    recalcularTotales();
}

/** Recalcula los totales generales del formulario */
function recalcularTotales() {
    let totalBruto  = 0;
    let itemsCount  = 0;

    for (let i = 1; i <= activeRows; i++) {
        const row = document.querySelector(`[data-row="${i}"]`);
        if (!row || row.classList.contains('hidden-row')) continue;

        const cant   = parseFloat(row.querySelector('.item-cant').value)   || 0;
        const precio = parseFloat(row.querySelector('.item-precio').value) || 0;

        if (cant > 0 || precio > 0) itemsCount++;

        const bruto = cant * precio;
        totalBruto += bruto > 0 ? bruto : 0;
    }

    document.getElementById('total-items-count').textContent = itemsCount;
    document.getElementById('total-bruto').textContent       = formatCOP(totalBruto);
    document.getElementById('total-pagar').textContent       = formatCOP(totalBruto);
    document.getElementById('valor-letras').textContent      = totalBruto > 0
        ? numeroALetras(totalBruto)
        : '—';
}

// ----------------------------------------------------------------
// Número a Letras (Español colombiano)
// ----------------------------------------------------------------

function numeroALetras(num) {
    const millones  = ['','un millón','dos millones','tres millones','cuatro millones','cinco millones',
                       'seis millones','siete millones','ocho millones','nueve millones'];
    const miles     = ['','mil','dos mil','tres mil','cuatro mil','cinco mil','seis mil','siete mil',
                       'ocho mil','nueve mil','diez mil','once mil','doce mil','trece mil','catorce mil',
                       'quince mil','dieciséis mil','diecisiete mil','dieciocho mil','diecinueve mil'];
    const centenas  = ['','cien','doscientos','trescientos','cuatrocientos','quinientos',
                       'seiscientos','setecientos','ochocientos','novecientos'];
    const decenas   = ['','diez','veinte','treinta','cuarenta','cincuenta','sesenta','setenta','ochenta','noventa'];
    const unidades  = ['','uno','dos','tres','cuatro','cinco','seis','siete','ocho','nueve',
                       'diez','once','doce','trece','catorce','quince','dieciséis','diecisiete',
                       'dieciocho','diecinueve'];

    num = Math.floor(num);
    if (num === 0) return 'cero pesos';
    if (num >= 1000000) {
        const m = Math.floor(num / 1000000);
        const r = num % 1000000;
        return millones[m] + (r > 0 ? ' ' + numeroALetras(r).replace(' pesos', '') : '') + ' pesos';
    }
    if (num >= 1000) {
        const m = Math.floor(num / 1000);
        const r = num % 1000;
        let t;
        if (m < 20)       t = miles[m];
        else if (m < 100) t = decenas[Math.floor(m / 10)] + (m % 10 > 0 ? ' y ' + unidades[m % 10] : '') + ' mil';
        else              t = centenas[Math.floor(m / 100)] + ' ' + (m % 100 > 0 ? numeroALetras(m % 100).replace(' pesos', '') + ' ' : '') + 'mil';
        return t + (r > 0 ? ' ' + numeroALetras(r).replace(' pesos', '') : '') + ' pesos';
    }
    if (num >= 100) {
        return centenas[Math.floor(num / 100)]
            + (num % 100 > 0 ? ' ' + numeroALetras(num % 100).replace(' pesos', '') : '')
            + ' pesos';
    }
    if (num >= 20) {
        return decenas[Math.floor(num / 10)]
            + (num % 10 > 0 ? ' y ' + unidades[num % 10] : '')
            + ' pesos';
    }
    return unidades[num] + ' pesos';
}

// ----------------------------------------------------------------
// Acciones del formulario
// ----------------------------------------------------------------

/** Limpia todos los campos del formulario */
function limpiarFormulario() {
    document.querySelectorAll('#items-tabla input').forEach(inp => inp.value = '');
    for (let i = 1; i <= MAX_ROWS; i++) {
        const el = document.getElementById(`bruto_${i}`);
        if (el) el.textContent = '$0';
    }

    ['cliente-nombre', 'cliente-nit', 'cliente-direccion',
     'cliente-telefono', 'cliente-ciudad', 'observaciones', 'orden-compra']
        .forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });

    recalcularTotales();
}

/** Muestra un toast de confirmación al guardar */
function guardarFactura() {
    // Construir el objeto invoice (mismo formato que para imprimir)
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

    // Enviar por fetch al backend para persistir (tipo forzado a 'evento' por backend)
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    fetch('/alquiler/store', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrf || ''
        },
        body: JSON.stringify({ invoice })
    }).then(r => r.json())
      .then(data => {
        const toast = document.createElement('div');
        toast.className = 'alquiler-toast';
        toast.innerHTML = '<span class="material-symbols-outlined" style="font-variation-settings:\'FILL\' 1;font-size:1.2rem">check_circle</span> ' + (data.message || 'Factura guardada');
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2500);
        if (data.redirect) {
            setTimeout(() => { window.location.href = data.redirect; }, 800);
        }
    }).catch(err => {
        console.error('Error guardando factura', err);
        const toast = document.createElement('div');
        toast.className = 'alquiler-toast';
        toast.style.background = '#ef4444';
        toast.innerText = 'Error al guardar factura';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    });
}

/** Lanza el diálogo de impresión del navegador */
function imprimirFactura() {
    try {
        // Construir el objeto invoice a enviar al endpoint de impresión
        const invoice = {
            company: {
                name: document.querySelector('#factura-alquiler .company-name')?.textContent || 'ESENCIA RETRO',
                nit: '1,007,450,540',
                tel: '3162218491',
                city: 'Bogotá',
                email: 'esenciaretro10@gmail.com'
            },
            no: document.getElementById('factura-no')?.value || '',
            fecha: document.getElementById('factura-fecha')?.value || '',
            // 'vence' was removed from the UI; don't include it
            cliente: {
                nombre: document.getElementById('cliente-nombre')?.value || '',
                nit: document.getElementById('cliente-nit')?.value || '',
                direccion: document.getElementById('cliente-direccion')?.value || '',
                telefono: document.getElementById('cliente-telefono')?.value || '',
                ciudad: document.getElementById('cliente-ciudad')?.value || '',
            },
            items: [],
            observaciones: document.getElementById('observaciones')?.value || '',
            total: 0
        };

        // Recolectar filas visibles
        const rows = document.querySelectorAll('#items-tabla tr.item-row');
        rows.forEach((row, idx) => {
            if (row.classList.contains('hidden-row')) return;
            const desc = row.querySelector('.item-desc')?.value || '';
            const cant = parseFloat(row.querySelector('.item-cant')?.value) || 0;
            const precio = parseFloat(row.querySelector('.item-precio')?.value) || 0;
            if (!desc && cant === 0 && precio === 0) return;
            invoice.items.push({ desc: desc, cant: cant, precio: precio });
            invoice.total += cant * precio;
        });

        // Añadir medio de pago y orden de compra
        invoice.medio_pago = document.getElementById('medio-pago')?.value || '';
        invoice.orden_compra = document.getElementById('orden-compra')?.value || '';

        // Crear form y enviarlo en POST a la ruta de impresión, abriendo en nueva pestaña
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/alquiler/print';
        form.target = '_blank';

        // CSRF token desde meta tag
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrf) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrf;
            form.appendChild(csrfInput);
        }

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'invoice';
        input.value = JSON.stringify(invoice);
        form.appendChild(input);

        document.body.appendChild(form);
        form.submit();
        form.remove();
    } catch (e) {
        console.error('Error al preparar impresión:', e);
        window.print();
    }
}

// --- EXPORTAR FUNCIONES AL OBJETO GLOBAL WINDOW ---
window.onCantidadItemsChange = onCantidadItemsChange;
window.calcularFila = calcularFila;
window.limpiarFormulario = limpiarFormulario;
window.guardarFactura = guardarFactura;
window.imprimirFactura = imprimirFactura;
