@extends('layouts.app')

@section('title', 'Listado - Alquiler de Salón')

@push('styles')
    @vite('resources/css/pages/alquiler.css')
    <style>
        /* Gold button styles */
        .gold-btn {
            background: linear-gradient(180deg,#f6e2a6 0%, #c8a84b 100%);
            color: #111827;
            transition: background .15s ease, transform .06s ease;
        }
        .gold-btn:hover {
            background: linear-gradient(180deg,#edd884 0%, #b58b2a 100%);
            transform: translateY(-1px);
        }
        .gold-btn:active { transform: translateY(0); }
        .gold-btn .material-symbols-outlined { color: #111827; }
        /* smaller gold button variant for header */
        .gold-btn-sm { padding: .375rem .75rem; background: linear-gradient(180deg,#f6e2a6 0%, #c8a84b 100%); color:#111827; border-radius:.375rem; }
        .gold-btn-sm:hover { background: linear-gradient(180deg,#edd884 0%, #b58b2a 100%); }
        /* Invoice modal custom styles */
        .modal-overlay .modal-content { padding: 0; }
        .invoice-modal-header { background: linear-gradient(90deg,#0b0f12,#0b0d0e); padding: .75rem 1rem; border-top-left-radius: .5rem; border-top-right-radius: .5rem; display:flex; align-items:center; justify-content:space-between; }
        .invoice-modal-title { margin:0; font-size:1.125rem; font-weight:700; color:#fff; }
        .invoice-modal-actions { display:flex; gap:.5rem; align-items:center; }
        .invoice-modal-body { background:#fff; color:#111; padding:1rem 1.25rem; border-bottom-left-radius:.5rem; border-bottom-right-radius:.5rem; }
        .invoice-modal .gold-btn-sm { box-shadow: 0 2px 0 rgba(0,0,0,0.08); }
        .invoice-modal-close { background:transparent; border:none; color:rgba(255,255,255,.85); }
    </style>
@endpush

@section('content')
<div class="p-6 lg:p-8 flex-1">
    @php $mesas = $mesas ?? collect(); @endphp
    <header class="flex items-center justify-between mb-6 no-print">
        <h2 class="text-2xl font-extrabold text-white">Listado de Alquileres</h2>
        <div class="flex gap-2">
            <a href="{{ route('alquiler') }}" class="px-4 py-2 rounded-lg bg-primary/10 border border-primary/20 text-primary font-bold">Nuevo Alquiler</a>
        </div>
    </header>

    @if(session('status'))
        <div class="mb-4 text-sm text-green-400">{{ session('status') }}</div>
    @endif

    <div class="bg-surface-container-low border border-white/5 rounded-2xl overflow-hidden shadow-2xl p-4">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-white">
                <thead>
                    <tr class="bg-surface-container-high border-b border-white/10">
                        <th class="text-left py-3 px-3">No.</th>
                        <th class="text-left py-3 px-3">Fecha</th>
                        <th class="text-left py-3 px-3">Tipo</th>
                        <th class="text-left py-3 px-3">Cliente</th>
                        <th class="text-left py-3 px-3">NIT</th>
                        <th class="text-right py-3 px-3">Total</th>
                        <th class="text-left py-3 px-3">Estatus</th>
                        <th class="text-center py-3 px-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($facturas as $f)
                        <tr class="border-b border-white/5 hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 px-3">{{ $f->numero_orden }}</td>
                            <td class="py-3 px-3">{{ $f->fecha }}</td>
                            <td class="py-3 px-3">{{ ucfirst($f->tipo ?? 'evento') }}</td>
                            <td class="py-3 px-3">{{ $f->persona }}</td>
                            <td class="py-3 px-3">{{ $f->nit }}</td>
                            <td class="py-3 px-3 text-right">{{ isset($f->monto_total) ? '$'.number_format($f->monto_total,0,',','.') : '$0' }}</td>
                            <td class="py-3 px-3">{{ $f->estatus }}</td>
                            <td class="py-3 px-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <!-- Descargar PDF -->
                                    <a href="{{ url('/alquiler/'.$f->id.'/pdf') }}" title="Descargar PDF" class="w-8 h-8 flex items-center justify-center rounded-md gold-btn">
                                        <span class="material-symbols-outlined text-sm">file_download</span>
                                    </a>

                                    <!-- Imprimir -->
                                    <a href="{{ route('alquiler.printview', $f->id) }}?autoprint=1" target="_blank" title="Imprimir" class="w-8 h-8 flex items-center justify-center rounded-md gold-btn">
                                        <span class="material-symbols-outlined text-sm">print</span>
                                    </a>

                                    <!-- Ver -->
                                    <button type="button" data-id="{{ $f->id }}" title="Ver factura" class="w-8 h-8 flex items-center justify-center rounded-md gold-btn js-open-invoice">
                                        <span class="material-symbols-outlined text-sm">visibility</span>
                                    </button>

                                    <!-- Editar -->
                                    <a href="{{ route('alquiler.edit', $f->id) }}" title="Editar factura" class="w-8 h-8 flex items-center justify-center rounded-md gold-btn">
                                        <span class="material-symbols-outlined text-sm">edit</span>
                                    </a>

                                    <!-- Eliminar -->
                                    <form action="{{ route('alquiler.delete', $f->id) }}" method="POST" onsubmit="return confirm('Eliminar factura #{{ $f->numero_orden }} ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Eliminar factura" class="w-8 h-8 flex items-center justify-center rounded-md gold-btn">
                                            <span class="material-symbols-outlined text-sm">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 px-3 text-center text-on-surface-variant">No hay alquileres registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $facturas->links() }}
        </div>
    </div>
</div>
        <!-- Invoice modal (matching site's modal design) -->
        <div id="invoice-modal" class="modal-overlay hidden fixed inset-0 flex items-center justify-center p-4 z-50 bg-black/50 invoice-modal">
            <div class="bg-surface-container-low rounded-2xl shadow-2xl max-w-6xl w-full border border-surface-container modal-content invoice-modal-card" style="max-height:90vh; overflow:auto;">
                <div class="invoice-modal-header">
                    <h3 class="invoice-modal-title">Vista de Factura</h3>
                    <div class="invoice-modal-actions">
                        <button type="button" title="Imprimir factura" class="gold-btn-sm flex items-center gap-2 js-print-invoice">
                            <span class="material-symbols-outlined">print</span>
                        </button>
                        <a id="invoice-modal-download" href="#" class="gold-btn-sm">Descargar PDF</a>
                        <button type="button" class="invoice-modal-close js-close-invoice" aria-label="Cerrar">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div id="invoice-modal-body" class="invoice-modal-body">
                    <!-- loaded invoice HTML goes here -->
                    <div class="text-center py-12 text-sm text-muted">Cargando...</div>
                </div>
            </div>
        </div>

        @push('scripts')
            <script src="/js/pos-printer.js"></script>
            <script src="/js/alquiler-list.js"></script>
        @endpush

        @endsection
