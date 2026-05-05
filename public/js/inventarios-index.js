// Note: Using public/js path to match existing assets structure
const inventariosApi = '/api/inventarios';

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function showAlert(message, type = 'success') {
    const alerts = document.getElementById('alerts');
    alerts.innerHTML = `<div class="p-4 rounded-lg border">${message}</div>`;
    setTimeout(() => alerts.innerHTML = '', 3000);
}

let productos = [];

async function loadProductos() {
    try {
        const res = await fetch('/api/productos', { credentials: 'same-origin' });
        if (!res.ok) return;
        productos = await res.json();
        // only render dropdown if element exists on the page
        if (document.getElementById('productos-dropdown')) {
            renderProductosDropdown(productos);
        }
    } catch (err) {
        console.error('Failed to load productos', err);
    }
}

function renderProductosDropdown(list) {
    const dropdown = document.getElementById('productos-dropdown');
    if (!dropdown) return;
    dropdown.innerHTML = '';
    // enable scrollbar only when list is longer than 3
    if (list && list.length > 3) {
        dropdown.classList.add('max-h-40', 'overflow-y-auto');
    } else {
        dropdown.classList.remove('max-h-40', 'overflow-y-auto');
    }
    list.forEach(p => {
        const item = document.createElement('div');
        item.className = 'px-3 py-2 hover:bg-surface-container-highest cursor-pointer text-white';
        item.textContent = p.nombre;
        item.dataset.id = p.id;
        item.addEventListener('click', () => {
            document.getElementById('inventario-producto-name').value = p.nombre;
            document.getElementById('inventario-producto-id').value = p.id;
            dropdown.classList.add('hidden');
        });
        dropdown.appendChild(item);
    });
    // show dropdown after rendering
    if (list && list.length > 0) {
        dropdown.classList.remove('hidden');
    } else {
        dropdown.classList.add('hidden');
    }
}

