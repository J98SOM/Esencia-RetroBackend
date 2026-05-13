@extends('layouts.app')

@section('title', 'Esencia Retro - Alquiler de Salón')

{{-- CSS dedicado a esta vista --}}
@push('styles')
    @vite('resources/css/pages/alquiler.css')
@endpush

@section('content')
<div class="p-6 lg:p-8 flex-1">

    {{-- ── Page Header ─────────────────────────────────────────── --}}
    <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8 no-print">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <div class="w-10 h-10 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary"
                          style="font-variation-settings:'FILL' 1;">celebration</span>
                </div>
                <h2 class="text-3xl font-extrabold tracking-tight text-white">Alquiler de Salón</h2>
            </div>
            <p class="text-on-surface-variant text-sm mt-1 ml-13">Gestión y facturación de eventos y reservas de salón</p>
        </div>
        <div class="flex gap-3">
            <button onclick="limpiarFormulario()"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-white/10 text-slate-400
                           hover:bg-white/5 hover:text-white transition-all text-xs font-bold uppercase tracking-widest">
                <span class="material-symbols-outlined text-sm">refresh</span>
                <span>Limpiar</span>
            </button>
            <button onclick="imprimirFactura()"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary/10 border border-primary/20
                           text-primary hover:bg-primary hover:text-on-primary transition-all text-xs font-bold uppercase tracking-widest">
                <span class="material-symbols-outlined text-sm">print</span>
                <span>Imprimir</span>
            </button>
        </div>
    </header>

    {{-- ── Invoice Card ───────────────────────────────────────── --}}
    <div id="factura-alquiler" class="bg-surface-container-low border border-white/5 rounded-2xl overflow-hidden shadow-2xl">

        {{-- Hidden input to indicate editing and carry factura id (if any) --}}
        <input type="hidden" id="factura-id" data-editing="{{ isset($factura) && $factura->id ? '1' : '0' }}" value="{{ $factura->id ?? '' }}">

        {{-- Empresa Header --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-0 border-b border-white/10">

            {{-- Logo & datos empresa --}}
            <div class="flex items-center gap-5 p-6 border-b md:border-b-0 md:border-r border-white/10">
                <div class="w-24 h-24 min-w-[96px] rounded-xl overflow-hidden border border-primary/20 bg-black
                            flex items-center justify-center">
                    <img src="{{ asset('img/logo.png') }}" alt="Esencia Retro"
                         class="w-full h-full object-contain mix-blend-screen p-1">
                </div>
                <div class="space-y-1">
                    <p class="company-name text-white font-black text-sm tracking-wide">ESENCIA RETRO</p>
                    <p class="text-on-surface-variant text-xs">NIT: 1,007,450,540</p>
                    <p class="text-on-surface-variant text-xs">CR / RUT:</p>
                    <p class="text-on-surface-variant text-xs">Tel: 3162218491 - 3209180085</p>
                    <p class="text-on-surface-variant text-xs">Ciudad Bogotá</p>
                    <p class="text-on-surface-variant text-xs">Correo: esenciaretro10@gmail.com</p>
                </div>
            </div>

            {{-- Datos de la factura --}}
            <div class="p-6 flex flex-col justify-center">
                <h1 class="text-2xl font-black text-primary tracking-widest uppercase mb-5
                           text-center border-b border-white/10 pb-4">Factura de Venta</h1>
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <label class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant w-24 shrink-0">No.</label>
                          <input type="text" id="factura-no" name="factura_no" readonly
                               class="flex-1 bg-surface-container-highest border border-white/10 rounded-lg
                                   px-3 py-2 text-white text-sm font-bold focus:outline-none focus:border-primary transition-all"
                              value="{{ $factura->numero_orden ?? $nextInvoiceNo ?? '' }}">
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant w-24 shrink-0">Fecha</label>
                           <input type="date" id="factura-fecha" name="factura_fecha"
                               class="flex-1 bg-surface-container-highest border border-white/10 rounded-lg
                                   px-3 py-2 text-primary text-sm font-bold focus:outline-none focus:border-primary transition-all"
                               value="{{ $factura->fecha ?? date('Y-m-d') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Orden de compra (encima de los inputs del cliente) --}}
        <div class="px-5 pt-4 pb-2">
            <div class="flex items-center">
                <label class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant w-36 shrink-0">Orden de compra / pedido:</label>
                  <input type="text" id="orden-compra" name="orden_compra"
                      class="flex-1 bg-surface-container-highest border border-white/10 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-primary transition-all"
                      placeholder="" value="{{ $factura->orden_compra ?? '' }}">
            </div>
        </div>

        {{-- Cliente --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-0 border-b border-white/10">
            <div class="p-5 space-y-3 border-b md:border-b-0 md:border-r border-white/10">
                <div class="flex items-center gap-3">
                    <label class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant w-24 shrink-0">Señores</label>
                              <input type="text" id="cliente-nombre" name="cliente_nombre"
                              class="flex-1 bg-surface-container-highest border border-white/10 rounded-lg
                                  px-3 py-2 text-white text-sm font-semibold focus:outline-none focus:border-primary transition-all"
                                  placeholder="Nombre del cliente" value="{{ $factura->persona ?? '' }}">
                </div>
                <div class="flex items-center gap-3">
                    <label class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant w-24 shrink-0">NIT</label>
                          <input type="text" id="cliente-nit" name="cliente_nit"
                              class="flex-1 bg-surface-container-highest border border-white/10 rounded-lg
                                  px-3 py-2 text-white text-sm focus:outline-none focus:border-primary transition-all"
                              placeholder="NIT / Cédula" value="{{ $factura->nit ?? '' }}">
                </div>
                <div class="flex items-center gap-3">
                    <label class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant w-24 shrink-0">Dirección</label>
                          <input type="text" id="cliente-direccion" name="cliente_direccion"
                              class="flex-1 bg-surface-container-highest border border-white/10 rounded-lg
                                  px-3 py-2 text-white text-sm focus:outline-none focus:border-primary transition-all"
                              placeholder="Dirección" value="{{ $factura->direccion ?? '' }}">
                </div>
            </div>
            <div class="p-5 space-y-3">
                <div class="flex items-center gap-3">
                    <label class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant w-24 shrink-0">Teléfono</label>
                              <input type="tel" id="cliente-telefono" name="cliente_telefono"
                                  class="flex-1 bg-surface-container-highest border border-white/10 rounded-lg
                                      px-3 py-2 text-white text-sm font-semibold focus:outline-none focus:border-primary transition-all"
                                  placeholder="Teléfono" value="{{ $factura->telefono ?? '' }}">
                </div>
                <div class="flex items-center gap-3">
                    <label class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant w-24 shrink-0">Ciudad</label>
                              <input type="text" id="cliente-ciudad" name="cliente_ciudad"
                                  class="flex-1 bg-surface-container-highest border border-white/10 rounded-lg
                                      px-3 py-2 text-white text-sm font-semibold focus:outline-none focus:border-primary transition-all"
                                  placeholder="Ciudad" value="{{ $factura->ciudad ?? '' }}">
                </div>

                {{-- Medio de pago (debajo de Ciudad) --}}
                <div class="flex items-center gap-3">
                    <label class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant w-24 shrink-0">Medio de pago</label>
                    @php $selectedMedio = $metodos[0]['metodo'] ?? ($factura && isset($factura->metodo) ? $factura->metodo : null); @endphp
                    <select id="medio-pago" name="medio_pago"
                            class="flex-1 bg-surface-container-highest border border-white/10 rounded-lg px-3 py-2 text-white text-xs font-bold focus:outline-none focus:border-primary transition-all">
                        <option value="efectivo" {{ ($selectedMedio === 'efectivo') ? 'selected' : '' }}>Efectivo</option>
                        <option value="transferencia" {{ ($selectedMedio === 'transferencia') ? 'selected' : '' }}>Transferencia</option>
                        <option value="tarjeta" {{ ($selectedMedio === 'tarjeta') ? 'selected' : '' }}>Tarjeta</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Mostrar medio de pago seleccionado encima de los inputs (solo lectura) --}}
        <div class="px-5 pt-2 pb-2">
            <div class="flex justify-end">
                <div class="text-[12px] text-on-surface-variant">
                    <strong class="uppercase mr-2">Medio de pago:</strong>
                    <span id="display-medio-pago" class="font-black text-primary">{{ strtoupper($selectedMedio ?? '') }}</span>
                </div>
            </div>
        </div>

        {{-- Control de cantidad de ítems --}}
        <div class="flex items-center gap-4 px-5 pt-4 pb-2 no-print mt-6">
            <span class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
                Cantidad de ítems:
            </span>
            @php $itemsCount = isset($items) ? count($items) : 0; $itemsCount = $itemsCount > 0 ? $itemsCount : 5; @endphp
            <select id="items-count-selector"
                    onchange="onCantidadItemsChange(this)"
                    aria-label="Seleccionar cantidad de ítems">
                @for($n = 1; $n <= 15; $n++)
                    <option value="{{ $n }}" {{ $n === $itemsCount ? 'selected' : '' }}>{{ $n }}</option>
                @endfor
            </select>
        </div>

        <div class="overflow-x-auto">
            @if(config('app.debug'))
                <div class="px-5 mb-3 text-xs text-on-surface-variant">
                    <strong>Debug:</strong>
                    Productos cargados: {{ isset($products) ? $products->count() : 0 }}
                    @if(isset($products) && $products->count() > 0)
                        — Ejemplos: {{ $products->take(5)->pluck('nombre')->join(', ') }}
                    @endif
                </div>
            @endif
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-surface-container-high border-b border-white/10">
                        <th class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant py-3 px-4 text-center w-10">Ítem</th>
                        <th class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant py-3 px-3 text-left">Descripción</th>
                        <th class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant py-3 px-3 text-center w-16">Cant/Hora</th>
                        <th class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant py-3 px-3 text-right w-32">Vr. Unitario</th>
                        <th class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant py-3 px-4 text-right w-32">Vr. Bruto</th>
                    </tr>
                </thead>
                <tbody id="items-tabla">
                        @for($i = 1; $i <= 15; $i++)
                    @php $it = $items[$i-1] ?? null; $descVal = $it['desc'] ?? ''; $cantVal = $it['cant'] ?? ''; $precioVal = $it['precio'] ?? ''; $bruto = ($cantVal && $precioVal) ? number_format($cantVal * $precioVal,0,',','.') : '0'; @endphp
                    <tr class="border-b border-white/5 hover:bg-white/[0.02] transition-colors item-row"
                        data-row="{{ $i }}">
                        {{-- # --}}
                        <td class="py-2 px-4 text-center text-on-surface-variant text-xs font-bold">{{ $i }}</td>

                        {{-- Descripción --}}
                        <td class="py-2 px-3">
                        <div style="display:flex;gap:.5rem;align-items:center">
                            @php
                                $selectedProductName = '';
                                if (isset($it) && !empty($it['producto_id']) && isset($products)) {
                                    $found = $products->firstWhere('id', $it['producto_id']);
                                    $selectedProductName = $found?->nombre ?? '';
                                }
                            @endphp
                            <input list="products-list" class="product-dropdown" data-row="{{ $i }}" placeholder="— Producto —"
                                   style="min-width:140px;padding:.25rem;border-radius:.375rem;background:transparent;color:#fff;border:1px solid rgba(255,255,255,.06)"
                                   value="{{ $selectedProductName }}">
                            <input type="text" name="descripcion_{{ $i }}" class="item-desc w-full bg-transparent border-b border-transparent
                                focus:border-white/20 text-white text-xs outline-none py-1 transition-all" placeholder="{{ $i === 1 ? 'Ej: Alquiler de terraza + sonido' : '' }}" value="{{ $descVal }}">
                            <input type="hidden" class="item-product-id" name="producto_id_{{ $i }}" value="{{ $it['producto_id'] ?? '' }}">
                        </div>
                       </td>

                        {{-- Cantidad --}}
                        <td class="py-2 px-3">
                        <input type="number" name="cantidad_{{ $i }}" min="0"
                            class="item-cant w-full bg-transparent border-b border-transparent
                                focus:border-white/20 text-white text-xs text-center outline-none py-1 transition-all"
                            placeholder="0" oninput="calcularFila({{ $i }})" value="{{ $cantVal }}">
                       </td>

                        {{-- Vr. Unitario --}}
                        <td class="py-2 px-3">
                        <input type="number" name="vr_unitario_{{ $i }}" min="0" step="0.01"
                            class="item-precio w-full bg-transparent border-b border-transparent
                                focus:border-white/20 text-white text-xs text-right outline-none py-1 transition-all"
                            placeholder="$0" oninput="calcularFila({{ $i }})" value="{{ $precioVal }}">
                       </td>

                        {{-- Vr. Bruto (calculado) --}}
                            <td class="py-2 px-4 text-right">
                            <span id="bruto_{{ $i }}" class="text-white text-xs font-semibold">${{ $bruto }}</span>
                        </td>
                    </tr>
                    @endfor
                </tbody>
            </table>
            @if(isset($products) && $products->count() > 0)
                <datalist id="products-list">
                    @foreach($products as $p)
                        <option value="{{ $p->nombre }}" data-id="{{ $p->id }}" data-price="{{ $p->precio }}"></option>
                    @endforeach
                </datalist>
                <script>
                    window.PRODUCTS_DATA = {!! isset($products) ? $products->map(fn($x)=>['id'=>$x->id,'nombre'=>$x->nombre,'precio'=>$x->precio])->toJson() : '[]' !!};
                </script>
            @endif
        </div>

        {{-- ── Totales ─────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 md:grid-cols-2 border-t border-white/10">

            {{-- Izq: conteo y valor en letras --}}
            <div class="p-5 space-y-3 border-b md:border-b-0 md:border-r border-white/10">
                <div class="flex items-center gap-3">
                    <label class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant w-28 shrink-0">
                        Total Ítems:
                    </label>
                    <span id="total-items-count" class="text-white text-sm font-bold">0</span>
                </div>
                <div class="flex items-start gap-3">
                    <label class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant w-28 shrink-0 mt-0.5">
                        Valor en Letras:
                    </label>
                    <span id="valor-letras" class="text-on-surface-variant text-xs italic flex-1">—</span>
                </div>
            </div>

            {{-- Der: resumen financiero --}}
            <div class="p-5 space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">Total Bruto</span>
                    <span id="total-bruto" class="text-white text-sm font-bold">$0</span>
                </div>
                <div class="flex justify-between items-center pt-3 border-t border-white/10">
                    <span class="text-sm font-black uppercase tracking-widest text-white">Total a Pagar</span>
                    <span id="total-pagar" class="text-xl font-black text-primary">$0</span>
                </div>
            </div>
        </div>

        {{-- ── Observaciones ───────────────────────────────────── --}}
        <div class="border-t border-white/10 p-5">
            <label class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant block mb-2">
                Observaciones:
            </label>
                 <textarea id="observaciones" name="observaciones" rows="2"
                     class="w-full bg-surface-container-highest border border-white/10 rounded-xl
                         px-4 py-3 text-white text-sm focus:outline-none focus:border-primary transition-all resize-none"
                     placeholder="Observaciones del evento...">{{ $factura->observaciones ?? '' }}</textarea>
        </div>

        {{-- ── Botones de acción ───────────────────────────────── --}}
        <div class="border-t border-white/10 p-5 flex flex-wrap gap-3 justify-end no-print">
            <button type="button" onclick="limpiarFormulario()"
                    class="flex items-center gap-2 px-5 py-3 rounded-xl border border-white/10 text-slate-400
                           hover:bg-white/5 hover:text-white transition-all text-xs font-black uppercase tracking-widest">
                <span class="material-symbols-outlined text-base">delete_sweep</span> Limpiar
            </button>
            @if(isset($factura) && $factura)
                <button type="button" id="btn-editar" onclick="guardarFactura()"
                        class="flex items-center gap-2 px-5 py-3 rounded-xl bg-amber-600 border border-amber-700
                               text-white hover:bg-amber-500 hover:text-on-primary transition-all text-xs font-black uppercase tracking-widest">
                    <span class="material-symbols-outlined text-base">edit</span> Editar
                </button>
            @else
                <button type="button" id="btn-guardar" onclick="guardarFactura()"
                        class="flex items-center gap-2 px-5 py-3 rounded-xl bg-surface-container-highest border border-white/10
                               text-white hover:bg-primary hover:text-on-primary transition-all text-xs font-black uppercase tracking-widest">
                    <span class="material-symbols-outlined text-base">save</span> Guardar
                </button>
            @endif
            <button type="button" onclick="imprimirFactura()"
                    class="flex items-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-br from-primary to-primary-container
                           text-on-primary-container font-black uppercase tracking-widest text-xs
                           hover:scale-95 transition-all shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined text-base">print</span> Imprimir Factura
            </button>
        </div>

    </div>{{-- /factura-alquiler --}}

</div>{{-- /page --}}
@endsection

@push('scripts')
    @vite('resources/js/pages/alquiler.js')
    @if(isset($factura) && $factura)
        <script>
            window.ALQUILER_EDITING = true;
            window.ALQUILER_FACTURA_ID = '{{ $factura->id }}';
        </script>
    @endif
@endpush
