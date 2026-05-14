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
    // Fecha de hoy (solo si el campo está vacío)
    const today = new Date().toISOString().split('T')[0];
    const fechaEl = document.getElementById('factura-fecha');
    if (fechaEl && (!fechaEl.value || fechaEl.value === '')) {
        fechaEl.value = today;
    }

    // Número de factura automático: el valor lo proporciona el servidor en la vista (readonly)

    // Leer selector de cantidad de filas (permite que el servidor prefije la cantidad al editar)
    const itemsSelector = document.getElementById('items-count-selector');
    if (itemsSelector) {
        activeRows = parseInt(itemsSelector.value, 10) || activeRows;
    }
    // Aplica la cantidad de filas
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
    // Attach product select handlers
    attachProductHandlers();
});

/** Attach change handlers to product select elements so selecting a product fills description and price */
function attachProductHandlers() {
    // Support legacy select elements (if any)
    const selects = document.querySelectorAll('.product-select');
    selects.forEach(sel => {
        if (sel._attached) return; sel._attached = true;
        sel.addEventListener('change', function(e){
            const rowNum = this.dataset.row;
            const opt = this.options[this.selectedIndex];
            const pid = this.value || '';
            const name = opt?.dataset?.name || opt?.text || '';
            const price = parseFloat(opt?.dataset?.price) || 0;
            const row = document.querySelector(`[data-row="${rowNum}"]`);
            if (!row) return;
            const descInput = row.querySelector('.item-desc');
            if (descInput && (!descInput.value || descInput.value.trim() === '')) {
                descInput.value = name;
            }
            const pidInput = row.querySelector('.item-product-id');
            if (pidInput) pidInput.value = pid;
            const priceInput = row.querySelector('.item-precio');
            if (priceInput) {
                priceInput.value = price > 0 ? price : '';
            }
            calcularFila(parseInt(rowNum,10));
        });
        if (sel.value) {
            const evt = new Event('change');
            sel.dispatchEvent(evt);
        }
    });

    // New: inputs with datalist for searchable product dropdown
    const dropdowns = document.querySelectorAll('.product-dropdown');
    // build a name->product map for fast lookup
    if (window.PRODUCTS_DATA && !window.PRODUCTS_MAP) {
        window.PRODUCTS_MAP = {};
        window.PRODUCTS_DATA.forEach(p => { window.PRODUCTS_MAP[p.nombre] = p; });
    }
    dropdowns.forEach(inp => {
        if (inp._attached) return; inp._attached = true;
        inp.addEventListener('input', function(e) {
            const name = (this.value || '').toString();
            const rowNum = this.dataset.row;
            selectProductByName(rowNum, name);
        });
        inp.addEventListener('change', function(e) {
            const name = (this.value || '').toString();
            const rowNum = this.dataset.row;
            selectProductByName(rowNum, name);
        });
        // If prefilled (editing), trigger selection
        if (inp.value && inp.value.trim() !== '') {
            selectProductByName(inp.dataset.row, inp.value.trim());
        }
    });
    // Attach description handlers to clear producto_id when user types a custom description
    attachDescriptionHandlers();
}

/** Find product by name and fill row fields (id, price, desc) */
function selectProductByName(rowNum, name) {
    if (!name) return;
    const prod = (window.PRODUCTS_MAP && window.PRODUCTS_MAP[name]) || (window.PRODUCTS_DATA && window.PRODUCTS_DATA.find(p => p.nombre === name));
    const row = document.querySelector(`[data-row="${rowNum}"]`);
    if (!row) return;
    const descInput = row.querySelector('.item-desc');
    const pidInput = row.querySelector('.item-product-id');
    const priceInput = row.querySelector('.item-precio');
    if (prod) {
        if (descInput && (!descInput.value || descInput.value.trim() === '')) descInput.value = prod.nombre;
        if (pidInput) pidInput.value = prod.id;
        if (priceInput) priceInput.value = prod.precio;
        // mark as product-derived description
        row._manualDesc = false;
    } else {
        // not found: clear product id and price but keep description
        if (pidInput) pidInput.value = '';
        row._manualDesc = false;
    }
    calcularFila(parseInt(rowNum,10));
}

