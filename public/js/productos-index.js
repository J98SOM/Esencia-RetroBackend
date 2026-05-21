// Productos front-end manager
(function() {
    const apiBase = '/api/productos';

    function apiHeaders(extraHeaders = {}) {
        if (window.getApiHeaders) {
            return window.getApiHeaders(extraHeaders);
        }

        return {
            Accept: 'application/json',
            ...extraHeaders,
        };
    }

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function showAlert(message, type = 'success') {
        const alerts = document.getElementById('alerts');
        const alertHTML = `
            <div class="p-4 rounded-lg border transition-all animate-fade-in ${type === 'success' ? 'bg-green-500/10 border-green-500/30 text-green-400' : 'bg-red-500/10 border-red-500/30 text-red-400'}">
                <p>${message}</p>
            </div>
        `;
        alerts.innerHTML = alertHTML;
        setTimeout(() => { alerts.innerHTML = ''; }, 3500);
    }

    async function loadProductos() {
        try {
            const res = await fetch(apiBase, { credentials: 'same-origin', headers: apiHeaders() });
            if (!res.ok) throw new Error('Failed to fetch productos');
            const data = await res.json();
            const tbody = document.getElementById('productos-tbody');
            tbody.innerHTML = '';

            // Support multiple response shapes: array, { productos: [...] }, or resource collection { data: [...] }
            let items = [];
            if (Array.isArray(data)) {
                items = data;
            } else if (data && Array.isArray(data.productos)) {
                items = data.productos;
            } else if (data && Array.isArray(data.data)) {
                items = data.data;
            }

            if (!items || items.length === 0) {
                tbody.innerHTML = '<tr class="border-t border-surface-container/30"><td colspan="6" class="px-6 py-8 text-center text-white/60">No hay productos</td></tr>';
                return;
            }

            items.forEach(p => {
                const tr = document.createElement('tr');
                tr.className = 'border-t border-surface-container/30 hover:bg-surface-container/20 transition-colors';
                tr.innerHTML = `
                    <td class="px-6 py-4 text-white">${p.id}</td>
                    <td class="px-6 py-4 text-sm text-white/70"><img src="${p.imagen_url || '/img/no-image.svg'}" class="w-16 h-12 object-cover rounded" alt="${p.nombre}"/></td>
                    <td class="px-6 py-4 text-white">${p.nombre}</td>
                    <td class="px-6 py-4 text-white/70">${p.categoria}</td>
                    <td class="px-6 py-4 text-white">${Number(p.precio).toFixed(2)}</td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick="editProducto(${p.id})" class="px-3 py-1 rounded bg-primary/20 text-primary hover:bg-primary/30 font-medium text-xs">Editar</button>
                            <button onclick="deleteProducto(${p.id})" class="px-3 py-1 rounded bg-red-500/20 text-red-400 hover:bg-red-500/30 font-medium text-xs">Eliminar</button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } catch (err) {
            console.error(err);
            const tbody = document.getElementById('productos-tbody');
            if (tbody) {
                tbody.innerHTML = '<tr class="border-t border-surface-container/30"><td colspan="6" class="px-6 py-8 text-center text-white/60">No hay productos</td></tr>';
            }
            showAlert('Error al cargar productos', 'error');
        }
    }

    window.openProductoModal = function() {
        const idEl = document.getElementById('producto-id'); if (idEl) idEl.value = '';
        const nombreEl = document.getElementById('producto-nombre'); if (nombreEl) nombreEl.value = '';
        const categoriaEl = document.getElementById('producto-categoria'); if (categoriaEl) categoriaEl.value = '';
        const precioEl = document.getElementById('producto-precio'); if (precioEl) precioEl.value = '';
        const imagenEl = document.getElementById('producto-imagen'); if (imagenEl) imagenEl.value = '';
        // clear preview when creating
        const prev = document.getElementById('producto-preview');
        const prevContainer = document.getElementById('producto-preview-container');
        if (prev) { prev.src = '/img/no-image.svg'; }
        if (prevContainer) { prevContainer.classList.add('hidden'); }
        const modal = document.getElementById('producto-modal'); if (modal) modal.classList.remove('hidden');
    };

    window.closeProductoModal = function() {
        const modal = document.getElementById('producto-modal'); if (modal) modal.classList.add('hidden');
    };

    window.editProducto = async function(id) {
        try {
            const res = await fetch(`${apiBase}/${id}`, { credentials: 'same-origin', headers: apiHeaders() });
            if (!res.ok) throw new Error('Failed to fetch producto');
            const p = await res.json();
            document.getElementById('producto-id').value = p.id;
            document.getElementById('producto-nombre').value = p.nombre;
            document.getElementById('producto-categoria').value = p.categoria;
            document.getElementById('producto-precio').value = p.precio;
            // show preview if available
            const prevContainer = document.getElementById('producto-preview-container');
            const prev = document.getElementById('producto-preview');
            if (p.imagen_url && prev) {
                prev.src = p.imagen_url;
                if (prevContainer) prevContainer.classList.remove('hidden');
            } else {
                if (prev) prev.src = '/img/no-image.svg';
                if (prevContainer) prevContainer.classList.add('hidden');
            }
            document.getElementById('producto-modal-title').textContent = 'Editar Producto';
            document.getElementById('producto-modal').classList.remove('hidden');
        } catch (err) {
            console.error(err);
            showAlert('Error al cargar el producto', 'error');
        }
    };

    // preview selected file in the modal
    function setupFilePreview() {
        const input = document.getElementById('producto-imagen');
        const prevContainer = document.getElementById('producto-preview-container');
        const prev = document.getElementById('producto-preview');
        if (!input || !prev) return;
        input.addEventListener('change', () => {
            const f = input.files[0];
            if (f) {
                prev.src = URL.createObjectURL(f);
                if (prevContainer) prevContainer.classList.remove('hidden');
            } else {
                prev.src = '/img/no-image.svg';
                if (prevContainer) prevContainer.classList.add('hidden');
            }
        });
    }

    window.deleteProducto = function(id) {
        if (!confirm('¿Eliminar producto?')) return;
        fetch(`${apiBase}/${id}`, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: apiHeaders({ 'X-CSRF-TOKEN': csrfToken() })
        }).then(r => {
            if (!r.ok) throw new Error('delete failed');
            showAlert('Producto eliminado');
            loadProductos();
        }).catch(err => {
            console.error(err);
            showAlert('Error al eliminar', 'error');
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        loadProductos();
        const formEl = document.getElementById('producto-form');
        if (!formEl) return;
        setupFilePreview();
        formEl.addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('producto-id').value;
            const nombre = document.getElementById('producto-nombre').value.trim();
            const categoria = document.getElementById('producto-categoria').value.trim();
            const precio = document.getElementById('producto-precio').value;
            const imagen = document.getElementById('producto-imagen').files[0];

            const form = new FormData();
            form.append('nombre', nombre);
            form.append('categoria', categoria);
            form.append('precio', precio);
            if (imagen) form.append('imagen', imagen);

            try {
                const url = id ? `${apiBase}/${id}` : apiBase;
                // Laravel expects POST with _method=PUT for updates
                if (id) form.append('_method', 'PUT');

                const res = await fetch(url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: apiHeaders({ 'X-CSRF-TOKEN': csrfToken() }),
                    body: form
                });

                if (!res.ok) {
                    const err = await res.json().catch(() => null);
                    const errEl = document.getElementById('producto-form-error');
                    if (errEl) {
                        errEl.textContent = (err && (err.message || JSON.stringify(err))) || 'Error al guardar';
                        errEl.classList.remove('hidden');
                    }
                    return;
                }

                window.closeProductoModal();
                loadProductos();
                showAlert(id ? 'Producto actualizado' : 'Producto creado');
            } catch (err) {
                console.error(err);
                showAlert('Error al guardar producto', 'error');
            }
        });
    });
})();