@extends('layouts.app')

@section('title', 'Gestión de Mesas')

@section('navigation')
    <a href="{{ route('dashboard') }}" class="block px-4 py-3 rounded-lg text-white/70 hover:text-white hover:bg-surface-container font-semibold transition">
        <svg class="w-5 h-5 inline-block mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 11l4-4"/>
        </svg>
        <span>Panel de Control</span>
    </a>
    <a href="{{ route('usuarios.index') }}" class="block px-4 py-3 rounded-lg text-white/70 hover:text-white hover:bg-surface-container font-semibold transition">
        <svg class="w-5 h-5 inline-block mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM15 20H9m6 0h6M9 20H3m6 0a9 9 0 1118 0m-9 0a4.5 4.5 0 100-9 4.5 4.5 0 000 9z"/>
        </svg>
        <span>Gestión de Usuarios</span>
    </a>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Alert Messages -->
    <div id="alerts" class="mb-6"></div>

    <!-- Action Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
        <!-- Create Mesa Card -->
        <div class="card-hover bg-surface-container-low rounded-2xl p-6 shadow-lg cursor-pointer border border-surface-container hover:border-primary-container/50 transition-all duration-300 group" onclick="openMesaModal()">
            <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-primary-container/50 to-surface-container-high rounded-full mb-4 border border-inverse-primary/50">
                <svg class="w-6 h-6 text-primary group-hover:text-primary-dark transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-white mb-2">Crear Mesa</h3>
            <p class="text-sm text-white/70">Agrega una nueva mesa al sistema</p>
        </div>

        <!-- View Mesas Card -->
        <div class="card-hover bg-surface-container-low rounded-2xl p-6 shadow-lg cursor-pointer border border-surface-container hover:border-primary-container/50 transition-all duration-300 group" onclick="scrollToMesasTable()">
            <div class="flex items-center justify-center w-12 h-12 bg-surface-container rounded-full mb-4 border border-surface-container-high">
                <svg class="w-6 h-6 text-primary group-hover:text-primary-dark transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-white mb-2">Ver Mesas</h3>
            <p class="text-sm text-white/70">Lista de todas las mesas</p>
        </div>

        <!-- Reload Mesas Card -->
        <div class="card-hover bg-surface-container-low rounded-2xl p-6 shadow-lg cursor-pointer border border-surface-container hover:border-primary-container/50 transition-all duration-300 group" onclick="loadMesas()">
            <div class="flex items-center justify-center w-12 h-12 bg-primary-container/40 rounded-full mb-4 border border-primary-container/50">
                <svg class="w-6 h-6 text-primary group-hover:text-primary-dark transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-white mb-2">Actualizar</h3>
            <p class="text-sm text-white/70">Recargar lista de mesas</p>
        </div>
    </div>

    <!-- Mesas Table -->
    <div id="mesas-section" class="bg-surface-container-low rounded-2xl shadow-lg overflow-hidden border border-surface-container">
        <div class="px-6 py-4 bg-gradient-to-r from-surface-container to-surface-container-lowest border-b border-surface-container">
            <h2 class="text-xl font-bold text-white">Lista de Mesas</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm table-striped text-white">
                <thead class="bg-surface-container border-b border-surface-container-high">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-white">#ID</th>
                        <th class="px-6 py-3 text-left font-semibold text-white">Nombre</th>
                        <th class="px-6 py-3 text-left font-semibold text-white">Capacidad</th>
                        <th class="px-6 py-3 text-left font-semibold text-white">Registro</th>
                        <th class="px-6 py-3 text-center font-semibold text-white">Acciones</th>
                    </tr>
                </thead>
                <tbody id="mesas-tbody">
                    <tr class="border-t border-surface-container/30">
                        <td colspan="5" class="px-6 py-8 text-center text-white/60">Cargando mesas...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Mesa Modal -->