// When user edits the description manually, clear producto_id so backend stores null
function attachDescriptionHandlers() {
    const descs = document.querySelectorAll('.item-desc');
    descs.forEach(inp => {
        if (inp._attachedDesc) return; inp._attachedDesc = true;
        inp.addEventListener('input', function(e){
            const row = this.closest('tr.item-row');
            if (!row) return;
            // mark manual editing and clear product id
            row._manualDesc = true;
            const pidInput = row.querySelector('.item-product-id');
            if (pidInput) pidInput.value = '';
        });
    });
}

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

    // Agregar mesa_id si fue provisto por la vista (crear factura desde una mesa)
    invoice.mesa_id = document.getElementById('mesa-id')?.value || null;

    const rows = document.querySelectorAll('#items-tabla tr.item-row');
    rows.forEach((row) => {
        if (row.classList.contains('hidden-row')) return;
        const desc = row.querySelector('.item-desc')?.value || '';
        const cant = parseFloat(row.querySelector('.item-cant')?.value) || 0;
        const precio = parseFloat(row.querySelector('.item-precio')?.value) || 0;
        if (!desc && cant === 0 && precio === 0) return;
        const pid = row.querySelector('.item-product-id')?.value || null;
        invoice.items.push({ desc: desc, cant: cant, precio: precio, producto_id: pid });
        invoice.total += cant * precio;
    });

    // Determine if editing: prefer hidden input, fallback to global flag
    const facturaIdEl = document.getElementById('factura-id');
    const isEditing = (facturaIdEl && facturaIdEl.dataset.editing === '1') || window.ALQUILER_EDITING === true || !!window.ALQUILER_FACTURA_ID;
    if (isEditing) {
        invoice.factura_id = facturaIdEl?.value || window.ALQUILER_FACTURA_ID || null;
    }

    // Validaciones completas antes de enviar: fecha, cliente, NIT, medio de pago y consistencia de ítems
    const errors = [];

    if (!invoice.fecha || invoice.fecha.trim() === '') {
        errors.push('Fecha de la factura (campo Fecha)');
    }

    if (!invoice.cliente || !invoice.cliente.nombre || invoice.cliente.nombre.trim() === '') {
        errors.push('Nombre del cliente');
    }

    if (!invoice.cliente || !invoice.cliente.nit || invoice.cliente.nit.trim() === '') {
        errors.push('NIT / Cédula del cliente');
    }

    if (!invoice.medio_pago || invoice.medio_pago.trim() === '') {
        errors.push('Medio de pago');
    }

    // Validar ítems: al menos uno completo, y cada ítem parcial debe estar completo
    let validItemCount = 0;
    invoice.items.forEach((it, idx) => {
        const desc = (it.desc || '').toString().trim();
        const cant = parseFloat(it.cant) || 0;
        const precio = parseFloat(it.precio) || 0;

        if (desc === '' && cant === 0 && precio === 0) {
            return; // fila vacía, ignora
        }

        // Si hay algún valor, todos deben estar presentes y válidos
        if (desc === '' || cant <= 0 || precio <= 0) {
            errors.push(`Ítem ${idx + 1}: completar descripción, cantidad (>0) y precio (>0)`);
        } else {
            validItemCount++;
        }
    });

    if (validItemCount === 0) {
        errors.push('Agregar al menos un ítem con descripción, cantidad y precio válidos');
    }

    if (errors.length > 0) {
        alert('Corrige los siguientes campos antes de guardar:\n\n- ' + errors.join('\n- '));
        // enfocar el primer campo inválido razonable
        if (errors[0].toLowerCase().includes('fecha')) document.getElementById('factura-fecha')?.focus();
        else if (errors[0].toLowerCase().includes('nombre')) document.getElementById('cliente-nombre')?.focus();
        else if (errors[0].toLowerCase().includes('nit')) document.getElementById('cliente-nit')?.focus();
        else if (errors[0].toLowerCase().includes('medio de pago')) document.getElementById('medio-pago')?.focus();
        else {
            // intentar enfocar primer item con problema
            const firstProblem = document.querySelector('#items-tabla tr.item-row');
            firstProblem?.querySelector('.item-desc')?.focus();
        }
        return;
    }

    // Confirmación del usuario
    const confirmMsg = isEditing ? '¿Desea actualizar esta factura?' : '¿Desea crear la factura de alquiler?';
    if (!window.confirm(confirmMsg)) {
        return;
    }
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const url = isEditing ? '/alquiler/update' : '/alquiler/store';
    fetch(url, {
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

                // Preferir redirect provisto por el backend, si no viene y no es edición, enviar al listado
                const redirectUrl = data.redirect || (!isEditing ? '/alquiler/list' : null);
                if (redirectUrl) {
                        setTimeout(() => { window.location.href = redirectUrl; }, 800);
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
// Alias para botón 'Editar' que reutiliza la misma lógica (frontend detecta modo edición)
window.editarFactura = function() { guardarFactura(); };