async function loadInventarios() {
    try {
        const res = await fetch(inventariosApi, { credentials: 'same-origin' });
        if (!res.ok) throw new Error('Failed to fetch');
        const data = await res.json();
        const tbody = document.getElementById('inventarios-tbody');
        tbody.innerHTML = '';
        if (data.length === 0) {
            tbody.innerHTML = '<tr class="border-t"><td colspan="8" class="p-4">No hay items.</td></tr>';
            return;
        }

        data.forEach(item => {
            const tr = document.createElement('tr');
            tr.className = 'border-t border-surface-container/30';
            tr.innerHTML = `
                <td class="px-6 py-4">${item.id}</td>
                <td class="px-6 py-4">${item.nombre}</td>
                <td class="px-6 py-4">${item.producto ? item.producto.nombre : ''}</td>
                <td class="px-6 py-4">${item.stock_inicial}</td>
                <td class="px-6 py-4">${item.stock_minimo}</td>
                <td class="px-6 py-4">${item.unidad_medida ?? ''}</td>
                <td class="px-6 py-4">${item.descuento_inventario ?? ''}</td>
                <td class="px-6 py-4">
                    <button onclick="editInventario(${item.id})" class="mr-2 text-white/80">Editar</button>
                    <button onclick="deleteInventario(${item.id})" class="text-red-400">Eliminar</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    } catch (err) {
        console.error(err);
        showAlert('Error al cargar inventarios', 'error');
    }
}

function openInventarioModal() {
    const modal = document.getElementById('inventario-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.getElementById('inventario-modal-title').textContent = 'Crear Item';
    document.getElementById('inventario-id').value = '';
    document.getElementById('inventario-nombre').value = '';
    document.getElementById('inventario-stock-inicial').value = 0;
    document.getElementById('inventario-stock-minimo').value = 0;
    document.getElementById('inventario-unidad').value = '';
    document.getElementById('inventario-descuento').value = 1;
    document.getElementById('inventario-producto-name').value = '';
    document.getElementById('inventario-producto-id').value = '';
}

function closeInventarioModal() {
    const modal = document.getElementById('inventario-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

async function editInventario(id) {
    try {
        const res = await fetch(`${inventariosApi}/${id}`, { credentials: 'same-origin' });
        if (!res.ok) throw new Error('Failed');
        const item = await res.json();
        document.getElementById('inventario-modal-title').textContent = 'Editar Item';
        document.getElementById('inventario-id').value = item.id;
        document.getElementById('inventario-nombre').value = item.nombre;
        document.getElementById('inventario-stock-inicial').value = item.stock_inicial;
        document.getElementById('inventario-stock-minimo').value = item.stock_minimo;
        document.getElementById('inventario-unidad').value = item.unidad_medida || '';
        document.getElementById('inventario-descuento').value = item.descuento_inventario ?? 1;
        document.getElementById('inventario-producto-id').value = item.producto ? item.producto.id : '';
        document.getElementById('inventario-producto-name').value = item.producto ? item.producto.nombre : '';
        const modal = document.getElementById('inventario-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    } catch (err) {
        console.error(err);
        showAlert('Error al cargar item', 'error');
    }
}

async function deleteInventario(id) {
    if (!confirm('¿Eliminar este item?')) return;
    try {
        const res = await fetch(`${inventariosApi}/${id}`, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': csrfToken() }
        });
        if (!res.ok) throw new Error('Failed to delete');
        await loadInventarios();
        showAlert('Item eliminado');
    } catch (err) {
        console.error(err);
        showAlert('Error al eliminar', 'error');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadInventarios();

    document.getElementById('inventario-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('inventario-id').value;
        const nombre = document.getElementById('inventario-nombre').value.trim();
        const stock_inicial = parseInt(document.getElementById('inventario-stock-inicial').value, 10) || 0;
        const stock_minimo = parseInt(document.getElementById('inventario-stock-minimo').value, 10) || 0;
        const unidad_medida = document.getElementById('inventario-unidad').value.trim();
        const descuento = parseFloat(document.getElementById('inventario-descuento').value) || 0;

        try {
                const productoId = document.getElementById('inventario-producto-id').value || null;
                const payload = { nombre, producto_id: productoId, stock_inicial, stock_minimo, unidad_medida, descuento_inventario: descuento };
            const opt = { method: id ? 'PUT' : 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() }, credentials: 'same-origin', body: JSON.stringify(payload) };
            const url = id ? `${inventariosApi}/${id}` : inventariosApi;
            const res = await fetch(url, opt);
            if (res.status === 422) {
                const err = await res.json();
                document.getElementById('inventario-form-error').classList.remove('hidden');
                document.getElementById('inventario-form-error').textContent = Object.values(err.errors || {}).flat().join(', ');
                return;
            }
            if (!res.ok) throw new Error('Failed to save');
            await loadInventarios();
            closeInventarioModal();
            showAlert(id ? 'Item actualizado' : 'Item creado');
        } catch (err) {
            console.error(err);
            showAlert('Error al guardar', 'error');
        }
    });
});

// wire product name input to hidden id
document.addEventListener('input', (e) => {
    if (e.target && e.target.id === 'inventario-producto-name') {
        const name = e.target.value;
        const found = productos.find(p => p.nombre === name);
        document.getElementById('inventario-producto-id').value = found ? found.id : '';
        const dropdown = document.getElementById('productos-dropdown');
        if (!dropdown) return;
        // If input is empty, show full list; otherwise show filtered results
        if (name.trim() === '') {
            renderProductosDropdown(productos);
            return;
        }
        const filtered = productos.filter(p => p.nombre.toLowerCase().includes(name.toLowerCase()));
        if (filtered.length === 0) {
            dropdown.classList.add('hidden');
            return;
        }
        // render filtered
        dropdown.innerHTML = '';
        filtered.forEach(p => {
            const item = document.createElement('div');
            item.className = 'px-3 py-2 hover:bg-surface-container-highest cursor-pointer text-white';
            item.textContent = p.nombre;
            item.dataset.id = p.id;
            item.addEventListener('click', () => {
                document.getElementById('inventario-producto-name').value = p.nombre;
                document.getElementById('inventario-producto-id').value = p.id;
                dropdown.classList.add('hidden');
            });
            dropdown.appendChild(item);
        });
    }
});

// hide dropdown when clicking outside
document.addEventListener('click', (e) => {
    const dropdown = document.getElementById('productos-dropdown');
    const input = document.getElementById('inventario-producto-name');
    if (!dropdown || !input) return;
    if (e.target === input || dropdown.contains(e.target)) return;
    dropdown.classList.add('hidden');
});

document.addEventListener('DOMContentLoaded', () => {
    loadProductos();
    const input = document.getElementById('inventario-producto-name');
    const dropdown = document.getElementById('productos-dropdown');
    if (input && dropdown) {
        // show full list when focusing or clicking the input
        input.addEventListener('focus', (ev) => {
            ev.stopPropagation();
            renderProductosDropdown(productos);
        });
        input.addEventListener('click', (ev) => {
            ev.stopPropagation();
            renderProductosDropdown(productos);
        });
    }
});
