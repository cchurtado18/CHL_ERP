@extends('layouts.app-new')

@section('title', 'Dashboard - CH Logistics')
@section('navbar-title', 'Dashboard')

@section('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">
@endsection

@section('content')
@php
    $usuario = auth()->user();
    $ahora = \Carbon\Carbon::now()->locale('es');
    $hora = (int) $ahora->format('G');
    $saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
    $primerNombre = \Illuminate\Support\Str::of($usuario->nombre ?? '')->trim()->explode(' ')->first();
    $esAdmin = $usuario && $usuario->rol === 'admin';

    $variacion = function ($actual, $anterior) {
        if ($anterior <= 0) {
            return $actual > 0 ? null : 0;
        }
        return round((($actual - $anterior) / $anterior) * 100);
    };
@endphp
<div class="dash mx-auto w-full max-w-[1400px] space-y-6">

    {{-- ===== Encabezado ===== --}}
    <section class="dash-hero relative overflow-hidden rounded-2xl px-6 py-6 text-white sm:px-8 sm:py-7">
        <div class="relative z-10 flex flex-wrap items-end justify-between gap-5">
            <div>
                <p class="text-sm font-semibold text-white/60">{{ \Illuminate\Support\Str::ucfirst($ahora->translatedFormat('l, j \d\e F \d\e Y')) }}</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight sm:text-3xl">
                    {{ $saludo }}{{ $primerNombre ? ', '.$primerNombre : '' }} <span class="inline-block origin-[70%_70%] animate-[wave_2s_ease-in-out_1]">👋</span>
                </h1>
                <p class="mt-1 text-[15px] text-white/70">Este es el resumen de CH Logistics.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if($usuario?->tienePermiso('inventario'))
                    <a href="{{ route('inventario.create') }}" class="dash-btn-amber inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-extrabold">
                        <i class="fas fa-plus"></i> Nuevo paquete
                    </a>
                @endif
                @if($usuario?->tienePermiso('facturacion'))
                    <a href="{{ route('facturacion.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-bold text-white hover:bg-white/15">
                        <i class="fas fa-file-invoice-dollar"></i> Nueva factura
                    </a>
                @endif
                <a href="{{ route('public.tracking') }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-bold text-white hover:bg-white/15">
                    <i class="fas fa-magnifying-glass"></i> Rastrear
                </a>
            </div>
        </div>
    </section>

    @if($esAdmin)

    {{-- ===== Este mes ===== --}}
    @php
        $kpis = [
            ['Paquetes del mes', number_format($mesActual['paquetes']), $variacion($mesActual['paquetes'], $mesAnterior['paquetes']), 'fa-box', 'bg-[#15537c]/10 text-[#15537c]'],
            ['Libras del mes', number_format($mesActual['libras'], 1).' lb', $variacion($mesActual['libras'], $mesAnterior['libras']), 'fa-weight-hanging', 'bg-sky-100 text-sky-700'],
            ['Monto del mes', '$'.number_format($mesActual['monto'], 2), $variacion($mesActual['monto'], $mesAnterior['monto']), 'fa-dollar-sign', 'bg-emerald-100 text-emerald-700'],
            ['Clientes con envíos', number_format($mesActual['clientes']), $variacion($mesActual['clientes'], $mesAnterior['clientes']), 'fa-user-check', 'bg-amber-100 text-amber-700'],
        ];
    @endphp
    <section>
        <div class="mb-3 flex items-baseline justify-between">
            <h2 class="text-sm font-extrabold uppercase tracking-[0.12em] text-slate-500">Este mes</h2>
            <span class="text-xs font-medium text-slate-400">Comparado con {{ \Carbon\Carbon::now()->subMonthNoOverflow()->locale('es')->translatedFormat('F') }}</span>
        </div>
        <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
            @foreach($kpis as [$label, $valor, $var, $icono, $tono])
                <div class="dash-card min-w-0 rounded-2xl p-4 sm:p-5">
                    <div class="flex items-start justify-between gap-2">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl {{ $tono }}"><i class="fas {{ $icono }}"></i></span>
                        @if($var === null)
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-500">Nuevo</span>
                        @elseif($var > 0)
                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700"><i class="fas fa-arrow-up text-[10px]"></i> {{ $var }}%</span>
                        @elseif($var < 0)
                            <span class="rounded-full bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700"><i class="fas fa-arrow-down text-[10px]"></i> {{ abs($var) }}%</span>
                        @else
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-500">= 0%</span>
                        @endif
                    </div>
                    <p class="mt-3 truncate text-xl font-extrabold tracking-tight text-slate-900 sm:mt-4 sm:text-2xl">{{ $valor }}</p>
                    <p class="mt-0.5 text-xs font-semibold text-slate-500 sm:text-sm">{{ $label }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ===== Módulos ===== --}}
    @php
        $modules = [
            ['title' => 'Clientes', 'icon' => 'fa-users', 'count' => $totalClientes ?? 0, 'route' => route('clientes.index')],
            ['title' => 'Usuarios', 'icon' => 'fa-user-shield', 'count' => $totalUsuarios ?? 0, 'route' => route('usuarios.index')],
            ['title' => 'Facturación', 'icon' => 'fa-file-invoice-dollar', 'count' => $totalFacturas ?? 0, 'route' => route('facturacion.index')],
            ['title' => 'Inventario', 'icon' => 'fa-boxes', 'count' => $totalPaquetes ?? 0, 'route' => route('inventario.index')],
            ['title' => 'Tracking', 'icon' => 'fa-route', 'count' => null, 'route' => route('tracking.dashboard')],
            ['title' => 'Notificaciones', 'icon' => 'fa-bell', 'count' => null, 'route' => route('notificaciones.index')],
        ];
    @endphp
    <section class="grid grid-cols-2 gap-3 sm:grid-cols-3 2xl:grid-cols-6">
        @foreach($modules as $mod)
            <a href="{{ $mod['route'] }}" class="dash-card dash-mod group flex min-w-0 items-center gap-2.5 rounded-2xl px-3 py-3 sm:gap-3 sm:px-4 sm:py-3.5">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#15537c]/10 text-[#15537c] transition group-hover:bg-[#15537c] group-hover:text-white">
                    <i class="fas {{ $mod['icon'] }}"></i>
                </span>
                <span class="min-w-0 leading-tight">
                    <span class="block text-lg font-extrabold text-slate-900">{{ $mod['count'] !== null ? number_format($mod['count']) : '' }}@if($mod['count'] === null)<i class="fas fa-arrow-right text-sm text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-[#15537c]"></i>@endif</span>
                    <span class="block text-[12.5px] font-semibold text-slate-500 [overflow-wrap:anywhere] sm:text-sm">{{ $mod['title'] }}</span>
                </span>
            </a>
        @endforeach
    </section>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Columna izquierda: 2/3 --}}
        <div class="min-w-0 space-y-6 lg:col-span-2">

            {{-- Estadísticas por cliente --}}
            <section class="dash-card rounded-2xl">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-5 py-4 sm:px-6">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Estadísticas por cliente</h2>
                        <p class="text-sm text-slate-500">Busca un cliente o mira todos juntos.</p>
                    </div>
                    <span id="statsPeriodo" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">Elige un cliente</span>
                </div>

                <div class="p-5 sm:p-6">
                    <div class="grid gap-3 md:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)_minmax(0,1.1fr)]">
                        <div class="relative">
                            <label for="clienteAutocomplete" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Cliente</label>
                            <div class="flex gap-2">
                                <div class="relative min-w-0 flex-1">
                                    <i class="fas fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                                    <input type="text" id="clienteAutocomplete" class="dash-input w-full pl-10" placeholder="Nombre del cliente..." autocomplete="off">
                                </div>
                                <button type="button" id="btnTodosClientes" class="shrink-0 rounded-xl border border-[#15537c]/30 bg-[#15537c]/10 px-3.5 text-sm font-bold text-[#15537c] transition hover:bg-[#15537c]/20" title="Ver estadísticas de todos los clientes en el rango">Todos</button>
                            </div>
                            <ul id="autocompleteList" class="absolute left-0 right-0 top-full z-20 mt-1 hidden max-h-60 overflow-y-auto rounded-xl border border-slate-200 bg-white py-1 shadow-xl"></ul>
                        </div>
                        <div>
                            <label for="servicioSelect" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Servicio</label>
                            <select id="servicioSelect" class="dash-input w-full">
                                <option value="todos">Todos</option>
                                <option value="aereo">Aéreo</option>
                                <option value="maritimo">Marítimo</option>
                                <option value="pie_cubico">Pie cúbico</option>
                            </select>
                        </div>
                        <div>
                            <label for="fechaRango" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Fechas</label>
                            <div class="relative">
                                <i class="far fa-calendar pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                                <input type="text" id="fechaRango" class="dash-input w-full pl-10" placeholder="Todo el historial" autocomplete="off">
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl bg-[#15537c]/[0.06] p-4">
                            <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-[#15537c]/80"><i class="fas fa-box"></i> Paquetes</p>
                            <p id="clienteTotalPaquetes" class="mt-2 text-2xl font-extrabold text-[#15537c]">-</p>
                        </div>
                        <div class="rounded-2xl bg-emerald-50 p-4">
                            <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-emerald-700/80"><i class="fas fa-dollar-sign"></i> Dinero</p>
                            <p id="clienteDinero" class="mt-2 text-2xl font-extrabold text-emerald-700">-</p>
                        </div>
                        <div class="rounded-2xl bg-sky-50 p-4">
                            <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-sky-700/80"><i class="fas fa-weight-hanging"></i> Libras</p>
                            <p id="clienteLibras" class="mt-2 text-2xl font-extrabold text-sky-700">- lb</p>
                        </div>
                    </div>

                    <div id="noClienteDataMsg" class="mt-4 hidden rounded-2xl border border-dashed border-slate-200 py-6 text-center text-sm text-slate-500">
                        <i class="fas fa-box-open mb-2 block text-2xl text-slate-300"></i>
                        No hay paquetes para este cliente en el rango seleccionado.
                    </div>

                    <div id="tablaPorTipoWrap" class="mt-5 hidden">
                        <h3 class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500">Detalle por tipo (todos los clientes)</h3>
                        <div class="overflow-x-auto rounded-xl border border-slate-200">
                            <table class="w-full border-collapse text-left text-sm text-slate-800">
                                <thead class="bg-slate-50 text-xs font-bold uppercase text-slate-500">
                                    <tr>
                                        <th class="px-4 py-2.5">Tipo</th>
                                        <th class="px-4 py-2.5 text-right">Paquetes</th>
                                        <th class="px-4 py-2.5 text-right">Dinero</th>
                                        <th class="px-4 py-2.5 text-right">Libras</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaPorTipoBody"></tbody>
                            </table>
                        </div>
                        <p id="tablaPorTipoRango" class="mt-2 text-xs text-slate-500"></p>
                    </div>
                </div>
            </section>

            {{-- Últimos paquetes --}}
            <section class="dash-card overflow-hidden rounded-2xl">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Últimos paquetes registrados</h2>
                        <p class="text-sm text-slate-500">Los 5 ingresos más recientes.</p>
                    </div>
                    <a href="{{ route('inventario.index') }}" class="text-sm font-bold text-[#15537c] hover:underline">Ver todos <i class="fas fa-arrow-right text-xs"></i></a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-2.5 sm:px-6">Cliente</th>
                                <th class="px-4 py-2.5">Servicio</th>
                                <th class="px-4 py-2.5">Guía</th>
                                <th class="px-4 py-2.5">Tracking</th>
                                <th class="px-5 py-2.5 text-right sm:px-6">Ingreso</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($ultimosPaquetes ?? [] as $paq)
                                @php
                                    $tipo = strtolower(str_replace(['á','é','í','ó','ú'], ['a','e','i','o','u'], $paq->servicio->tipo_servicio ?? ''));
                                    $chip = match ($tipo) {
                                        'aereo' => ['bg-sky-50 text-sky-700', 'fa-plane'],
                                        'maritimo' => ['bg-[#15537c]/10 text-[#15537c]', 'fa-ship'],
                                        'pie_cubico' => ['bg-amber-50 text-amber-700', 'fa-cube'],
                                        default => ['bg-slate-100 text-slate-600', 'fa-box'],
                                    };
                                    $nombreCliente = $paq->cliente->nombre_completo ?? '—';
                                @endphp
                                <tr class="cursor-pointer transition hover:bg-slate-50" onclick="window.location='{{ route('inventario.show', $paq->id) }}'">
                                    <td class="px-5 py-3 sm:px-6">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#15537c] text-xs font-bold text-white">{{ mb_strtoupper(mb_substr($nombreCliente, 0, 1)) }}</span>
                                            <span class="whitespace-nowrap font-semibold text-slate-900">{{ $nombreCliente }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-bold {{ $chip[0] }}">
                                            <i class="fas {{ $chip[1] }} text-[10px]"></i>{{ $paq->servicio->tipo_servicio ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-[13px] font-semibold text-slate-800">
                                        <a href="{{ route('inventario.show', $paq->id) }}" class="hover:text-[#15537c] hover:underline">{{ $paq->numero_guia }}</a>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-[13px] text-slate-500">{{ $paq->tracking_codigo ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right text-slate-500 sm:px-6">{{ $paq->fecha_ingreso ? \Carbon\Carbon::parse($paq->fecha_ingreso)->format('d/m/Y') : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-10 text-center text-slate-500"><i class="fas fa-box-open mb-2 block text-2xl text-slate-300"></i>No hay paquetes recientes.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        {{-- Columna derecha --}}
        <div class="min-w-0 space-y-6">
            @php $totalPie = collect($serviciosPieData ?? [])->sum(); @endphp
            <section class="dash-card rounded-2xl p-5 sm:p-6">
                <h2 class="text-base font-extrabold text-slate-900">Distribución por tipo</h2>
                <p class="text-sm text-slate-500">{{ \Illuminate\Support\Str::ucfirst($ahora->translatedFormat('F Y')) }}</p>
                @if($totalPie > 0)
                    <div class="relative mx-auto mt-4 max-w-[240px]">
                        <canvas id="graficoServiciosPie" height="240" width="240"></canvas>
                        <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-3xl font-extrabold text-slate-900">{{ $totalPie }}</span>
                            <span class="text-xs font-semibold text-slate-500">paquetes</span>
                        </div>
                    </div>
                    <ul id="pieLegend" class="mt-5 space-y-2"></ul>
                @else
                    <div class="mt-4 rounded-2xl border border-dashed border-slate-200 py-10 text-center text-sm text-slate-500">
                        <i class="fas fa-chart-pie mb-2 block text-2xl text-slate-300"></i>
                        Aún no hay paquetes este mes.
                    </div>
                @endif
            </section>

            <section class="dash-card rounded-2xl p-5 sm:p-6">
                <h2 class="text-base font-extrabold text-slate-900">Accesos rápidos</h2>
                <div class="mt-3 grid gap-2">
                    @foreach([
                        ['Clientes', 'Registrar o buscar un cliente', 'fa-user-plus', route('clientes.index')],
                        ['Facturación', 'Facturas y cobros', 'fa-file-invoice-dollar', route('facturacion.index')],
                        ['Tracking', 'Seguimiento de envíos', 'fa-route', route('tracking.dashboard')],
                    ] as [$t, $d, $i, $r])
                        <a href="{{ $r }}" class="group flex items-center gap-3 rounded-xl border border-slate-100 px-3 py-2.5 transition hover:border-[#15537c]/20 hover:bg-slate-50">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-500 transition group-hover:bg-[#15537c]/10 group-hover:text-[#15537c]"><i class="fas {{ $i }}"></i></span>
                            <span class="min-w-0 flex-1 leading-tight">
                                <span class="block text-sm font-bold text-slate-800">{{ $t }}</span>
                                <span class="block text-xs text-slate-500">{{ $d }}</span>
                            </span>
                            <i class="fas fa-chevron-right text-xs text-slate-300 group-hover:text-[#15537c]"></i>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>
    </div>

    @else
        {{-- Usuarios sin rol admin: accesos a sus módulos --}}
        @php
            $misModulos = collect(config('permisos.modulos', []))
                ->except('dashboard')
                ->filter(fn ($m, $key) => $usuario?->tienePermiso($key) && \Illuminate\Support\Facades\Route::has($key.'.index'));
        @endphp
        <section>
            <h2 class="mb-3 text-sm font-extrabold uppercase tracking-[0.12em] text-slate-500">Tus módulos</h2>
            @if($misModulos->isNotEmpty())
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($misModulos as $key => $m)
                        <a href="{{ route($key.'.index') }}" class="dash-card dash-mod group flex items-center gap-4 rounded-2xl p-4">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#15537c]/10 text-[#15537c] transition group-hover:bg-[#15537c] group-hover:text-white"><i class="fas {{ $m['icono'] ?? 'fa-folder' }}"></i></span>
                            <span class="min-w-0 flex-1">
                                <span class="block font-bold text-slate-900">{{ $m['label'] }}</span>
                                <span class="block truncate text-sm text-slate-500">{{ $m['descripcion'] ?? '' }}</span>
                            </span>
                            <i class="fas fa-chevron-right text-xs text-slate-300 group-hover:text-[#15537c]"></i>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="dash-card rounded-2xl p-6 text-sm text-slate-500">Aún no tienes módulos asignados. Pide acceso a un administrador.</p>
            @endif
        </section>
    @endif
</div>

<style>
    .dash-hero {
        background:
            radial-gradient(520px 260px at 100% 0%, rgba(240,166,58,0.22), transparent 60%),
            radial-gradient(600px 320px at 0% 100%, rgba(26,100,148,0.5), transparent 60%),
            linear-gradient(140deg, #0e3a5c 0%, #0a2740 65%, #071a2b 100%);
        box-shadow: 0 20px 40px -28px rgba(11,31,51,0.7);
    }
    .dash-hero::after {
        content: ""; position: absolute; inset: 0; pointer-events: none;
        background-image: radial-gradient(rgba(255,255,255,0.08) 1px, transparent 1px);
        background-size: 20px 20px;
        mask-image: linear-gradient(90deg, transparent 0%, #000 70%);
    }
    .dash-btn-amber { color: #3d2504; background: linear-gradient(180deg, #f6bb5f 0%, #f0a63a 100%); transition: filter .15s; }
    .dash-btn-amber:hover { filter: brightness(1.05); }
    .dash-card { background: #fff; border: 1px solid #e6ebf0; box-shadow: 0 1px 2px rgba(11,31,51,0.04), 0 12px 32px -24px rgba(11,31,51,0.25); }
    .dash-mod { transition: transform .15s, box-shadow .15s, border-color .15s; }
    .dash-mod:hover { transform: translateY(-2px); border-color: rgba(21,83,124,0.25); box-shadow: 0 16px 32px -20px rgba(21,83,124,0.45); }
    .dash-input { border-radius: 0.75rem; border: 1px solid #d5dde5; background: #fff; padding: 0.65rem 0.9rem; font-size: 0.95rem; color: #0b1f33; transition: border-color .15s, box-shadow .15s; }
    .dash-input.pl-10 { padding-left: 2.5rem; }
    .dash-input:focus { outline: none; border-color: #15537c; box-shadow: 0 0 0 3px rgba(21,83,124,0.15); }
    @keyframes wave { 0%, 60%, 100% { transform: rotate(0); } 10%, 30% { transform: rotate(14deg); } 20% { transform: rotate(-8deg); } 40% { transform: rotate(-4deg); } 50% { transform: rotate(10deg); } }
    @media (prefers-reduced-motion: reduce) { .dash-mod, .dash-mod:hover { transition: none; transform: none; } }
</style>

@if($esAdmin)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script>
(function() {
var clientesData = @json($clientesData ?? []);
var clientesList = @json($clientes ?? []);
var serviciosPieData = @json($serviciosPieData ?? []);
var chLogisticsPalette = ['#15537c','#f0a63a','#38a3d9','#0b1f33','#8fb8d4','#e57f5c','#5fb38f','#b7c4d1'];
function money(v) { return '$' + parseFloat(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
function num(v) { return parseFloat(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
var periodoEl = document.getElementById('statsPeriodo');
function fmtFecha(ymd) { if (!ymd) return ''; var p = String(ymd).split('-'); return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : ymd; }
function setPeriodo() {
    if (!periodoEl) return;
    var texto, activo = true;
    if (!selectedClienteId) { texto = 'Elige un cliente'; activo = false; }
    else if (fechaDesde && fechaHasta) { texto = fmtFecha(fechaDesde) + ' – ' + fmtFecha(fechaHasta); }
    else if (selectedClienteId === 'todos') { texto = 'Mes actual'; }
    else { texto = 'Todo el historial'; }
    periodoEl.textContent = texto;
    periodoEl.className = 'rounded-full px-3 py-1 text-xs font-bold ' + (activo ? 'bg-[#15537c]/10 text-[#15537c]' : 'bg-slate-100 text-slate-500');
}
var input = document.getElementById('clienteAutocomplete');
var list = document.getElementById('autocompleteList');
var servicioSelect = document.getElementById('servicioSelect');
var btnTodosClientes = document.getElementById('btnTodosClientes');
var selectedClienteId = null;
var fechaDesde = null, fechaHasta = null;

function formatLocalYMD(d) {
    var y = d.getFullYear();
    var m = String(d.getMonth() + 1).padStart(2, '0');
    var day = String(d.getDate()).padStart(2, '0');
    return y + '-' + m + '-' + day;
}
function getDateRangeOrDefaultMonth() {
    if (fechaDesde && fechaHasta) return { desde: fechaDesde, hasta: fechaHasta };
    var now = new Date();
    var first = new Date(now.getFullYear(), now.getMonth(), 1);
    var last = new Date(now.getFullYear(), now.getMonth() + 1, 0);
    return { desde: formatLocalYMD(first), hasta: formatLocalYMD(last) };
}
function setTodosModeActive(active) {
    if (!btnTodosClientes) return;
    if (active) {
        btnTodosClientes.classList.add('ring-2', 'ring-[#15537c]', 'bg-[#15537c]/25');
    } else {
        btnTodosClientes.classList.remove('ring-2', 'ring-[#15537c]', 'bg-[#15537c]/25');
    }
}
function hidePorTipoTable() {
    var wrap = document.getElementById('tablaPorTipoWrap');
    if (wrap) wrap.classList.add('hidden');
}
function renderPorTipoTable(porTipo, desde, hasta) {
    var wrap = document.getElementById('tablaPorTipoWrap');
    var body = document.getElementById('tablaPorTipoBody');
    var rangoEl = document.getElementById('tablaPorTipoRango');
    if (!wrap || !body) return;
    if (!porTipo) { wrap.classList.add('hidden'); return; }
    var rows = [
        { key: 'maritimo', label: 'Marítimo' },
        { key: 'aereo', label: 'Aéreo' },
        { key: 'pie_cubico', label: 'Pie cúbico' }
    ];
    body.innerHTML = '';
    rows.forEach(function(r) {
        var d = porTipo[r.key] || { paquetes: 0, dinero: 0, libras: 0 };
        var tr = document.createElement('tr');
        tr.className = 'border-b border-slate-100 last:border-0';
        tr.innerHTML = '<td class="px-4 py-2.5 font-semibold">' + r.label + '</td>' +
            '<td class="px-4 py-2.5 text-right">' + (d.paquetes || 0) + '</td>' +
            '<td class="px-4 py-2.5 text-right">' + money(d.dinero) + '</td>' +
            '<td class="px-4 py-2.5 text-right">' + num(d.libras) + ' lb</td>';
        body.appendChild(tr);
    });
    wrap.classList.remove('hidden');
    if (rangoEl && desde && hasta) {
        rangoEl.textContent = 'Período: ' + fmtFecha(desde) + ' – ' + fmtFecha(hasta);
    }
}

var idsCliente = {
    total: document.getElementById('clienteTotalPaquetes'),
    dinero: document.getElementById('clienteDinero'),
    libras: document.getElementById('clienteLibras'),
};

function setClienteStats(data, servicio) {
    hidePorTipoTable();
    setPeriodo();
    if (!data) {
        if (idsCliente.total) idsCliente.total.textContent = '-';
        if (idsCliente.dinero) idsCliente.dinero.textContent = '-';
        if (idsCliente.libras) idsCliente.libras.textContent = '-';
        var noMsg = document.getElementById('noClienteDataMsg');
        if (noMsg) noMsg.classList.remove('hidden');
        return;
    }
    var total = 0, dinero = 0, libras = 0;
    if (servicio === 'todos') {
        total = (data.paquetes_aereo||0)+(data.paquetes_maritimo||0)+(data.paquetes_pie_cubico||0);
        dinero = (data.ingresos_aereo||0)+(data.ingresos_maritimo||0)+(data.ingresos_pie_cubico||0);
        libras = (data.libras_aereo||0)+(data.libras_maritimo||0)+(data.libras_pie_cubico||0);
    } else if (servicio === 'aereo') { total = data.paquetes_aereo||0; dinero = data.ingresos_aereo||0; libras = data.libras_aereo||0; }
    else if (servicio === 'maritimo') { total = data.paquetes_maritimo||0; dinero = data.ingresos_maritimo||0; libras = data.libras_maritimo||0; }
    else if (servicio === 'pie_cubico') { total = data.paquetes_pie_cubico||0; dinero = data.ingresos_pie_cubico||0; libras = data.libras_pie_cubico||0; }
    if (idsCliente.total) idsCliente.total.textContent = total;
    if (idsCliente.dinero) idsCliente.dinero.textContent = typeof dinero === 'number' ? money(dinero) : '-';
    if (idsCliente.libras) idsCliente.libras.textContent = (typeof libras === 'number' ? num(libras) : '-') + ' lb';
    var noMsg = document.getElementById('noClienteDataMsg');
    if (noMsg) noMsg.classList.toggle('hidden', total > 0);
}

function showSuggestions(term) {
    if (!list) return;
    list.innerHTML = '';
    var liTodos = document.createElement('li');
    liTodos.className = 'cursor-pointer px-4 py-2.5 hover:bg-[#15537c]/10 border-b border-slate-100 text-sm font-bold text-[#15537c]';
    liTodos.textContent = 'Todos los clientes';
    liTodos.onclick = function() {
        input.value = 'Todos los clientes';
        selectedClienteId = 'todos';
        list.classList.add('hidden');
        setTodosModeActive(true);
        fetchClienteStats();
    };
    list.appendChild(liTodos);
    var filtered = clientesList.filter(function(c){ return (c.nombre_completo||'').toLowerCase().indexOf((term||'').toLowerCase()) !== -1; });
    if (filtered.length === 0) { list.classList.remove('hidden'); return; }
    filtered.forEach(function(cliente) {
        var li = document.createElement('li');
        li.className = 'cursor-pointer px-4 py-2.5 text-sm text-slate-800 hover:bg-slate-50';
        li.textContent = cliente.nombre_completo;
        li.onclick = function() {
            input.value = cliente.nombre_completo;
            selectedClienteId = cliente.id;
            setTodosModeActive(false);
            list.classList.add('hidden');
            renderClienteStats(cliente.id);
        };
        list.appendChild(li);
    });
    list.classList.remove('hidden');
}

if (btnTodosClientes) {
    btnTodosClientes.addEventListener('click', function() {
        if (input) input.value = 'Todos los clientes';
        selectedClienteId = 'todos';
        setTodosModeActive(true);
        if (list) list.classList.add('hidden');
        fetchClienteStats();
    });
}
if (input) {
    input.addEventListener('input', function() {
        var term = this.value.trim();
        if (!term) {
            list && list.classList.add('hidden');
            selectedClienteId = null;
            setTodosModeActive(false);
            hidePorTipoTable();
            setClienteStats(null, servicioSelect ? servicioSelect.value : 'todos');
            return;
        }
        if (term.toLowerCase() !== 'todos los clientes') {
            setTodosModeActive(false);
            if (selectedClienteId === 'todos') {
                selectedClienteId = null;
                hidePorTipoTable();
                setClienteStats(null, servicioSelect ? servicioSelect.value : 'todos');
            }
        }
        showSuggestions(term);
    });
    input.addEventListener('focus', function() { if (this.value.trim()) showSuggestions(this.value.trim()); });
}
document.addEventListener('click', function(e) {
    if (list && !list.contains(e.target) && e.target !== input) list.classList.add('hidden');
});

function renderClienteStats(clienteId) {
    var data = clientesData[clienteId] || null;
    setClienteStats(data, servicioSelect ? servicioSelect.value : 'todos');
}

if (servicioSelect) servicioSelect.addEventListener('change', function() {
    if (selectedClienteId === 'todos') {
        fetchClienteStats();
        return;
    }
    if (selectedClienteId) {
        if (!fechaDesde && !fechaHasta) renderClienteStats(selectedClienteId);
        else fetchClienteStats();
    }
});

function setClienteStatsAjax(data) {
    setPeriodo();
    if (!data) {
        hidePorTipoTable();
        if (idsCliente.total) idsCliente.total.textContent = '-';
        if (idsCliente.dinero) idsCliente.dinero.textContent = '-';
        if (idsCliente.libras) idsCliente.libras.textContent = '-';
        var noMsg = document.getElementById('noClienteDataMsg');
        if (noMsg) { noMsg.classList.remove('hidden'); }
        return;
    }
    if (idsCliente.total) idsCliente.total.textContent = data.total ?? '-';
    if (idsCliente.dinero) idsCliente.dinero.textContent = data.dinero !== undefined ? money(data.dinero) : '-';
    if (idsCliente.libras) idsCliente.libras.textContent = (data.libras !== undefined ? num(data.libras) : '-') + ' lb';
    var noMsg = document.getElementById('noClienteDataMsg');
    if (noMsg) noMsg.classList.toggle('hidden', (data.total||0) > 0);
    if (data.todos_clientes && data.por_tipo) {
        renderPorTipoTable(data.por_tipo, data.desde, data.hasta);
    } else {
        hidePorTipoTable();
    }
}

function fetchClienteStats() {
    if (!selectedClienteId) { setClienteStatsAjax(null); return; }
    if (idsCliente.total) idsCliente.total.textContent = '...';
    if (idsCliente.dinero) idsCliente.dinero.textContent = '...';
    if (idsCliente.libras) idsCliente.libras.textContent = '...';
    var tipo = servicioSelect ? servicioSelect.value : 'todos';
    var url = '/dashboard/estadisticas-paquetes-cliente?tipo_servicio=' + encodeURIComponent(tipo);
    if (selectedClienteId === 'todos') {
        var dr = getDateRangeOrDefaultMonth();
        url += '&cliente_id=todos&desde=' + encodeURIComponent(dr.desde) + '&hasta=' + encodeURIComponent(dr.hasta);
    } else {
        url += '&cliente_id=' + encodeURIComponent(selectedClienteId);
        if (fechaDesde) url += '&desde=' + encodeURIComponent(fechaDesde);
        if (fechaHasta) url += '&hasta=' + encodeURIComponent(fechaHasta);
    }
    fetch(url, { headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}, credentials: 'same-origin' })
        .then(function(r){ return r.ok ? r.json() : Promise.reject(); })
        .then(setClienteStatsAjax)
        .catch(function(){ setClienteStatsAjax(null); });
}

if (typeof flatpickr !== 'undefined') {
    flatpickr('#fechaRango', {
        mode: 'range',
        dateFormat: 'Y-m-d',
        locale: 'es',
        onChange: function(selectedDates) {
            if (selectedDates.length === 2) {
                fechaDesde = selectedDates[0].toISOString().slice(0,10);
                fechaHasta = selectedDates[1].toISOString().slice(0,10);
            } else { fechaDesde = null; fechaHasta = null; }
            fetchClienteStats();
        }
    });
}

var ctxPie = document.getElementById('graficoServiciosPie');
if (ctxPie && typeof Chart !== 'undefined') {
    var pieLabels = Object.keys(serviciosPieData);
    var pieData = Object.values(serviciosPieData);
    var pieColors = pieLabels.map(function(_, i){ return chLogisticsPalette[i % chLogisticsPalette.length]; });
    var pieTotal = pieData.reduce(function(a,b){ return a + b; }, 0);
    var legend = document.getElementById('pieLegend');
    if (legend) {
        legend.innerHTML = pieLabels.map(function(label, i) {
            var pct = pieTotal ? Math.round(pieData[i] / pieTotal * 100) : 0;
            return '<li class="flex items-center gap-3 text-sm">' +
                '<span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background:' + pieColors[i] + '"></span>' +
                '<span class="flex-1 font-semibold capitalize text-slate-700">' + String(label).replace(/[<>&]/g, '') + '</span>' +
                '<span class="font-bold text-slate-900">' + pieData[i] + '</span>' +
                '<span class="w-10 text-right text-xs font-semibold text-slate-400">' + pct + '%</span></li>';
        }).join('');
    }
    new Chart(ctxPie.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: pieLabels,
            datasets: [{ data: pieData, backgroundColor: pieColors, borderColor: '#fff', borderWidth: 3, hoverOffset: 6 }]
        },
        options: {
            cutout: '68%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            var total = ctx.dataset.data.reduce(function(a,b){ return a+b; }, 0);
                            var pct = total ? ((ctx.parsed/total)*100).toFixed(1) : 0;
                            return ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                        }
                    }
                }
            }
        }
    });
}
})();
</script>
@endpush
@endif
@endsection
