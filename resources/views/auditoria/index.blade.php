@extends('layouts.app-new')

@section('title', 'Auditoría - CH Logistics')
@section('navbar-title', 'Auditoría')

@section('content')
@php
    $etiquetaModulo = fn (string $m) => \App\Http\Controllers\AuditoriaController::etiquetaModulo($m);
@endphp
<div class="mx-auto w-full max-w-[1400px] space-y-8">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Auditoría</h1>
        <p class="mt-1 text-sm text-slate-500">Registro de cambios en todos los módulos: quién, cuándo y qué se hizo.</p>
    </div>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium uppercase tracking-wide text-slate-500">Total</p>
            <p class="mt-1 text-2xl font-bold text-slate-800">{{ $logs->total() }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium uppercase tracking-wide text-slate-500">Altas</p>
            <p class="mt-1 text-2xl font-bold text-emerald-700">{{ $creaciones }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium uppercase tracking-wide text-slate-500">Ediciones</p>
            <p class="mt-1 text-2xl font-bold text-amber-700">{{ $ediciones }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium uppercase tracking-wide text-slate-500">Bajas</p>
            <p class="mt-1 text-2xl font-bold text-rose-700">{{ $eliminaciones }}</p>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('auditoria.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="min-w-[160px] flex-1 max-w-xs">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Usuario</label>
                <input type="text" name="usuario" value="{{ request('usuario') }}" placeholder="Nombre o correo"
                    class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
            </div>
            <div class="w-44">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Módulo</label>
                <select name="modulo" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
                    <option value="">Todos</option>
                    @foreach($modulos as $mod)
                        <option value="{{ $mod }}" @selected(request('modulo') === $mod)>{{ $etiquetaModulo($mod) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-40">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Acción</label>
                <select name="accion" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
                    <option value="">Todas</option>
                    @foreach(['crear' => 'Crear', 'editar' => 'Editar', 'eliminar' => 'Eliminar'] as $val => $lab)
                        <option value="{{ $val }}" @selected(request('accion') === $val)>{{ $lab }}</option>
                    @endforeach
                </select>
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
            <a href="{{ route('auditoria.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-5 py-2.5 text-base font-medium text-slate-600 hover:bg-slate-50">Limpiar</a>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm text-slate-800">
                <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                    <tr>
                        <th class="px-4 py-2 font-semibold">Fecha</th>
                        <th class="px-4 py-2 font-semibold">Usuario</th>
                        <th class="px-4 py-2 font-semibold">Módulo</th>
                        <th class="px-4 py-2 font-semibold">Acción</th>
                        <th class="px-4 py-2 font-semibold">Resumen</th>
                        <th class="px-4 py-2 font-semibold text-right">Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr class="border-b border-slate-100 {{ $loop->iteration % 2 === 0 ? 'bg-slate-50' : 'bg-white' }} hover:bg-slate-100">
                        <td class="whitespace-nowrap px-4 py-2 font-medium">
                            {{ $log->fecha ? \Carbon\Carbon::parse($log->fecha)->format('d/m/Y H:i') : '—' }}
                        </td>
                        <td class="px-4 py-2">{{ $log->usuario->nombre ?? $log->usuario->email ?? 'Sistema' }}</td>
                        <td class="px-4 py-2">
                            <span class="inline-flex rounded-full bg-[#15537c]/10 px-2 py-0.5 text-xs font-semibold text-[#15537c]">{{ $etiquetaModulo($log->modulo) }}</span>
                        </td>
                        <td class="px-4 py-2">
                            @if($log->accion === 'crear')
                                <span class="inline-flex rounded-full bg-emerald-200 px-2 py-0.5 text-xs font-semibold text-emerald-900">Crear</span>
                            @elseif($log->accion === 'eliminar')
                                <span class="inline-flex rounded-full bg-rose-200 px-2 py-0.5 text-xs font-semibold text-rose-900">Eliminar</span>
                            @else
                                <span class="inline-flex rounded-full bg-amber-200 px-2 py-0.5 text-xs font-semibold text-amber-900">Editar</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-slate-700">{{ $log->resumen }}</td>
                        <td class="px-4 py-2 text-right">
                            <button type="button" onclick="openLogModal('log-content-{{ $log->id }}')" class="rounded-lg p-2 text-slate-700 hover:bg-slate-100 hover:text-[#15537c]" title="Ver detalle"><i class="fas fa-eye"></i></button>
                            <div id="log-content-{{ $log->id }}" class="hidden">
                                @if(($log->fuente ?? '') === 'inventario')
                                    @if($log->accion === 'crear')
                                        <p><strong>Guía:</strong> {{ $log->detalle['despues']['numero_guia'] ?? $log->guia ?? '—' }}</p>
                                        <p><strong>Estado:</strong> {{ $log->detalle['despues']['estado'] ?? '—' }}</p>
                                        <p><strong>Peso:</strong> {{ $log->detalle['despues']['peso_lb'] ?? '—' }} lb</p>
                                    @else
                                        <p class="mb-2 font-semibold">Cambios en inventario</p>
                                        @foreach(($log->detalle['despues'] ?? []) as $campo => $nuevo)
                                            @php $antes = $log->detalle['antes'][$campo] ?? null; @endphp
                                            @if($antes != $nuevo && !in_array($campo, ['created_at','updated_at','created_by','updated_by','id'], true))
                                                <p><span class="font-semibold text-[#15537c]">{{ str_replace('_', ' ', $campo) }}</span>: {{ is_scalar($antes) ? $antes : '—' }} → {{ is_scalar($nuevo) ? $nuevo : '—' }}</p>
                                            @endif
                                        @endforeach
                                    @endif
                                @else
                                    <p><strong>Ruta:</strong> {{ $log->detalle['ruta'] ?? '—' }}</p>
                                    <p><strong>URL:</strong> {{ $log->detalle['url'] ?? '—' }}</p>
                                    <p><strong>Método:</strong> {{ $log->detalle['metodo'] ?? '—' }}</p>
                                    <p><strong>IP:</strong> {{ $log->detalle['ip'] ?? '—' }}</p>
                                    @if(!empty($log->detalle['params']))
                                        <p class="mt-3 font-semibold">Parámetros</p>
                                        <pre class="mt-1 overflow-x-auto rounded-lg bg-slate-50 p-3 text-xs">{{ json_encode($log->detalle['params'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    @endif
                                    @if(!empty($log->detalle['payload']))
                                        <p class="mt-3 font-semibold">Datos enviados</p>
                                        <pre class="mt-1 overflow-x-auto rounded-lg bg-slate-50 p-3 text-xs">{{ json_encode($log->detalle['payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-slate-500">Aún no hay movimientos registrados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($logs->hasPages())
    <div class="flex justify-center pt-2">
        {{ $logs->links('vendor.pagination.custom') }}
    </div>
    @endif
</div>

<div id="logDetailModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-hidden="true">
    <div class="flex min-h-full items-center justify-center p-6">
        <div class="fixed inset-0 bg-slate-900/50" onclick="closeLogModal()"></div>
        <div class="relative w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <h3 class="text-lg font-semibold text-slate-800">Detalle de auditoría</h3>
                <button type="button" onclick="closeLogModal()" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100">&times;</button>
            </div>
            <div id="logDetailModalBody" class="mt-4 max-h-96 overflow-y-auto text-sm text-slate-700"></div>
            <div class="mt-4 flex justify-end">
                <button type="button" onclick="closeLogModal()" class="rounded-lg border border-slate-300 px-4 py-2 text-slate-600 hover:bg-slate-50">Cerrar</button>
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
@endsection
