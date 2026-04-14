@php
function cambiosDiferenciaLogs($antes, $despues) {
    $traducciones = [
        'updated_at' => 'Actualizado el',
        'peso_lb' => 'Peso lb',
        'monto_calculado' => 'Monto calculado',
        'notas' => 'Notas',
        'estado' => 'Estado',
        'numero_guia' => 'Warehouse',
    ];
    $cambios = [];
    foreach ($despues ?? [] as $key => $valorNuevo) {
        $valorViejo = ($antes ?? [])[$key] ?? null;
        if ($valorViejo != $valorNuevo && !in_array($key, ['created_at','created_by','factura_id','cliente_id','servicio_id','updated_by','id','tracking_codigo'])) {
            $label = $traducciones[$key] ?? ucfirst(str_replace('_',' ',$key));
            if ($key === 'updated_at' && $valorViejo && $valorNuevo) {
                $valorViejo = \Carbon\Carbon::parse($valorViejo)->format('d/m/Y H:i');
                $valorNuevo = \Carbon\Carbon::parse($valorNuevo)->format('d/m/Y H:i');
            }
            $cambios[$label] = ['antes' => $valorViejo, 'despues' => $valorNuevo];
        }
    }
    return $cambios;
}
@endphp
@extends('layouts.app-new')

@section('title', 'Historial de Inventario - CH LOGISTICS ERP')
@section('navbar-title', 'Historial Inventario')