<div id="mesa-modal" class="modal-overlay hidden fixed inset-0 flex items-center justify-center p-4 z-50 bg-black/50">
    <div class="bg-surface-container-low rounded-2xl shadow-2xl max-w-md w-full p-8 border border-surface-container">
        <div class="flex items-center justify-between mb-6">
            <h2 id="mesa-modal-title" class="text-2xl font-bold text-white">Crear Mesa</h2>
            <button onclick="closeMesaModal()" class="text-white/60 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="mesa-form" class="space-y-4">
            <input type="hidden" id="mesa-id">

            <div>
                <label class="block text-sm font-semibold text-white mb-2">Nombre</label>
                <input type="text" id="mesa-nombre-input" placeholder="Mesa 1" class="w-full px-4 py-2 bg-surface-container border border-surface-container-high text-white placeholder-white/50 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required/>
                <p class="text-red-400 text-xs mt-1 hidden" data-error="nombre"></p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-white mb-2">Capacidad (personas)</label>
                <input type="number" id="mesa-capacidad-input" placeholder="4" class="w-full px-4 py-2 bg-surface-container border border-surface-container-high text-white placeholder-white/50 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" min="1" required/>
                <p class="text-red-400 text-xs mt-1 hidden" data-error="capacidad"></p>
            </div>

            <div id="mesa-form-error" class="hidden p-4 bg-red-500/10 border border-red-500/30 text-red-400 rounded-lg text-sm"></div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 text-white font-semibold py-2 rounded-lg bg-amber-600 hover:bg-amber-700 transition">
                    Guardar
                </button>
                <button type="button" onclick="closeMesaModal()" class="flex-1 px-4 py-2 border border-surface-container-high text-white/70 font-semibold rounded-lg hover:bg-surface-container-highest hover:text-white">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
const apiBase = '/api/mesas';

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function showAlert(message, type = 'success') {
    const alerts = document.getElementById('alerts');
    const alertHTML = `
        <div class="p-4 rounded-lg border transition-all animate-fade-in ${
            type === 'success' 
                ? 'bg-green-500/10 border-green-500/30 text-green-400' 
                : 'bg-red-500/10 border-red-500/30 text-red-400'
        }">
            <p>${message}</p>
        </div>
    `;
    alerts.innerHTML = alertHTML;
    setTimeout(() => {
        alerts.innerHTML = '';
    }, 4000);
}

function showErrors(errors) {
    const errorDiv = document.getElementById('mesa-form-error');
    
    document.querySelectorAll('[data-error]').forEach(el => {
        el.classList.add('hidden');
        el.textContent = '';
    });
    
    if (Object.keys(errors).length === 0) {
        if (errorDiv) errorDiv.classList.add('hidden');
        return;
    }
    
    // Mostrar SweetAlert si hay error de nombre único
    if (errors.nombre && errors.nombre.some(msg => msg.includes('ya existe'))) {
        Swal.fire({
            icon: 'warning',
            title: '¡Nombre duplicado!',
            text: 'El nombre de la mesa ya existe. Por favor, usa otro nombre.',
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#ea7a39',
        });
        if (errorDiv) errorDiv.classList.add('hidden');
        return; // No mostrar otros errores
    }
    
    // Mostrar otros errores normalmente
    const messages = [];
    for (const [field, msgs] of Object.entries(errors)) {
        const errorEl = document.querySelector(`[data-error="${field}"]`);
        if (errorEl && msgs.length > 0) {
            errorEl.textContent = msgs[0];
            errorEl.classList.remove('hidden');
        }
        messages.push(...msgs);
    }
    
    if (errorDiv && messages.length > 0) {
        const errorMsg = errorDiv?.querySelector('p') || document.createElement('p');
        errorMsg.textContent = messages.join(', ');
        errorDiv.classList.remove('hidden');
    }
}

