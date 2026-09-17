@extends('layouts.portal')

@section('title', 'Mis paquetes')
@section('navbar-title', 'Mis paquetes')

@section('content')
@php
    $estadoBadge = function ($estado) {
        return match ($estado) {
            'recibido' => 'bg-amber-200 text-amber-900',
            'entregado' => 'bg-emerald-200 text-emerald-900',
            'en_transito', 'en_camino' => 'bg-sky-200 text-sky-900',
            'en_aduana' => 'bg-violet-200 text-violet-900',
            'listo_entrega' => 'bg-orange-200 text-orange-900',
            default => 'bg-slate-200 text-slate-900',
        };
    };
@endphp
<div class="mx-auto w-full max-w-[1400px] space-y-6 sm:space-y-8">
    {{-- Stats estilo inventario --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-5 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#15537c]/10 text-[#15537c] text-xl sm:h-14 sm:w-14 sm:text-2xl"><i class="fas fa-boxes"></i></div>
                <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 sm:text-sm">Total</p>
                    <p class="text-xl font-bold text-slate-800 sm:text-2xl">{{ $totalPaquetes }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600 text-xl sm:h-14 sm:w-14 sm:text-2xl"><i class="fas fa-check-circle"></i></div>
                <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 sm:text-sm">Recibidos</p>
                    <p class="text-xl font-bold text-slate-800 sm:text-2xl">{{ $totalRecibidos }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 col-span-2 lg:col-span-1">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 text-xl sm:h-14 sm:w-14 sm:text-2xl"><i class="fas fa-box-open"></i></div>
                <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 sm:text-sm">Entregados</p>
                    <p class="text-xl font-bold text-slate-800 sm:text-2xl">{{ $totalEntregados }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
            <div class="min-w-0 flex-1 sm:min-w-[220px]">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Buscar</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Tracking o guía"
                    class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
            </div>
            <div class="w-full sm:w-44">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Estado</label>
                <select name="estado" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
                    <option value="">Todos</option>
                    @foreach(['recibido','en_transito','en_aduana','listo_entrega','entregado','pendiente'] as $est)
                        <option value="{{ $est }}" @selected(request('estado') === $est)>{{ ucfirst(str_replace('_',' ', $est)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-[#15537c] px-5 py-2.5 text-base font-medium text-white hover:bg-[#0f3d5c] sm:flex-none">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="{{ route('portal.paquetes.index') }}" class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-slate-300 px-5 py-2.5 text-base font-medium text-slate-600 hover:bg-slate-50 sm:flex-none">Limpiar</a>
            </div>
        </form>
    </div>

    {{-- Toggle tabla / tarjetas --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="inline-flex rounded-xl border border-slate-200 bg-white p-1 shadow-sm">
            <button type="button" id="viewTable" class="view-toggle rounded-lg px-3 py-2 text-sm font-medium text-white bg-[#15537c] sm:px-4 sm:py-2.5 sm:text-base">
                <i class="fas fa-list mr-1 sm:mr-2"></i>Tabla
            </button>
            <button type="button" id="viewGrid" class="view-toggle rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 sm:px-4 sm:py-2.5 sm:text-base">
                <i class="fas fa-th-large mr-1 sm:mr-2"></i>Tarjetas
            </button>
        </div>
        <p class="text-sm text-slate-500">{{ $paquetes->total() }} paquete(s)</p>
    </div>

    {{-- Tabla (desktop) --}}
    <div id="containerTable" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] border-collapse text-left text-base text-black">
                <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                    <tr>
                        <th class="px-4 py-2 font-semibold text-center">Guía</th>
                        <th class="px-4 py-2 font-semibold text-center">Tracking</th>
                        <th class="px-4 py-2 font-semibold text-center">Servicio</th>
                        <th class="px-4 py-2 font-semibold text-center">Peso</th>
                        <th class="px-4 py-2 font-semibold text-center">Estado</th>
                        <th class="px-4 py-2 font-semibold text-center">Ingreso</th>
                        <th class="px-4 py-2 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paquetes as $p)
                    <tr class="border-b border-slate-100 {{ $loop->iteration % 2 === 0 ? 'bg-slate-50' : 'bg-white' }} hover:bg-slate-100">
                        <td class="px-4 py-1.5 text-center"><code class="rounded bg-slate-100 px-1.5 py-0.5 text-sm font-medium">{{ $p->numero_guia ?: '—' }}</code></td>
                        <td class="px-4 py-1.5 text-center font-mono text-sm font-medium text-[#15537c]">{{ $p->tracking_codigo ?: '—' }}</td>
                        <td class="px-4 py-1.5 text-center"><span class="rounded-md bg-slate-100 px-2 py-0.5 text-sm font-medium">{{ $p->servicio->tipo_servicio ?? '—' }}</span></td>
                        <td class="px-4 py-1.5 text-center font-medium whitespace-nowrap">{{ number_format($p->peso_lb ?? 0, 2) }} lb</td>
                        <td class="px-4 py-1.5 text-center">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-sm font-semibold {{ $estadoBadge($p->estado) }}">{{ ucfirst(str_replace('_',' ', $p->estado)) }}</span>
                        </td>
                        <td class="px-4 py-1.5 text-center font-medium">{{ \Carbon\Carbon::parse($p->fecha_ingreso)->format('d/m/Y') }}</td>
                        <td class="px-4 py-1.5 text-right">
                            <a href="{{ route('portal.paquetes.show', $p->id) }}" class="rounded-lg p-2 text-slate-700 hover:bg-slate-100 hover:text-[#15537c]" title="Ver"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-slate-600">No hay paquetes con esos filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tarjetas (móvil / toggle) --}}
    <div id="containerGrid" class="hidden">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse($paquetes as $p)
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-lg font-semibold text-black">{{ $p->tracking_codigo ?: $p->numero_guia ?: ('#'.$p->id) }}</p>
                        <p class="mt-1 text-sm font-medium text-slate-700">{{ $p->servicio->tipo_servicio ?? '—' }} · {{ number_format($p->peso_lb ?? 0, 2) }} lb</p>
                    </div>
                    <span class="shrink-0 rounded-full px-3 py-1 text-sm font-semibold {{ $estadoBadge($p->estado) }}">{{ ucfirst(str_replace('_',' ', $p->estado)) }}</span>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 text-sm">
                    <div>
                        <p class="font-medium text-slate-500">Guía</p>
                        <p class="font-mono font-semibold">{{ $p->numero_guia ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="font-medium text-slate-500">Ingreso</p>
                        <p class="font-semibold">{{ \Carbon\Carbon::parse($p->fecha_ingreso)->format('d/m/Y') }}</p>
                    </div>
                </div>
                <a href="{{ route('portal.paquetes.show', $p->id) }}" class="mt-4 block rounded-lg bg-[#15537c] py-2.5 text-center text-sm font-semibold text-white hover:bg-[#0f3d5c]">Ver detalle</a>
            </div>
            @empty
            <div class="col-span-full rounded-xl border border-slate-200 bg-white p-12 text-center text-slate-600">No hay paquetes con esos filtros.</div>
            @endforelse
        </div>
    </div>

    @if($paquetes->hasPages())
    <div class="flex justify-center pt-2">{{ $paquetes->links() }}</div>
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
    if (!table || !grid || !btnTable || !btnGrid) return;

    function showTable() {
        table.classList.remove('hidden');
        grid.classList.add('hidden');
        btnTable.classList.add('bg-[#15537c]', 'text-white');
        btnTable.classList.remove('text-slate-600');
        btnGrid.classList.remove('bg-[#15537c]', 'text-white');
        btnGrid.classList.add('text-slate-600');
        try { localStorage.setItem('portalPaquetesView', 'table'); } catch (e) {}
    }
    function showGrid() {
        grid.classList.remove('hidden');
        table.classList.add('hidden');
        btnGrid.classList.add('bg-[#15537c]', 'text-white');
        btnGrid.classList.remove('text-slate-600');
        btnTable.classList.remove('bg-[#15537c]', 'text-white');
        btnTable.classList.add('text-slate-600');
        try { localStorage.setItem('portalPaquetesView', 'grid'); } catch (e) {}
    }

    btnTable.addEventListener('click', showTable);
    btnGrid.addEventListener('click', showGrid);

    // En móvil por defecto tarjetas; en desktop tabla (salvo preferencia guardada)
    var saved = null;
    try { saved = localStorage.getItem('portalPaquetesView'); } catch (e) {}
    if (saved === 'grid' || (!saved && window.innerWidth < 768)) showGrid();
    else showTable();
})();
</script>
@endpush
