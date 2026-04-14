@extends('layouts.app-new')

@section('title', 'Lista de Trackings - CH LOGISTICS ERP')
@section('navbar-title', 'Trackings')

@section('content')
<div class="mx-auto w-full max-w-[1400px] space-y-8">
    @if (session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-base text-emerald-800" role="alert">
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    {{-- Filtros (client-side) --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-end gap-4">
            <div class="w-44">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Estado</label>
                <select id="filtroEstado" class="w-full appearance-none rounded-lg border border-slate-300 bg-white bg-[length:1.25rem] bg-[right_0.75rem_center] bg-no-repeat px-4 py-2.5 pr-10 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%2364758b%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 9l-7 7-7-7%22/%3E%3C/svg%3E');">
                    <option value="">Todos los estados</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="en_proceso">En Proceso</option>
                    <option value="completado">Completado</option>
                    <option value="vencido">Vencido</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>
            <div class="min-w-[180px] flex-1 max-w-xs">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Cliente</label>
                <select id="filtroCliente" class="w-full appearance-none rounded-lg border border-slate-300 bg-white bg-[length:1.25rem] bg-[right_0.75rem_center] bg-no-repeat px-4 py-2.5 pr-10 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%2364758b%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 9l-7 7-7-7%22/%3E%3C/svg%3E');">
                    <option value="">Todos los clientes</option>
                    @foreach($trackings->pluck('cliente.nombre_completo')->unique()->filter() as $nombreCliente)
                        <option value="{{ $nombreCliente }}">{{ $nombreCliente }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-44">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Vencimiento</label>
                <select id="filtroFecha" class="w-full appearance-none rounded-lg border border-slate-300 bg-white bg-[length:1.25rem] bg-[right_0.75rem_center] bg-no-repeat px-4 py-2.5 pr-10 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%2364758b%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 9l-7 7-7-7%22/%3E%3C/svg%3E');">
                    <option value="">Todas las fechas</option>
                    <option value="hoy">Vence hoy</option>
                    <option value="semana">Vence esta semana</option>
                    <option value="mes">Vence este mes</option>
                    <option value="vencido">Ya venció</option>
                </select>
            </div>
            <button type="button" onclick="aplicarFiltros()" class="inline-flex items-center gap-2 rounded-lg bg-[#15537c] px-5 py-2.5 text-base font-medium text-white hover:bg-[#0f3d5c]"><i class="fas fa-search"></i> Aplicar</button>
        </div>
    </div>

    {{-- Barra: Volver, total, Nuevo, Exportar, Verificar --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('tracking.dashboard') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2.5 text-base font-medium text-slate-600 hover:bg-slate-50"><i class="fas fa-arrow-left"></i> Dashboard</a>
            <span class="text-sm font-medium text-slate-500">Total: <span class="font-bold text-slate-800">{{ $trackings->total() }}</span></span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="exportarTrackings()" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2.5 text-base font-medium text-slate-600 hover:bg-slate-50"><i class="fas fa-download"></i> Exportar</button>
            <button type="button" onclick="verificarRecordatorios()" class="inline-flex items-center gap-2 rounded-lg border border-amber-300 px-4 py-2.5 text-base font-medium text-amber-700 hover:bg-amber-50"><i class="fas fa-sync"></i> Verificar recordatorios</button>
            <a href="{{ route('tracking.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#15537c] px-5 py-2.5 text-base font-semibold text-white shadow-sm hover:bg-[#0f3d5c]"><i class="fas fa-plus"></i> Nuevo Tracking</a>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full table-fixed border-collapse text-left text-base text-black" id="trackingTable">
                <colgroup>
                    <col style="width:14%">
                    <col style="width:16%">
                    <col style="width:10%">
                    <col style="width:14%">
                    <col style="width:12%">
                    <col style="width:14%">
                    <col style="width:14%">
                </colgroup>
                <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                    <tr>
                        <th class="px-4 py-2 font-semibold">Código</th>
                        <th class="px-4 py-2 font-semibold text-center">Cliente</th>
                        <th class="px-4 py-2 font-semibold text-center">Estado</th>
                        <th class="px-4 py-2 font-semibold text-center">Temporizador</th>
                        <th class="px-4 py-2 font-semibold text-center">Vence</th>
                        <th class="px-4 py-2 font-semibold text-center">Creado por</th>
                        <th class="px-4 py-2 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trackings as $tracking)
                    <tr class="tracking-row border-b border-slate-100 {{ $loop->iteration % 2 === 0 ? 'bg-slate-50' : 'bg-white' }} hover:bg-slate-100"
                        data-estado="{{ $tracking->estado }}"
                        data-cliente="{{ $tracking->cliente->nombre_completo ?? '' }}"
                        data-fecha="{{ $tracking->recordatorio_fecha }}">
                        <td class="px-4 py-1.5">
                            <div class="truncate font-semibold text-[#15537c]" title="{{ $tracking->tracking_codigo }}{{ $tracking->nota ? ' — ' . $tracking->nota : '' }}">{{ $tracking->tracking_codigo }}</div>
                        </td>
                        <td class="px-4 py-1.5 text-center">
                            <div class="truncate font-medium text-black" title="{{ $tracking->cliente->nombre_completo ?? 'N/D' }}">{{ $tracking->cliente->nombre_completo ?? 'N/D' }}</div>
                        </td>
                        <td class="px-4 py-1.5 text-center">
                            @php
                                $badgeEstado = match($tracking->estado) {
                                    'pendiente' => 'bg-amber-200 text-amber-900',
                                    'en_proceso' => 'bg-sky-200 text-sky-900',
                                    'completado' => 'bg-emerald-200 text-emerald-900',
                                    'vencido' => 'bg-red-200 text-red-900',
                                    'cancelado' => 'bg-slate-200 text-slate-700',
                                    default => 'bg-slate-200 text-slate-700'
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-sm font-semibold {{ $badgeEstado }}">{{ ucfirst(str_replace('_', ' ', $tracking->estado)) }}</span>
                        </td>
                        <td class="px-4 py-1.5 text-center">
                            <div id="temporizador-{{ $tracking->id }}" class="temporizador-display text-sm">
                                @if($tracking->recordatorio_fecha && $tracking->estado != 'completado')
                                    @php $vencimiento = \Carbon\Carbon::parse($tracking->recordatorio_fecha); @endphp
                                    @if($vencimiento->isFuture())
                                        <span class="text-amber-700">
                                            <i class="fas fa-clock mr-1"></i>
                                            <span class="temporizador-text" data-fecha="{{ $tracking->recordatorio_fecha }}">{{ $vencimiento->diffForHumans() }}</span>
                                        </span>
                                    @else
                                        <span class="text-red-700"><i class="fas fa-exclamation-triangle mr-1"></i>Vencido</span>
                                    @endif
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-1.5 text-center font-medium text-slate-900 whitespace-nowrap">
                            {{ $tracking->recordatorio_fecha ? \Carbon\Carbon::parse($tracking->recordatorio_fecha)->format('d/m/Y H:i') : '—' }}
                        </td>
                        <td class="px-4 py-1.5 text-center">
                            <span class="text-slate-800">{{ $tracking->creador->name ?? ($tracking->creado_por ? 'ID: ' . $tracking->creado_por : '—') }}</span>
                        </td>
                        <td class="px-4 py-1.5 text-right">
                            <div class="inline-flex items-center justify-end gap-2">
                                <a href="{{ route('tracking.show', $tracking) }}" class="rounded-lg p-2 text-slate-700 hover:bg-slate-100 hover:text-[#15537c]" title="Ver"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('tracking.edit', $tracking) }}" class="rounded-lg p-2 text-slate-700 hover:bg-slate-100 hover:text-[#15537c]" title="Editar"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('tracking.destroy', $tracking) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg p-2 text-slate-700 hover:bg-red-50 hover:text-red-700" onclick="return confirm('¿Eliminar este tracking?')" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-base text-slate-700">No hay trackings. <a href="{{ route('tracking.create') }}" class="font-medium text-[#15537c] hover:underline">Crear uno</a>.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($trackings->hasPages())
    <div class="flex justify-center pt-4">
        {{ $trackings->links('vendor.pagination.custom') }}
    </div>
    @endif
</div>

@push('scripts')
<script>
function aplicarFiltros() {
    var estado = document.getElementById('filtroEstado').value;
    var cliente = document.getElementById('filtroCliente').value;
    var fecha = document.getElementById('filtroFecha').value;
    var filas = document.querySelectorAll('.tracking-row');
    filas.forEach(function(fila) {
        var show = true;
        if (estado && fila.dataset.estado !== estado) show = false;
        if (cliente && fila.dataset.cliente !== cliente) show = false;
        if (fecha && fila.dataset.fecha) {
            var venc = new Date(fila.dataset.fecha);
            var ahora = new Date();
            switch (fecha) {
                case 'hoy':
                    var d = new Date(ahora.getFullYear(), ahora.getMonth(), ahora.getDate());
                    if (venc < d || venc >= new Date(d.getTime() + 86400000)) show = false;
                    break;
                case 'semana':
                    var inicio = new Date(ahora.getTime() - ahora.getDay() * 86400000);
                    if (venc < inicio || venc >= new Date(inicio.getTime() + 7 * 86400000)) show = false;
                    break;
                case 'mes':
                    var m1 = new Date(ahora.getFullYear(), ahora.getMonth(), 1);
                    var m2 = new Date(ahora.getFullYear(), ahora.getMonth() + 1, 1);
                    if (venc < m1 || venc >= m2) show = false;
                    break;
                case 'vencido':
                    if (venc > ahora) show = false;
                    break;
            }
        }
        fila.style.display = show ? '' : 'none';
    });
}
function exportarTrackings() { alert('Exportación en desarrollo'); }
function verificarRecordatorios() {
    fetch('/tracking/verificar-recordatorios').then(function(r) { return r.json(); }).then(function(d) {
        if (d.success) { alert(d.message); location.reload(); }
    }).catch(function() { alert('Error al verificar'); });
}
setInterval(function() {
    document.querySelectorAll('.temporizador-text').forEach(function(el) {
        var f = new Date(el.dataset.fecha);
        var now = new Date();
        var diff = f - now;
        if (diff > 0) {
            var d = Math.floor(diff / 86400000), h = Math.floor((diff % 86400000) / 3600000), m = Math.floor((diff % 3600000) / 60000);
            el.textContent = (d > 0 ? d + 'd ' : '') + h + 'h ' + m + 'm';
        } else {
            el.textContent = 'Vencido';
            if (el.parentElement) el.parentElement.className = 'text-red-700';
        }
    });
}, 60000);
document.addEventListener('DOMContentLoaded', function() {
    ['filtroEstado', 'filtroCliente', 'filtroFecha'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('change', aplicarFiltros);
    });
});
</script>
@endpush
@endsection
