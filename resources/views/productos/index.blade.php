@extends('layouts.app')

@section('title', 'Gestión de Productos')
@section('content')
@section('navigation')
    <a href="{{ route('dashboard') }}" class="block px-4 py-3 rounded-lg text-white/70 hover:text-white hover:bg-surface-container font-semibold transition">
        <svg class="w-5 h-5 inline-block mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 11l4-4"/>
        </svg>
        <span>Panel de Control</span>
    </a>
    <a href="{{ route('usuarios.index') }}" class="block mt-2 px-4 py-3 rounded-lg text-white/70 hover:text-white hover:bg-surface-container font-semibold transition">
        <svg class="w-5 h-5 inline-block mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.485 0 4.79.707 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <span>Gestión de Usuarios</span>
    </a>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
        <div class="card-hover bg-surface-container-low rounded-2xl p-6 shadow-lg cursor-pointer border border-surface-container hover:border-primary-container/50 transition-all duration-300 group" onclick="openProductoModal()">
            <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-primary-container/50 to-surface-container-high rounded-full mb-4 border border-inverse-primary/50">
                <svg class="w-6 h-6 text-primary group-hover:text-primary-dark transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-white mb-2">Crear Producto</h3>
            <p class="text-sm text-white/70">Agrega un nuevo plato o producto</p>
        </div>

        <div class="card-hover bg-surface-container-low rounded-2xl p-6 shadow-lg cursor-pointer border border-surface-container hover:border-primary-container/50 transition-all duration-300 group" onclick="scrollToProductosTable()">
            <div class="flex items-center justify-center w-12 h-12 bg-surface-container rounded-full mb-4 border border-surface-container-high">
                <svg class="w-6 h-6 text-primary group-hover:text-primary-dark transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-white mb-2">Ver Productos</h3>
            <p class="text-sm text-white/70">Lista de productos</p>
        </div>

        <div class="card-hover bg-surface-container-low rounded-2xl p-6 shadow-lg cursor-pointer border border-surface-container hover:border-primary-container/50 transition-all duration-300 group" onclick="loadProductos()">
            <div class="flex items-center justify-center w-12 h-12 bg-primary-container/40 rounded-full mb-4 border border-primary-container/50">
                <svg class="w-6 h-6 text-primary group-hover:text-primary-dark transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-white mb-2">Actualizar</h3>
            <p class="text-sm text-white/70">Recargar lista de productos</p>
        </div>
    </div>

    <div id="productos-section" class="bg-surface-container-low rounded-2xl shadow-lg overflow-hidden border border-surface-container">
        <div class="px-6 py-4 bg-gradient-to-r from-surface-container to-surface-container-lowest border-b border-surface-container">
            <h2 class="text-xl font-bold text-white">Lista de Productos</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm table-striped text-white">
                <thead class="bg-surface-container border-b border-surface-container-high">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-white">#ID</th>
                        <th class="px-6 py-3 text-left font-semibold text-white">Imagen</th>
                        <th class="px-6 py-3 text-left font-semibold text-white">Nombre</th>
                        <th class="px-6 py-3 text-left font-semibold text-white">Categoría</th>
                        <th class="px-6 py-3 text-left font-semibold text-white">Precio</th>
                        <th class="px-6 py-3 text-center font-semibold text-white">Acciones</th>
                    </tr>
                </thead>
                <tbody id="productos-tbody">
                    <tr class="border-t border-surface-container/30">
                        <td colspan="6" class="px-6 py-8 text-center text-white/60">Cargando productos...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Producto Modal -->
<div id="producto-modal" class="modal-overlay hidden fixed inset-0 flex items-center justify-center p-4 z-50 bg-black/50">
    <div class="bg-surface-container-low rounded-2xl shadow-2xl max-w-md w-full p-8 border border-surface-container">
        <div class="flex items-center justify-between mb-6">
            <h2 id="producto-modal-title" class="text-2xl font-bold text-white">Crear Producto</h2>
            <button onclick="closeProductoModal()" class="text-white/60 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="producto-form" class="space-y-4" enctype="multipart/form-data">
            <input type="hidden" id="producto-id">

            <div>
                <label class="block text-sm font-semibold text-white mb-2">Nombre</label>
                <input type="text" id="producto-nombre" placeholder="Nombre del plato" class="w-full px-4 py-2 bg-surface-container border border-surface-container-high text-white placeholder-white/50 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required />
            </div>

            <div>
                <label class="block text-sm font-semibold text-white mb-2">Categoría</label>
                <input type="text" id="producto-categoria" placeholder="Entrante, Principal" class="w-full px-4 py-2 bg-surface-container border border-surface-container-high text-white placeholder-white/50 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required />
            </div>

            <div>
                <label class="block text-sm font-semibold text-white mb-2">Precio</label>
                <input type="number" step="0.01" id="producto-precio" placeholder="25000.00" class="w-full px-4 py-2 bg-surface-container border border-surface-container-high text-white placeholder-white/50 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required />
            </div>

            <div>
                <label class="block text-sm font-semibold text-white mb-2">Imagen (opcional)</label>
                <input type="file" id="producto-imagen" accept="image/*" class="w-full text-white" />
            </div>

            <div id="producto-preview-container" class="hidden mt-4">
                <label class="block text-sm font-semibold text-white mb-2">Vista previa</label>
                <img id="producto-preview" src="/img/no-image.svg" alt="Preview" class="w-48 h-32 object-cover rounded-lg border border-surface-container" />
            </div>

            <div id="producto-form-error" class="hidden p-4 bg-red-500/10 border border-red-500/30 text-red-400 rounded-lg text-sm"></div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 text-white font-semibold py-2 rounded-lg bg-amber-600 hover:bg-amber-700 transition">Guardar</button>
                <button type="button" onclick="closeProductoModal()" class="flex-1 px-4 py-2 border border-surface-container-high text-white/70 font-semibold rounded-lg hover:bg-surface-container-highest hover:text-white">Cancelar</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script src="/js/productos-index.js"></script>
@endpush
