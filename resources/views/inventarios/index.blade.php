@extends('layouts.app')

@section('title', 'Gestión de Inventario')

@section('navigation')
    <a href="{{ route('dashboard') }}" class="block px-4 py-3 rounded-lg text-white/70 hover:text-white hover:bg-surface-container font-semibold transition">
        <svg class="w-5 h-5 inline-block mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 11l4-4"/>
        </svg>
        <span>Panel de Control</span>
    </a>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div id="alerts" class="mb-6"></div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
        <div class="card-hover bg-surface-container-low rounded-2xl p-6 shadow-lg cursor-pointer border border-surface-container hover:border-primary-container/50 transition-all duration-300 group" onclick="openInventarioModal()">
            <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-primary-container/50 to-surface-container-high rounded-full mb-4 border border-inverse-primary/50">
                <svg class="w-6 h-6 text-primary group-hover:text-primary-dark transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V6a4 4 0 10-8 0v5M3 11h18l-1 9a2 2 0 01-2 2H6a2 2 0 01-2-2L3 11z"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-white mb-2">Crear Item</h3>
            <p class="text-sm text-white/70">Agregar ítem al inventario</p>
        </div>

        <div class="card-hover bg-surface-container-low rounded-2xl p-6 shadow-lg cursor-pointer border border-surface-container hover:border-primary-container/50 transition-all duration-300 group" onclick="scrollToInventariosTable()">
            <div class="flex items-center justify-center w-12 h-12 bg-surface-container rounded-full mb-4 border border-surface-container-high">
                <svg class="w-6 h-6 text-primary group-hover:text-primary-dark transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-white mb-2">Ver Inventario</h3>
            <p class="text-sm text-white/70">Lista de items</p>
        </div>

        <div class="card-hover bg-surface-container-low rounded-2xl p-6 shadow-lg cursor-pointer border border-surface-container hover:border-primary-container/50 transition-all duration-300 group" onclick="loadInventarios()">
            <div class="flex items-center justify-center w-12 h-12 bg-primary-container/40 rounded-full mb-4 border border-primary-container/50">
                <svg class="w-6 h-6 text-primary group-hover:text-primary-dark transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-white mb-2">Actualizar</h3>
            <p class="text-sm text-white/70">Recargar lista</p>
        </div>
    </div>

    <div id="inventarios-section" class="bg-surface-container-low rounded-2xl shadow-lg overflow-hidden border border-surface-container">
        <div class="px-6 py-4 bg-gradient-to-r from-surface-container to-surface-container-lowest border-b border-surface-container">
            <h2 class="text-xl font-bold text-white">Lista de Inventario</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm table-striped text-white">
                <thead class="bg-surface-container border-b border-surface-container-high">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-white">#ID</th>
                        <th class="px-6 py-3 text-left font-semibold text-white">Nombre</th>
                        <th class="px-6 py-3 text-left font-semibold text-white">Producto</th>
                        <th class="px-6 py-3 text-left font-semibold text-white">Stock Inicial</th>
                        <th class="px-6 py-3 text-left font-semibold text-white">Stock Mínimo</th>
                                <th class="px-6 py-3 text-left font-semibold text-white">Unidad</th>
                                <th class="px-6 py-3 text-left font-semibold text-white">Descuento</th>
                                <th class="px-6 py-3 text-left font-semibold text-white">Acciones</th>
                    </tr>
                </thead>
                <tbody id="inventarios-tbody">
                    <tr class="border-t border-surface-container/30">
                        <td colspan="8" class="px-6 py-4">Cargando...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Inventario Modal -->
<div id="inventario-modal" class="modal-overlay hidden fixed inset-0 flex items-center justify-center p-4 z-50 bg-black/50">
    <div class="bg-surface-container-low rounded-2xl shadow-2xl max-w-md w-full p-8 border border-surface-container">
        <div class="flex items-center justify-between mb-6">
            <h2 id="inventario-modal-title" class="text-2xl font-bold text-white">Crear Item</h2>
            <button onclick="closeInventarioModal()" class="text-white/60 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="inventario-form" class="space-y-4">
            <input type="hidden" id="inventario-id">

            <div>
                <label class="block text-sm font-semibold text-white mb-2">Nombre</label>
                <input type="text" id="inventario-nombre" placeholder="Nombre del ítem" class="w-full px-4 py-2 bg-surface-container border border-surface-container-high text-white placeholder-white/50 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required />
            </div>

            <div class="grid grid-cols-4 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-white mb-2">Stock Inicial</label>
                    <input type="number" id="inventario-stock-inicial" min="0" value="0" class="w-full px-4 py-2 bg-surface-container border border-surface-container-high text-white rounded-lg" required />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-white mb-2">Stock Mínimo</label>
                    <input type="number" id="inventario-stock-minimo" min="0" value="0" class="w-full px-4 py-2 bg-surface-container border border-surface-container-high text-white rounded-lg" required />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-white mb-2">Unidad</label>
                    <input type="text" id="inventario-unidad" placeholder="pcs, kg" class="w-full px-4 py-2 bg-surface-container border border-surface-container-high text-white rounded-lg" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-white mb-2">Descuento</label>
                    <input type="number" id="inventario-descuento" min="0" step="any" value="1" class="w-full px-4 py-2 bg-surface-container border border-surface-container-high text-white rounded-lg" />
                </div>
            </div>

            <div class="relative">
                <label class="block text-sm font-semibold text-white mb-2">Producto (opcional)</label>
                <input id="inventario-producto-name" autocomplete="off" placeholder="Escribe para buscar..." class="w-full px-4 py-2 bg-surface-container border border-surface-container-high text-white rounded-lg" />
                <input type="hidden" id="inventario-producto-id">

                <!-- Custom dropdown for products (scrollable) -->
                <div id="productos-dropdown" class="hidden absolute left-0 right-0 mt-1 bg-surface-container-low border border-surface-container rounded-lg shadow-lg z-50 max-h-40 overflow-y-auto"></div>
            </div>

            <div id="inventario-form-error" class="hidden p-4 bg-red-500/10 border border-red-500/30 text-red-400 rounded-lg text-sm"></div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 text-white font-semibold py-2 rounded-lg bg-amber-600 hover:bg-amber-700 transition">Guardar</button>
                <button type="button" onclick="closeInventarioModal()" class="flex-1 px-4 py-2 border border-surface-container-high text-white/70 font-semibold rounded-lg hover:bg-surface-container-highest hover:text-white">Cancelar</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script src="/js/inventarios-index.js"></script>
@endpush
