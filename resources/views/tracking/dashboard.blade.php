@extends('layouts.app-new')

@section('title', 'Dashboard de Tracking - CH LOGISTICS ERP')
@section('navbar-title', 'Dashboard Tracking')

@section('content')
<div class="mx-auto w-full max-w-[1400px] space-y-8">
    @if (session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-base text-emerald-800" role="alert">
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    {{-- Barra: título + acciones --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Dashboard de Tracking</h1>
            <p class="mt-1 text-slate-600">Monitoreo y control de seguimientos con temporizadores</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('tracking.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2.5 text-base font-medium text-slate-600 hover:bg-slate-50"><i class="fas fa-list"></i> Ver Todos</a>
            <a href="{{ route('tracking.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2.5 text-base font-medium text-emerald-800 hover:bg-emerald-100">Completados</a>
            <a href="{{ route('tracking.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#15537c] px-5 py-2.5 text-base font-semibold text-white shadow-sm hover:bg-[#0f3d5c]"><i class="fas fa-plus"></i> Nuevo Tracking</a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-5 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-[#15537c]/10 text-[#15537c] text-2xl"><i class="fas fa-calendar-alt"></i></div>
                <div>
                    <p class="text-sm font-medium uppercase tracking-wide text-slate-500">Total</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $totalTrackings }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-amber-100 text-amber-600 text-2xl"><i class="fas fa-clock"></i></div>
                <div>
                    <p class="text-sm font-medium uppercase tracking-wide text-slate-500">Pendientes</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $trackingsPendientes }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-red-100 text-red-600 text-2xl"><i class="fas fa-exclamation-triangle"></i></div>
                <div>
                    <p class="text-sm font-medium uppercase tracking-wide text-slate-500">Vencidos</p>
                    <p class="text-2xl font-bold text-red-700">{{ $trackingsVencidos }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 text-2xl"><i class="fas fa-check-circle"></i></div>
                <div>
                    <p class="text-sm font-medium uppercase tracking-wide text-slate-500">Completados</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $trackingsCompletados }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Búsqueda rápida --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <label class="mb-2 block text-sm font-medium text-slate-600">Buscar por código</label>
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="codigoTracking" class="w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-4 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" placeholder="Código de tracking...">
            </div>
            <button type="button" onclick="buscarTracking()" class="inline-flex items-center gap-2 rounded-lg bg-[#15537c] px-5 py-2.5 text-base font-medium text-white hover:bg-[#0f3d5c]"><i class="fas fa-search"></i> Buscar</button>
            <button type="button" onclick="cargarProximosVencer()" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2.5 text-base font-medium text-slate-600 hover:bg-slate-50"><i class="fas fa-clock"></i> Próximos a Vencer</button>
        </div>
        <div id="resultadoBusqueda" class="mt-4"></div>
    </div>

    {{-- Trackings vencidos --}}
    @if($trackingsVencidos > 0)
    <div class="overflow-hidden rounded-xl border-2 border-red-200 bg-white shadow-sm">
        <div class="border-b border-red-200 bg-red-50 px-5 py-3 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-red-800"><i class="fas fa-exclamation-triangle mr-2"></i>Trackings Vencidos</h2>
            <span class="rounded-full bg-red-200 px-3 py-1 text-sm font-bold text-red-900">{{ $trackingsVencidos }}</span>
        </div>
        <div class="p-5">
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                <strong>Atención:</strong> Hay {{ $trackingsVencidos }} tracking(s) vencidos.
                <a href="{{ route('tracking.index') }}" class="font-semibold text-[#15537c] hover:underline ml-1">Ver lista</a>
            </div>
            @if($trackingsVencidosList->count() > 0)
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($trackingsVencidosList->take(6) as $tracking)
                <div class="rounded-xl border-2 border-red-200 bg-white p-4">
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <span class="font-semibold text-[#15537c]">{{ $tracking->tracking_codigo }}</span>
                        <span class="rounded-full bg-red-200 px-2 py-0.5 text-xs font-bold text-red-900">VENCIDO</span>
                    </div>
                    <p class="text-sm text-slate-600 mb-1"><strong>Cliente:</strong> {{ $tracking->cliente->nombre_completo ?? 'N/D' }}</p>
                    <p class="text-sm text-red-700 mb-3">Vencido: {{ \Carbon\Carbon::parse($tracking->recordatorio_fecha)->format('d/m/Y H:i') }}</p>
                    <div class="flex gap-2">
                        <a href="{{ route('tracking.show', $tracking) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50"><i class="fas fa-eye mr-1"></i>Ver</a>
                        <button type="button" onclick="marcarCompletado({{ $tracking->id }})" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-emerald-700"><i class="fas fa-check mr-1"></i>Completar</button>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Próximos a vencer --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-white px-5 py-3 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-amber-800"><i class="fas fa-clock mr-2"></i>Próximos a Vencer (7 días)</h2>
            <span class="rounded-full bg-amber-100 px-3 py-1 text-sm font-bold text-amber-900" id="contadorProximos">{{ $proximosVencer->count() }}</span>
        </div>
        <div class="p-5">
            <div id="proximosVencerList" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($proximosVencer as $tracking)
                <div class="rounded-xl border border-amber-200 bg-amber-50/50 p-4">
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <span class="font-semibold text-[#15537c]">{{ $tracking->tracking_codigo }}</span>
                        <span class="rounded-full bg-amber-200 px-2 py-0.5 text-xs font-semibold text-amber-900">{{ \Carbon\Carbon::parse($tracking->recordatorio_fecha)->diffForHumans() }}</span>
                    </div>
                    <p class="text-sm text-slate-600 mb-1"><strong>Cliente:</strong> {{ $tracking->cliente->nombre_completo ?? 'N/D' }}</p>
                    <p class="text-sm text-slate-700 mb-3">Vence: {{ \Carbon\Carbon::parse($tracking->recordatorio_fecha)->format('d/m/Y H:i') }}</p>
                    <div class="flex gap-2">
                        <a href="{{ route('tracking.show', $tracking) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50"><i class="fas fa-eye mr-1"></i>Ver</a>
                        <button type="button" onclick="marcarCompletado({{ $tracking->id }})" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-emerald-700"><i class="fas fa-check mr-1"></i>Completar</button>
                    </div>
                </div>
                @empty
                <div class="col-span-full rounded-xl border border-slate-200 bg-slate-50 py-12 text-center text-slate-600">
                    <i class="fas fa-check-circle text-4xl text-emerald-500 mb-3 block"></i>
                    <p>No hay trackings próximos a vencer</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Temporizadores activos --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-800 mb-4"><i class="fas fa-stopwatch mr-2 text-[#15537c]"></i>Temporizadores activos</h2>
        <div id="temporizadoresActivos" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {{-- Opcional: contenido dinámico --}}
        </div>
    </div>
</div>

@push('scripts')
<script>
function buscarTracking() {
    var codigo = document.getElementById('codigoTracking').value.trim();
    if (!codigo) {
        mostrarAlerta('Ingresa un código de tracking', 'warning');
        return;
    }
    var token = document.querySelector('meta[name="csrf-token"]');
    fetch('/tracking/buscar?codigo=' + encodeURIComponent(codigo))
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var div = document.getElementById('resultadoBusqueda');
            if (data.success) {
                var t = data.tracking;
                div.innerHTML = '<div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-emerald-800">' +
                    '<p class="font-semibold">' + t.tracking_codigo + '</p>' +
                    '<p class="text-sm"><strong>Cliente:</strong> ' + (t.cliente ? (t.cliente.nombre_completo || t.cliente.nombre) : 'N/D') + '</p>' +
                    '<p class="text-sm"><strong>Estado:</strong> ' + t.estado + '</p>' +
                    '<p class="text-sm"><strong>Vence:</strong> ' + (t.recordatorio_fecha ? new Date(t.recordatorio_fecha).toLocaleString() : '-') + '</p>' +
                    '<a href="/tracking/' + t.id + '" class="mt-2 inline-flex items-center gap-1 rounded-lg bg-[#15537c] px-3 py-1.5 text-sm font-medium text-white hover:bg-[#0f3d5c]"><i class="fas fa-eye"></i> Ver detalles</a>' +
                    '</div>';
            } else {
                div.innerHTML = '<div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-amber-800"><i class="fas fa-exclamation-triangle mr-2"></i>' + (data.message || 'No encontrado') + '</div>';
            }
        })
        .catch(function() {
            mostrarAlerta('Error al buscar', 'danger');
        });
}
function cargarProximosVencer() {
    fetch('/tracking/proximos-vencer')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var el = document.getElementById('contadorProximos');
            if (el) el.textContent = Array.isArray(data) ? data.length : 0;
            mostrarAlerta('Próximos a vencer: ' + (Array.isArray(data) ? data.length : 0), 'info');
        })
        .catch(function() {});
}
function verTracking(id) {
    window.location.href = '/tracking/' + id;
}
function marcarCompletado(id) {
    if (!confirm('¿Marcar este tracking como completado?')) return;
    var token = document.querySelector('meta[name="csrf-token"]');
    var headers = { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
    if (token) headers['X-CSRF-TOKEN'] = token.getAttribute('content');
    fetch('/tracking/' + id + '/completar', { method: 'POST', headers: headers })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                mostrarAlerta('Tracking completado', 'success');
                setTimeout(function() { location.reload(); }, 1000);
            } else {
                mostrarAlerta('No se pudo completar', 'danger');
            }
        })
        .catch(function() {
            mostrarAlerta('Error al actualizar', 'danger');
        });
}
function mostrarAlerta(mensaje, tipo) {
    var classes = {
        success: 'rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800',
        warning: 'rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-amber-800',
        danger: 'rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-red-800',
        info: 'rounded-xl border border-sky-200 bg-sky-50 px-5 py-4 text-sky-800'
    };
    var wrap = document.createElement('div');
    wrap.className = (classes[tipo] || classes.info) + ' mb-4';
    wrap.textContent = mensaje;
    var container = document.querySelector('.mx-auto.w-full.max-w-\\[1400px\\]');
    if (container && container.firstChild) {
        container.insertBefore(wrap, container.firstChild);
        setTimeout(function() { wrap.remove(); }, 5000);
    } else {
        alert(mensaje);
    }
}
document.addEventListener('DOMContentLoaded', function() {
    var c = document.getElementById('contadorProximos');
    if (c) c.textContent = {{ $proximosVencer->count() }};
});
</script>
@endpush
@endsection
