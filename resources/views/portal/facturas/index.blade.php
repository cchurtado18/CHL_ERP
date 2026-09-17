@extends('layouts.portal')

@section('title', 'Mis facturas')
@section('navbar-title', 'Mis facturas')

@section('content')
<div class="mx-auto w-full max-w-[1400px] space-y-6 sm:space-y-8">
    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
            <div class="w-full sm:w-56">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Estado de pago</label>
                <select name="estado_pago" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
                    <option value="">Todos</option>
                    @foreach(['pendiente','parcial','pagado','entregado_pagado','entregado_sin_pagar'] as $est)
                        <option value="{{ $est }}" @selected(request('estado_pago') === $est)>{{ ucfirst(str_replace('_',' ', $est)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-[#15537c] px-5 py-2.5 text-base font-medium text-white hover:bg-[#0f3d5c] sm:flex-none">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="{{ route('portal.facturas.index') }}" class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-slate-300 px-5 py-2.5 text-base font-medium text-slate-600 hover:bg-slate-50 sm:flex-none">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="inline-flex rounded-xl border border-slate-200 bg-white p-1 shadow-sm">
            <button type="button" id="viewTable" class="rounded-lg bg-[#15537c] px-3 py-2 text-sm font-medium text-white sm:px-4 sm:text-base"><i class="fas fa-list mr-1"></i>Tabla</button>
            <button type="button" id="viewGrid" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 sm:px-4 sm:text-base"><i class="fas fa-th-large mr-1"></i>Tarjetas</button>
        </div>
        <p class="text-sm text-slate-500">{{ $facturas->total() }} factura(s)</p>
    </div>

    <div id="containerTable" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] border-collapse text-left text-base">
                <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                    <tr>
                        <th class="px-4 py-2 font-semibold">Folio</th>
                        <th class="px-4 py-2 font-semibold text-center">Fecha</th>
                        <th class="px-4 py-2 font-semibold text-center">Tipo</th>
                        <th class="px-4 py-2 font-semibold text-center">Monto</th>
                        <th class="px-4 py-2 font-semibold text-center">Pago</th>
                        <th class="px-4 py-2 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($facturas as $f)
                    <tr class="border-b border-slate-100 {{ $loop->iteration % 2 === 0 ? 'bg-slate-50' : 'bg-white' }} hover:bg-slate-100">
                        <td class="px-4 py-1.5 font-semibold">{{ $f->etiquetaFolio() }}</td>
                        <td class="px-4 py-1.5 text-center">{{ \Carbon\Carbon::parse($f->fecha_factura)->format('d/m/Y') }}</td>
                        <td class="px-4 py-1.5 text-center capitalize">{{ str_replace('_',' ', $f->tipo_factura ?? 'paqueteria') }}</td>
                        <td class="px-4 py-1.5 text-center font-semibold text-emerald-800">${{ number_format($f->monto_total, 2) }}</td>
                        <td class="px-4 py-1.5 text-center">
                            <span class="inline-flex rounded-full bg-slate-200 px-2 py-0.5 text-sm font-semibold capitalize text-slate-900">{{ str_replace('_',' ', $f->estado_pago) }}</span>
                        </td>
                        <td class="px-4 py-1.5 text-right">
                            <div class="inline-flex items-center gap-1">
                                <a href="{{ route('portal.facturas.show', $f->id) }}" class="rounded-lg p-2 text-slate-700 hover:bg-slate-100 hover:text-[#15537c]" title="Ver"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('portal.facturas.pdf', $f->id) }}" class="rounded-lg p-2 text-slate-700 hover:bg-slate-100 hover:text-[#15537c]" title="PDF"><i class="fas fa-file-pdf"></i></a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-slate-600">No se encontraron facturas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="containerGrid" class="hidden">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse($facturas as $f)
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-lg font-semibold text-slate-800">Folio {{ $f->etiquetaFolio() }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ \Carbon\Carbon::parse($f->fecha_factura)->format('d/m/Y') }}</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold capitalize">{{ str_replace('_',' ', $f->estado_pago) }}</span>
                </div>
                <p class="mt-4 text-2xl font-bold text-emerald-800">${{ number_format($f->monto_total, 2) }}</p>
                <p class="text-sm capitalize text-slate-500">{{ str_replace('_',' ', $f->tipo_factura ?? 'paqueteria') }}</p>
                <div class="mt-4 flex gap-2">
                    <a href="{{ route('portal.facturas.show', $f->id) }}" class="flex-1 rounded-lg bg-[#15537c] py-2.5 text-center text-sm font-semibold text-white hover:bg-[#0f3d5c]">Ver</a>
                    <a href="{{ route('portal.facturas.pdf', $f->id) }}" class="flex-1 rounded-lg border border-slate-300 py-2.5 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">PDF</a>
                </div>
            </div>
            @empty
            <div class="col-span-full rounded-xl border border-slate-200 bg-white p-12 text-center text-slate-600">No se encontraron facturas.</div>
            @endforelse
        </div>
    </div>

    @if($facturas->hasPages())
    <div class="flex justify-center">{{ $facturas->links() }}</div>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function() {
    var table = document.getElementById('containerTable');
    var grid = document.getElementById('containerGrid');
    var btnTable = document.getElementById('viewTable');
    var btnGrid = document.getElementById('viewGrid');
    if (!table || !grid) return;
    function showTable() {
        table.classList.remove('hidden'); grid.classList.add('hidden');
        btnTable.classList.add('bg-[#15537c]', 'text-white'); btnTable.classList.remove('text-slate-600');
        btnGrid.classList.remove('bg-[#15537c]', 'text-white'); btnGrid.classList.add('text-slate-600');
    }
    function showGrid() {
        grid.classList.remove('hidden'); table.classList.add('hidden');
        btnGrid.classList.add('bg-[#15537c]', 'text-white'); btnGrid.classList.remove('text-slate-600');
        btnTable.classList.remove('bg-[#15537c]', 'text-white'); btnTable.classList.add('text-slate-600');
    }
    btnTable.addEventListener('click', showTable);
    btnGrid.addEventListener('click', showGrid);
    if (window.innerWidth < 768) showGrid(); else showTable();
})();
</script>
@endpush