@section('content')
@if(auth()->check() && auth()->user()->rol === 'admin')
<div class="mx-auto w-full max-w-[1400px] space-y-8">
    @if (session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-base text-emerald-800" role="alert">
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-5 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-[#15537c]/10 text-[#15537c] text-2xl"><i class="fas fa-history"></i></div>
                <div>
                    <p class="text-sm font-medium uppercase tracking-wide text-slate-500">Total cambios</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $logs->total() }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 text-2xl"><i class="fas fa-plus"></i></div>
                <div>
                    <p class="text-sm font-medium uppercase tracking-wide text-slate-500">Creaciones</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $logs->where('accion','crear')->count() }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-amber-100 text-amber-600 text-2xl"><i class="fas fa-edit"></i></div>
                <div>
                    <p class="text-sm font-medium uppercase tracking-wide text-slate-500">Ediciones</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $logs->where('accion','editar')->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('logs_inventario.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="min-w-[160px] flex-1 max-w-xs">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Agente</label>
                <input type="text" name="agente" value="{{ request('agente') }}" placeholder="Nombre agente" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
            </div>
            <div class="w-36">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Acción</label>
                <select name="accion" class="w-full appearance-none rounded-lg border border-slate-300 bg-white bg-[length:1.25rem] bg-[right_0.75rem_center] bg-no-repeat px-4 py-2.5 pr-10 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%2364758b%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 9l-7 7-7-7%22/%3E%3C/svg%3E');">
                    <option value="">Todas</option>
                    <option value="crear" {{ request('accion')=='crear'?'selected':'' }}>Crear</option>
                    <option value="editar" {{ request('accion')=='editar'?'selected':'' }}>Editar</option>
                </select>
            </div>
            <div class="w-36">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Guía</label>
                <input type="text" name="warehouse" value="{{ request('warehouse') }}" placeholder="Guía" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
            </div>
            <div class="w-40">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Desde</label>
                <input type="date" name="desde" value="{{ request('desde') }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
            </div>
            <div class="w-40">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Hasta</label>
                <input type="date" name="hasta" value="{{ request('hasta') }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
            </div>
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-[#15537c] px-5 py-2.5 text-base font-medium text-white hover:bg-[#0f3d5c]"><i class="fas fa-search"></i> Filtrar</button>
            <a href="{{ route('logs_inventario.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-5 py-2.5 text-base font-medium text-slate-600 hover:bg-slate-50">Limpiar</a>
        </form>
    </div>

    {{-- Tabla --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full table-fixed border-collapse text-left text-base text-black">
                <colgroup>
                    <col style="width:14%">
                    <col style="width:14%">
                    <col style="width:10%">
                    <col style="width:12%">
                    <col style="width:48%">
                </colgroup>
                <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                    <tr>
                        <th class="px-4 py-2 font-semibold">Fecha</th>
                        <th class="px-4 py-2 font-semibold text-center">Agente</th>
                        <th class="px-4 py-2 font-semibold text-center">Acción</th>
                        <th class="px-4 py-2 font-semibold text-center">Guía</th>
                        <th class="px-4 py-2 font-semibold">Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr class="border-b border-slate-100 {{ $loop->iteration % 2 === 0 ? 'bg-slate-50' : 'bg-white' }} hover:bg-slate-100">
                        <td class="px-4 py-1.5 font-medium text-slate-900 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-1.5 text-center">{{ $log->usuario->nombre ?? 'N/A' }}</td>
                        <td class="px-4 py-1.5 text-center">
                            @if($log->accion === 'crear')
                                <span class="inline-flex items-center rounded-full bg-emerald-200 px-2 py-0.5 text-sm font-semibold text-emerald-900">Crear</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-amber-200 px-2 py-0.5 text-sm font-semibold text-amber-900">Editar</span>
                            @endif
                        </td>
                        <td class="px-4 py-1.5 text-center font-medium text-slate-900">{{ $log->inventario->numero_guia ?? '—' }}</td>
                        <td class="px-4 py-1.5">
                            <div class="flex items-center justify-between gap-2">
                                @if($log->accion === 'crear')
                                    <span class="text-sm font-semibold text-emerald-800"><i class="fas fa-plus-circle mr-1"></i>Paquete creado</span>
                                @else
                                    <span class="text-sm font-semibold text-amber-800"><i class="fas fa-edit mr-1"></i>Paquete editado</span>
                                @endif
                                <button type="button" onclick="openLogModal('log-content-{{ $log->id }}')" class="rounded-lg p-2 text-slate-700 hover:bg-slate-100 hover:text-[#15537c]" title="Ver detalles"><i class="fas fa-eye"></i></button>
                            </div>
                            <div id="log-content-{{ $log->id }}" class="hidden">
                                @if($log->accion === 'crear')
                                    <div class="space-y-2 text-left">
                                        <p><strong>Guía:</strong> {{ $log->despues['numero_guia'] ?? '—' }}</p>
                                        <p><strong>Estado:</strong> <span class="rounded bg-slate-200 px-1.5 py-0.5 text-sm">{{ ucfirst($log->despues['estado'] ?? '—') }}</span></p>
                                        <p><strong>Peso lb:</strong> {{ $log->despues['peso_lb'] ?? '—' }}</p>
                                        <p><strong>Notas:</strong> {{ $log->despues['notas'] ?? '—' }}</p>
                                        <p><strong>Fecha ingreso:</strong> {{ $log->despues['fecha_ingreso'] ?? '—' }}</p>
                                        <p><strong>Tarifa manual:</strong> {{ $log->despues['tarifa_manual'] ?? '—' }}</p>
                                        <p><strong>Monto calculado:</strong> {{ $log->despues['monto_calculado'] ?? '—' }}</p>
                                        <p><strong>Tracking:</strong> {{ $log->despues['tracking_codigo'] ?? '—' }}</p>
                                    </div>
                                @else
                                    @php $diffs = cambiosDiferenciaLogs($log->antes, $log->despues); @endphp
                                    @if(count($diffs))
                                        <ul class="space-y-2 text-left">
                                            @foreach($diffs as $campo => $val)
                                            <li><span class="font-semibold text-[#15537c]">{{ $campo }}</span>: <span class="rounded bg-slate-200 px-1">{{ $val['antes'] }}</span> → <span class="rounded bg-emerald-200 px-1">{{ $val['despues'] }}</span></li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-slate-500">No hay cambios relevantes.</p>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-12 text-center text-base text-slate-700">No hay registros de cambios en inventario.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($logs->hasPages())
    <div class="flex justify-center pt-4">
        {{ $logs->links('vendor.pagination.custom') }}
    </div>
    @endif
</div>

{{-- Modal único para detalle --}}
<div id="logDetailModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-hidden="true">
    <div class="flex min-h-full items-center justify-center p-6">
        <div class="fixed inset-0 bg-slate-900/50 transition-opacity" onclick="closeLogModal()"></div>
        <div class="relative w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <h3 class="text-lg font-semibold text-slate-800"><i class="fas fa-info-circle mr-2 text-[#15537c]"></i>Detalle</h3>
                <button type="button" onclick="closeLogModal()" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700">&times;</button>
            </div>
            <div id="logDetailModalBody" class="mt-4 max-h-96 overflow-y-auto text-sm text-slate-700"></div>
            <div class="mt-4 flex justify-end">
                <button type="button" onclick="closeLogModal()" class="rounded-lg border border-slate-300 px-4 py-2 text-base font-medium text-slate-600 hover:bg-slate-50">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openLogModal(contentId) {
    var el = document.getElementById(contentId);
    var body = document.getElementById('logDetailModalBody');
    var modal = document.getElementById('logDetailModal');
    if (el && body && modal) {
        body.innerHTML = el.innerHTML;
        modal.classList.remove('hidden');
    }
}
function closeLogModal() {
    document.getElementById('logDetailModal').classList.add('hidden');
}
</script>
@endpush
@else
<div class="mx-auto w-full max-w-[1400px]">
    <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-base text-red-800" role="alert">
        No tienes permiso para acceder a este módulo.
    </div>
</div>
@endif
@endsection