async function loadMesas() {
    try {
        const res = await fetch(apiBase, {
            credentials: 'same-origin',
            headers: window.getApiHeaders ? window.getApiHeaders() : { 'Accept': 'application/json' }
        });
        if (!res.ok) throw new Error('Failed to fetch mesas');
        const data = await res.json();
        const tbody = document.getElementById('mesas-tbody');
        tbody.innerHTML = '';
        
        if (data.length === 0) {
            tbody.innerHTML = '<tr class="border-t border-surface-container/30"><td colspan="5" class="px-6 py-8 text-center text-white/60">No hay mesas registradas</td></tr>';
            return;
        }
        
        data.forEach(mesa => {
            const tr = document.createElement('tr');
            tr.className = 'border-t border-surface-container/30 hover:bg-surface-container/20 transition-colors';
            const registroDate = new Date(mesa.created_at).toLocaleDateString('es-ES');
            tr.innerHTML = `
                <td class="px-6 py-4 text-sm font-medium text-white">${mesa.id}</td>
                <td class="px-6 py-4 text-sm text-white">${mesa.nombre}</td>
                <td class="px-6 py-4 text-sm">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-container/20 text-primary">
                        ${mesa.capacidad} personas
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-white/70">${registroDate}</td>
                <td class="px-6 py-4 text-sm text-center">
                    <div class="flex items-center justify-center gap-2">
                        <button onclick="editMesa(${mesa.id})" class="px-3 py-1 rounded bg-primary/20 text-primary hover:bg-primary/30 font-medium transition text-xs">Editar</button>
                        <button onclick="deleteMesa(${mesa.id})" class="px-3 py-1 rounded bg-red-500/20 text-red-400 hover:bg-red-500/30 font-medium transition text-xs">Eliminar</button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    } catch (err) {
        console.error(err);
        showAlert('Error al cargar mesas', 'error');
    }
}

function openMesaModal() {
    const modal = document.getElementById('mesa-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.getElementById('mesa-modal-title').textContent = 'Crear Mesa';
    document.getElementById('mesa-id').value = '';
    document.getElementById('mesa-nombre-input').value = '';
    document.getElementById('mesa-capacidad-input').value = '';
    document.getElementById('mesa-form-error').classList.add('hidden');
    document.querySelectorAll('[data-error]').forEach(el => {
        el.classList.add('hidden');
        el.textContent = '';
    });
}

function closeMesaModal() {
    document.getElementById('mesa-modal').classList.add('hidden');
    document.getElementById('mesa-modal').classList.remove('flex');
    document.getElementById('mesa-form').reset();
    document.getElementById('mesa-form-error').classList.add('hidden');
}

async function editMesa(id) {
    try {
        const res = await fetch(`${apiBase}/${id}`, {
            credentials: 'same-origin',
            headers: window.getApiHeaders ? window.getApiHeaders() : { 'Accept': 'application/json' }
        });
        if (!res.ok) throw new Error('Failed to fetch mesa');
        const mesa = await res.json();
        
        document.getElementById('mesa-modal-title').textContent = 'Editar Mesa';
        document.getElementById('mesa-id').value = mesa.id;
        document.getElementById('mesa-nombre-input').value = mesa.nombre;
        document.getElementById('mesa-capacidad-input').value = mesa.capacidad;
        
        const modal = document.getElementById('mesa-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    } catch (err) {
        console.error(err);
        showAlert('Error al cargar la mesa', 'error');
    }
}

async function deleteMesa(id) {
    if (!confirm('¿Estás seguro de que deseas eliminar esta mesa?')) return;
    try {
        const res = await fetch(`${apiBase}/${id}`, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: (window.getApiHeaders ? window.getApiHeaders({ 'X-CSRF-TOKEN': csrfToken() }) : { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' })
        });
        if (!res.ok) throw new Error('Failed to delete');
        await loadMesas();
        showAlert('Mesa eliminada correctamente.');
    } catch (err) {
        console.error(err);
        showAlert('Error al eliminar la mesa', 'error');
    }
}

function scrollToMesasTable() {
    document.getElementById('mesas-section').scrollIntoView({ behavior: 'smooth' });
}

document.addEventListener('DOMContentLoaded', () => {
    loadMesas();

    document.getElementById('mesa-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('mesa-id').value;
        const nombre = document.getElementById('mesa-nombre-input').value.trim();
        const capacidad = parseInt(document.getElementById('mesa-capacidad-input').value, 10);
        
        try {
            const url = id ? `${apiBase}/${id}` : apiBase;
            const method = id ? 'PUT' : 'POST';
            const res = await fetch(url, {
                method,
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    ...(window.getApiHeaders ? window.getApiHeaders({ 'X-CSRF-TOKEN': csrfToken() }) : { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' })
                },
                body: JSON.stringify({ nombre, capacidad })
            });
            
            const contentType = res.headers.get('content-type') || '';
            const data = contentType.includes('application/json') ? await res.json() : { message: await res.text() };
            
            if (!res.ok) {
                showErrors(data.errors || {});
                if (!data.errors && data.message) {
                    showAlert(data.message, 'error');
                }
                return;
            }
            
            await loadMesas();
            closeMesaModal();
            showAlert(id ? 'Mesa actualizada correctamente.' : 'Mesa creada correctamente.');
        } catch (err) {
            console.error(err);
            showAlert('Error al guardar la mesa', 'error');
        }
    });
});
</script>
@endpush
