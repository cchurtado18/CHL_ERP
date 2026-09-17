@extends('layouts.app-new')

@section('title', 'Reporte de Rentabilidad - CH Logistics')
@section('navbar-title', 'Rentabilidad')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="mx-auto w-full max-w-[1400px] space-y-8">

    {{-- ─── Cabecera ─── --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <nav class="mb-2 flex items-center gap-2 text-sm text-slate-500">
                    <a href="{{ route('contabilidad.dashboard') }}" class="hover:text-[#15537c]">Contabilidad</a>
                    <i class="fas fa-chevron-right text-xs"></i>
                    <span class="font-semibold text-slate-700">Reporte de Rentabilidad</span>
                </nav>
                <h1 class="flex items-center gap-3 text-2xl font-bold text-slate-800">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow"><i class="fas fa-chart-line text-xl"></i></span>
                    Reporte de Rentabilidad
                </h1>
                <p class="mt-1 text-base text-slate-600">Ganancia por cliente y remitente, gastos extras y resultado neto. <strong>Paquetería + Encomiendas familiares.</strong></p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('contabilidad.dashboard') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"><i class="fas fa-arrow-left text-[#15537c]"></i> Volver al panel</a>
                <a href="{{ route('contabilidad.parametros.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-100"><i class="fas fa-sliders"></i> Configurar costos / lb</a>
                <a href="{{ route('contabilidad.gastos.create') }}" class="inline-flex items-center gap-2 rounded-lg border border-rose-300 bg-white px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50"><i class="fas fa-plus"></i> Cargar gasto</a>
                <a href="{{ route('contabilidad.gastos.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"><i class="fas fa-receipt"></i> Ver gastos</a>
            </div>
        </div>

        {{-- Costos vigentes por servicio --}}
        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4">
            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500"><i class="fas fa-weight-hanging mr-1"></i> Costo / lb por servicio:</span>
            @foreach($serviciosConCosto as $sc)
                @php
                    $iconoServ = match(strtolower($sc['nombre'])) {
                        'aéreo', 'aereo' => 'fa-plane',
                        'marítimo', 'maritimo' => 'fa-ship',
                        'pie cúbico', 'pie cubico' => 'fa-cube',
                        default => 'fa-box',
                    };
                @endphp
                <span class="inline-flex items-center gap-1.5 rounded-full {{ $sc['costo'] > 0 ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-rose-50 text-rose-800 border-rose-200' }} border px-2.5 py-1 text-xs font-semibold">
                    <i class="fas {{ $iconoServ }}"></i>
                    {{ $sc['nombre'] }}: <span class="tabular-nums">${{ number_format($sc['costo'], 4) }}</span>
                    @if(! $sc['es_especifico'] && $sc['costo'] > 0)
                        <span class="ml-1 text-[10px] text-slate-500" title="Usa el costo global">(global)</span>
                    @endif
                </span>
            @endforeach
        </div>
    </div>

    {{-- Alerta de costo no configurado --}}
    @php $servSinCosto = collect($serviciosConCosto)->where('costo', '<=', 0)->count(); @endphp
    @if($servSinCosto > 0)
        <div class="rounded-xl border-2 border-amber-300 bg-amber-50 px-5 py-4 text-base text-amber-900">
            <p class="font-semibold"><i class="fas fa-exclamation-triangle mr-2"></i> Hay {{ $servSinCosto }} servicio(s) sin costo configurado.</p>
            <p class="mt-1 text-sm">Esos servicios se calculan con $0/lb, por lo que la ganancia sale igual al ingreso (no es real). <a href="{{ route('contabilidad.parametros.index') }}" class="font-semibold underline">Configurar ahora →</a></p>
        </div>
    @endif

    {{-- Alerta de clientes en pérdida --}}
    @if($clientesEnPerdida > 0)
        <div class="rounded-xl border-2 border-rose-300 bg-rose-50 px-5 py-4 text-base text-rose-900 shadow-sm">
            <p class="font-semibold"><i class="fas fa-circle-exclamation mr-2"></i> Tenés <strong>{{ $clientesEnPerdida }}</strong> cliente(s) cobrando por debajo de tu costo. Estás perdiendo plata con ellos.</p>
            <p class="mt-1 text-sm">Revisá la tabla más abajo: las filas marcadas en rojo necesitan renegociación de tarifa o evaluar si conviene seguir atendiéndolas.</p>
        </div>
    @endif

    {{-- ╔═════════════ Filtro de período ═════════════╗ --}}
    <form method="GET" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="flex-1">
                <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500"><i class="fas fa-filter mr-1 text-[#15537c]"></i> Período</p>
                <div class="flex flex-wrap gap-2">
                    @php
                        $presets = [
                            'mes_actual'   => ['label' => 'Este mes',        'icon' => 'fa-calendar-day'],
                            'mes_anterior' => ['label' => 'Mes anterior',    'icon' => 'fa-calendar-minus'],
                            'ultimos_30'   => ['label' => 'Últimos 30 días', 'icon' => 'fa-clock'],
                            'trimestre'    => ['label' => 'Trimestre',       'icon' => 'fa-calendar-week'],
                            'anio'         => ['label' => 'Año',             'icon' => 'fa-calendar'],
                        ];
                    @endphp
                    @foreach($presets as $key => $info)
                        <a href="{{ route('contabilidad.rentabilidad.index', ['preset' => $key]) }}"
                           class="inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm font-semibold transition {{ $preset === $key ? 'border-[#15537c] bg-[#15537c] text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50' }}">
                            <i class="fas {{ $info['icon'] }}"></i> {{ $info['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Desde</label>
                    <input type="date" name="desde" value="{{ $preset === 'custom' ? $desde->toDateString() : '' }}" class="mt-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Hasta</label>
                    <input type="date" name="hasta" value="{{ $preset === 'custom' ? $hasta->toDateString() : '' }}" class="mt-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <button type="submit" class="rounded-lg bg-[#15537c] px-4 py-2 text-sm font-semibold text-white"><i class="fas fa-search"></i> Aplicar</button>
            </div>
        </div>
        <p class="mt-3 text-sm text-slate-500"><i class="fas fa-info-circle mr-1"></i> {{ $rangoLabel }}</p>
    </form>

    {{-- ╔═════════════ KPIs (mes actual vs anterior) ═════════════╗ --}}
    <section>
        <div class="mb-3 flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#15537c]/10 text-[#15537c]"><i class="fas fa-arrows-left-right"></i></span>
            <h2 class="text-xl font-bold text-slate-800">Comparativo del mes</h2>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600">{{ $labelMesActual }} vs {{ $labelMesAnterior }}</span>
        </div>

        @php
            $kpis = [
                ['titulo' => 'Libras enviadas', 'actual' => $rentaMesActual['libras'], 'anterior' => $rentaMesAnterior['libras'], 'var' => $variaciones['libras'], 'icon' => 'fa-weight-hanging', 'bg' => 'bg-sky-100', 'text' => 'text-sky-700', 'unidad' => 'lb', 'esMoneda' => false],
                ['titulo' => 'Ingreso bruto',   'actual' => $rentaMesActual['ingreso'], 'anterior' => $rentaMesAnterior['ingreso'], 'var' => $variaciones['ingreso'], 'icon' => 'fa-dollar-sign', 'bg' => 'bg-[#15537c]/10', 'text' => 'text-[#15537c]', 'esMoneda' => true],
                ['titulo' => 'Costo operativo', 'actual' => $rentaMesActual['costo'], 'anterior' => $rentaMesAnterior['costo'], 'var' => $variaciones['costo'], 'icon' => 'fa-minus-circle', 'bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'esMoneda' => true],
                ['titulo' => 'Ganancia bruta',  'actual' => $rentaMesActual['ganancia_bruta'], 'anterior' => $rentaMesAnterior['ganancia_bruta'], 'var' => $variaciones['ganancia_bruta'], 'icon' => 'fa-arrow-trend-up', 'bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'esMoneda' => true],
                ['titulo' => 'Gastos extras',   'actual' => $rentaMesActual['gastos'], 'anterior' => $rentaMesAnterior['gastos'], 'var' => $variaciones['gastos'], 'icon' => 'fa-receipt', 'bg' => 'bg-rose-100', 'text' => 'text-rose-700', 'esMoneda' => true],
                ['titulo' => 'Resultado neto',  'actual' => $rentaMesActual['neto'], 'anterior' => $rentaMesAnterior['neto'], 'var' => $variaciones['neto'], 'icon' => 'fa-coins', 'bg' => 'bg-violet-100', 'text' => 'text-violet-700', 'esMoneda' => true],
            ];
        @endphp

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            @foreach($kpis as $k)
                @php
                    $pct = $k['var']['pct'];
                    $dir = $k['var']['direccion'];
                    $inverso = $k['var']['inverso'] ?? false;
                    $sinDato = $pct === null;
                    $esBueno = $inverso ? ($dir === 'down') : ($dir === 'up');
                    if ($sinDato) {
                        $badgeClase = 'bg-slate-100 text-slate-500';
                        $arrow = 'fa-circle-info';
                        $badgeText = 'Sin dato';
                    } elseif ($dir === 'flat') {
                        $badgeClase = 'bg-slate-100 text-slate-600';
                        $arrow = 'fa-equals';
                        $badgeText = number_format($pct, 1).'%';
                    } else {
                        $badgeClase = $esBueno ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700';
                        $arrow = $dir === 'up' ? 'fa-arrow-up' : 'fa-arrow-down';
                        $badgeText = ($pct > 0 ? '+' : '').number_format($pct, 1).'%';
                    }
                @endphp
                <div class="flex h-full flex-col justify-between rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $k['bg'] }} text-sm {{ $k['text'] }}"><i class="fas {{ $k['icon'] }}"></i></div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $k['titulo'] }}</p>
                            </div>
                            <span class="inline-flex shrink-0 items-center gap-1 rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $badgeClase }}">
                                <i class="fas {{ $arrow }} text-[8px]"></i><span>{{ $badgeText }}</span>
                            </span>
                        </div>
                        <p class="mt-2 whitespace-nowrap text-xl font-bold tabular-nums text-slate-900">
                            @if($k['esMoneda'])${{ number_format($k['actual'], 2) }}@else{{ number_format($k['actual'], 0) }} <span class="text-sm font-medium text-slate-500">{{ $k['unidad'] }}</span>@endif
                        </p>
                    </div>
                    <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-2 text-xs text-slate-500">
                        <span>Mes ant.</span>
                        <span class="whitespace-nowrap font-semibold tabular-nums text-slate-700">@if($k['esMoneda'])${{ number_format($k['anterior'], 2) }}@else{{ number_format($k['anterior'], 0) }} {{ $k['unidad'] }}@endif</span>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ╔═════════════ Punto de equilibrio + Run Rate ═════════════╗ --}}
    <section class="grid grid-cols-1 gap-5 {{ $runRate ? 'lg:grid-cols-2' : '' }}">
        {{-- Punto de equilibrio --}}
        <div class="rounded-xl border-2 {{ $puntoEquilibrio['superado'] ? 'border-emerald-300 bg-gradient-to-br from-emerald-50 to-white' : 'border-amber-300 bg-gradient-to-br from-amber-50 to-white' }} p-6 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $puntoEquilibrio['superado'] ? 'bg-emerald-500' : 'bg-amber-500' }} text-white"><i class="fas fa-bullseye"></i></span>
                <h3 class="text-lg font-bold text-slate-800">Punto de equilibrio del período</h3>
            </div>
            @if($puntoEquilibrio['superado'])
                <p class="mt-4 text-base text-emerald-800">
                    <i class="fas fa-check-circle mr-1"></i> <strong>¡Ya superaste el equilibrio!</strong> Cada libra adicional es ganancia neta directa.
                </p>
                <p class="mt-2 text-sm text-slate-600">Llevás <strong>{{ number_format($puntoEquilibrio['libras_actuales'], 0) }} libras</strong> y tu ganancia bruta ya cubrió los <strong>${{ number_format($puntoEquilibrio['gastos'], 2) }}</strong> de gastos extras.</p>
            @else
                <p class="mt-4 text-sm text-slate-700">Para cubrir los <strong class="text-rose-700">${{ number_format($puntoEquilibrio['falta_cubrir'], 2) }}</strong> que faltan de los gastos extras, necesitás mover:</p>
                <p class="mt-2 text-4xl font-bold tabular-nums text-amber-700">{{ number_format($puntoEquilibrio['libras_necesarias'], 0) }} libras adicionales</p>
                <p class="mt-2 text-xs text-slate-500">Al margen actual de ${{ number_format($puntoEquilibrio['margen_promedio_lb'], 4) }}/lb. Llevás {{ number_format($puntoEquilibrio['libras_actuales'], 0) }} lb hasta ahora.</p>
            @endif
        </div>

        {{-- Run rate (solo si es mes en curso) --}}
        @if($runRate)
            <div class="rounded-xl border-2 {{ $runRate['neto_proyectado'] >= 0 ? 'border-sky-300 bg-gradient-to-br from-sky-50 to-white' : 'border-rose-300 bg-gradient-to-br from-rose-50 to-white' }} p-6 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $runRate['neto_proyectado'] >= 0 ? 'bg-sky-500' : 'bg-rose-500' }} text-white"><i class="fas fa-crystal-ball"></i></span>
                    <h3 class="text-lg font-bold text-slate-800">Proyección al cierre del mes</h3>
                </div>
                <p class="mt-3 text-sm text-slate-600">Día <strong>{{ $runRate['dia_actual'] }}</strong> de {{ $runRate['dias_totales'] }} ({{ $runRate['porcentaje_mes'] }}% del mes)</p>
                <div class="mt-3 space-y-1 text-sm">
                    <div class="flex items-center justify-between"><span class="text-slate-600">Libras proyectadas:</span> <span class="font-bold tabular-nums text-slate-900">{{ number_format($runRate['libras_proyectadas'], 0) }} lb</span></div>
                    <div class="flex items-center justify-between"><span class="text-slate-600">Ingreso proyectado:</span> <span class="font-bold tabular-nums text-[#15537c]">${{ number_format($runRate['ingreso_proyectado'], 2) }}</span></div>
                    <div class="flex items-center justify-between"><span class="text-slate-600">Ganancia bruta:</span> <span class="font-bold tabular-nums text-emerald-700">${{ number_format($runRate['ganancia_bruta_proyectada'], 2) }}</span></div>
                </div>
                <div class="mt-3 border-t border-slate-200 pt-3">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Resultado neto estimado al cierre</p>
                    <p class="text-2xl font-bold tabular-nums {{ $runRate['neto_proyectado'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">${{ number_format($runRate['neto_proyectado'], 2) }}</p>
                    @if($runRate['neto_proyectado'] < 0)
                        <p class="mt-1 text-xs font-semibold text-rose-700"><i class="fas fa-triangle-exclamation"></i> Si seguís a este ritmo, cerrarás el mes con pérdida.</p>
                    @endif
                </div>
            </div>
        @endif
    </section>

    {{-- ╔═════════════ Rentabilidad por cliente ═════════════╗ --}}
    <section>
        <div class="mb-3 flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700"><i class="fas fa-users"></i></span>
            <h2 class="text-xl font-bold text-slate-800">Rentabilidad por cliente</h2>
            <span class="ml-auto text-sm text-slate-500">{{ count($clientes) }} cliente(s) con actividad en el período</span>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1100px] border-collapse text-left text-base">
                    <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                        <tr>
                            <th class="px-3 py-2.5 font-semibold">#</th>
                            <th class="px-3 py-2.5 font-semibold">Cliente</th>
                            <th class="px-3 py-2.5 font-semibold text-center">Paquetes</th>
                            <th class="px-3 py-2.5 font-semibold text-right">Libras</th>
                            <th class="px-3 py-2.5 font-semibold text-right">Tarifa prom.</th>
                            <th class="px-3 py-2.5 font-semibold text-right">Ingreso</th>
                            <th class="px-3 py-2.5 font-semibold text-right">Costo op.</th>
                            <th class="px-3 py-2.5 font-semibold text-right">Ganancia</th>
                            <th class="px-3 py-2.5 font-semibold text-right">Margen</th>
                            <th class="px-3 py-2.5 font-semibold text-center">Estado</th>
                            <th class="px-3 py-2.5 font-semibold text-right">Detalle</th>
                        </tr>
                    </thead>
                    <tbody>
                    @php
                        $totLibras = 0; $totIngreso = 0; $totCosto = 0; $totGanancia = 0;
                    @endphp
                    @forelse($clientes as $i => $c)
                        @php
                            $totLibras += $c['libras']; $totIngreso += $c['ingreso']; $totCosto += $c['costo']; $totGanancia += $c['ganancia'];
                            $estadoCfg = match($c['estado']) {
                                'perdida'   => ['Pérdida', 'bg-rose-100 text-rose-800 border-rose-300', 'fa-circle-xmark', 'rowClass' => 'bg-rose-50/40'],
                                'bajo'      => ['Margen bajo', 'bg-amber-100 text-amber-800 border-amber-300', 'fa-triangle-exclamation', 'rowClass' => ''],
                                'saludable' => ['Saludable', 'bg-emerald-100 text-emerald-800 border-emerald-300', 'fa-circle-check', 'rowClass' => ''],
                            };
                            $medalla = match($i) {
                                0 => 'bg-yellow-400 text-white',
                                1 => 'bg-slate-400 text-white',
                                2 => 'bg-amber-600 text-white',
                                default => 'bg-slate-100 text-slate-500',
                            };
                        @endphp
                        <tr class="border-b border-slate-100 {{ $estadoCfg['rowClass'] ?: ($i % 2 === 0 ? 'bg-white' : 'bg-slate-50/50') }} hover:bg-slate-100">
                            <td class="px-3 py-2.5 align-top"><span class="inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold {{ $medalla }}">{{ $i + 1 }}</span></td>
                            <td class="px-3 py-2.5 align-top">
                                <div class="font-semibold text-slate-900">{{ $c['nombre'] }}</div>
                                <div class="mt-1 flex flex-wrap gap-1">
                                    @foreach($c['por_servicio'] as $ps)
                                        @php
                                            $iconoPS = match(strtolower($ps['nombre'])) {
                                                'aéreo', 'aereo' => 'fa-plane',
                                                'marítimo', 'maritimo' => 'fa-ship',
                                                'pie cúbico', 'pie cubico' => 'fa-cube',
                                                default => 'fa-box',
                                            };
                                            $colorPS = match(strtolower($ps['nombre'])) {
                                                'aéreo', 'aereo' => 'bg-sky-50 text-sky-700 border-sky-200',
                                                'marítimo', 'maritimo' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                                                'pie cúbico', 'pie cubico' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                                default => 'bg-slate-50 text-slate-600 border-slate-200',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center gap-1 rounded-full border {{ $colorPS }} px-1.5 py-0.5 text-[10px] font-semibold" title="{{ number_format($ps['libras'], 1) }} lb × ${{ number_format($ps['costo_unit'], 4) }}/lb = ${{ number_format($ps['costo'], 2) }}">
                                            <i class="fas {{ $iconoPS }} text-[9px]"></i>
                                            {{ $ps['nombre'] }}: {{ number_format($ps['libras'], 0) }} lb
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-3 py-2.5 text-center align-top text-slate-600 tabular-nums">{{ $c['paquetes'] }}</td>
                            <td class="px-3 py-2.5 text-right align-top font-medium tabular-nums text-slate-800">{{ number_format($c['libras'], 1) }}</td>
                            <td class="px-3 py-2.5 text-right align-top tabular-nums text-slate-700">${{ number_format($c['tarifa_promedio'], 2) }}/lb</td>
                            <td class="px-3 py-2.5 text-right align-top font-semibold tabular-nums text-[#15537c]">${{ number_format($c['ingreso'], 2) }}</td>
                            <td class="px-3 py-2.5 text-right align-top tabular-nums text-slate-600">
                                ${{ number_format($c['costo'], 2) }}
                                <div class="mt-0.5 text-[10px] font-normal text-slate-400">
                                    @foreach($c['por_servicio'] as $idx => $ps)
                                        {{ number_format($ps['libras'], 0) }}×${{ number_format($ps['costo_unit'], 2) }}@if($idx < count($c['por_servicio']) - 1) + @endif
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-3 py-2.5 text-right align-top font-bold tabular-nums {{ $c['ganancia'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">${{ number_format($c['ganancia'], 2) }}</td>
                            <td class="px-3 py-2.5 text-right align-top font-semibold tabular-nums {{ $c['margen'] >= 15 ? 'text-emerald-700' : ($c['margen'] >= 0 ? 'text-amber-700' : 'text-rose-700') }}">{{ number_format($c['margen'], 1) }}%</td>
                            <td class="px-3 py-2.5 text-center align-top">
                                <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs font-semibold {{ $estadoCfg[1] }}">
                                    <i class="fas {{ $estadoCfg[2] }} text-[10px]"></i> {{ $estadoCfg[0] }}
                                </span>
                            </td>
                            <td class="px-3 py-2.5 text-right align-top">
                                <a href="{{ route('contabilidad.rentabilidad.cliente', ['cliente' => $c['id'], 'preset' => $preset, 'desde' => request('desde'), 'hasta' => request('hasta')]) }}" class="inline-flex items-center gap-1 rounded-lg border border-[#15537c]/30 bg-[#15537c]/5 px-2.5 py-1 text-xs font-semibold text-[#15537c] hover:bg-[#15537c]/10">
                                    <i class="fas fa-search-dollar"></i> Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="px-4 py-12 text-center text-slate-500">Sin facturas de paquetería en el período seleccionado.</td></tr>
                    @endforelse
                    </tbody>
                    @if(count($clientes) > 0)
                        <tfoot class="border-t-2 border-slate-300 bg-slate-100">
                            <tr>
                                <td class="px-3 py-3"></td>
                                <td class="px-3 py-3 font-bold text-slate-800">TOTALES</td>
                                <td></td>
                                <td class="px-3 py-3 text-right font-bold tabular-nums text-slate-900">{{ number_format($totLibras, 1) }} lb</td>
                                <td class="px-3 py-3 text-right font-bold tabular-nums text-slate-700">${{ number_format($totLibras > 0 ? $totIngreso / $totLibras : 0, 2) }}/lb</td>
                                <td class="px-3 py-3 text-right font-bold tabular-nums text-[#15537c]">${{ number_format($totIngreso, 2) }}</td>
                                <td class="px-3 py-3 text-right font-bold tabular-nums text-slate-700">${{ number_format($totCosto, 2) }}</td>
                                <td class="px-3 py-3 text-right font-bold tabular-nums text-emerald-700">${{ number_format($totGanancia, 2) }}</td>
                                <td class="px-3 py-3 text-right font-bold tabular-nums">{{ number_format($totIngreso > 0 ? ($totGanancia / $totIngreso) * 100 : 0, 1) }}%</td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </section>

    {{-- ╔═════════════ Rentabilidad por remitente (Encomiendas) ═════════════╗ --}}
    @if(count($remitentes) > 0)
        <section>
            <div class="mb-3 flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700"><i class="fas fa-people-carry-box"></i></span>
                <h2 class="text-xl font-bold text-slate-800">Rentabilidad por remitente <span class="text-sm font-medium text-slate-500">(Encomiendas familiares)</span></h2>
                <span class="ml-auto text-sm text-slate-500">{{ count($remitentes) }} remitente(s) con actividad</span>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1100px] border-collapse text-left text-base">
                        <thead class="border-b border-slate-200 bg-slate-700 text-white">
                            <tr>
                                <th class="px-3 py-2.5 font-semibold">#</th>
                                <th class="px-3 py-2.5 font-semibold">Remitente</th>
                                <th class="px-3 py-2.5 font-semibold text-center">Items</th>
                                <th class="px-3 py-2.5 font-semibold text-right">Volumen</th>
                                <th class="px-3 py-2.5 font-semibold text-right">Ingreso</th>
                                <th class="px-3 py-2.5 font-semibold text-right">Costo op.</th>
                                <th class="px-3 py-2.5 font-semibold text-right">Ganancia</th>
                                <th class="px-3 py-2.5 font-semibold text-right">Margen</th>
                                <th class="px-3 py-2.5 font-semibold text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                        @php
                            $totRItems = 0; $totRPies = 0; $totRLibras = 0; $totRIngreso = 0; $totRCosto = 0; $totRGan = 0;
                        @endphp
                        @foreach($remitentes as $i => $r)
                            @php
                                $totRItems += $r['items']; $totRPies += $r['pies3']; $totRLibras += $r['libras'];
                                $totRIngreso += $r['ingreso']; $totRCosto += $r['costo']; $totRGan += $r['ganancia'];
                                $estR = match($r['estado']) {
                                    'perdida'   => ['Pérdida', 'bg-rose-100 text-rose-800 border-rose-300', 'fa-circle-xmark', 'rowClass' => 'bg-rose-50/40'],
                                    'bajo'      => ['Margen bajo', 'bg-amber-100 text-amber-800 border-amber-300', 'fa-triangle-exclamation', 'rowClass' => ''],
                                    'saludable' => ['Saludable', 'bg-emerald-100 text-emerald-800 border-emerald-300', 'fa-circle-check', 'rowClass' => ''],
                                };
                                $medR = match($i) {
                                    0 => 'bg-yellow-400 text-white',
                                    1 => 'bg-slate-400 text-white',
                                    2 => 'bg-amber-600 text-white',
                                    default => 'bg-slate-100 text-slate-500',
                                };
                            @endphp
                            <tr class="border-b border-slate-100 {{ $estR['rowClass'] ?: ($i % 2 === 0 ? 'bg-white' : 'bg-slate-50/50') }} hover:bg-slate-100">
                                <td class="px-3 py-2.5 align-top"><span class="inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold {{ $medR }}">{{ $i + 1 }}</span></td>
                                <td class="px-3 py-2.5 align-top">
                                    <div class="font-semibold text-slate-900">{{ $r['nombre'] }}</div>
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @foreach($r['por_metodo'] as $pm)
                                            <span class="inline-flex items-center gap-1 rounded-full border border-indigo-200 bg-indigo-50 px-1.5 py-0.5 text-[10px] font-semibold text-indigo-700" title="{{ number_format($pm['cantidad'], 2) }} {{ $pm['unidad'] }} × ${{ number_format($pm['costo_unit'], 4) }}/{{ $pm['unidad'] }} = ${{ number_format($pm['costo'], 2) }}">
                                                <i class="fas {{ $pm['unidad'] === 'pie³' ? 'fa-cube' : 'fa-weight-hanging' }} text-[9px]"></i>
                                                {{ $pm['etiqueta'] }}: {{ number_format($pm['cantidad'], 1) }} {{ $pm['unidad'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-3 py-2.5 text-center align-top tabular-nums text-slate-600">{{ $r['items'] }}</td>
                                <td class="px-3 py-2.5 text-right align-top tabular-nums text-slate-800">
                                    @if($r['pies3'] > 0) {{ number_format($r['pies3'], 1) }} pie³ @endif
                                    @if($r['pies3'] > 0 && $r['libras'] > 0) <br> @endif
                                    @if($r['libras'] > 0) {{ number_format($r['libras'], 1) }} lb @endif
                                </td>
                                <td class="px-3 py-2.5 text-right align-top font-semibold tabular-nums text-indigo-700">${{ number_format($r['ingreso'], 2) }}</td>
                                <td class="px-3 py-2.5 text-right align-top tabular-nums text-slate-600">${{ number_format($r['costo'], 2) }}</td>
                                <td class="px-3 py-2.5 text-right align-top font-bold tabular-nums {{ $r['ganancia'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">${{ number_format($r['ganancia'], 2) }}</td>
                                <td class="px-3 py-2.5 text-right align-top font-semibold tabular-nums {{ $r['margen'] >= 15 ? 'text-emerald-700' : ($r['margen'] >= 0 ? 'text-amber-700' : 'text-rose-700') }}">{{ number_format($r['margen'], 1) }}%</td>
                                <td class="px-3 py-2.5 text-center align-top">
                                    <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs font-semibold {{ $estR[1] }}">
                                        <i class="fas {{ $estR[2] }} text-[10px]"></i> {{ $estR[0] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot class="border-t-2 border-slate-300 bg-slate-100">
                            <tr>
                                <td class="px-3 py-3"></td>
                                <td class="px-3 py-3 font-bold text-slate-800">TOTALES</td>
                                <td class="px-3 py-3 text-center font-bold tabular-nums text-slate-900">{{ $totRItems }}</td>
                                <td class="px-3 py-3 text-right font-bold tabular-nums text-slate-900">
                                    @if($totRPies > 0) {{ number_format($totRPies, 1) }} pie³ @endif
                                    @if($totRPies > 0 && $totRLibras > 0) <br> @endif
                                    @if($totRLibras > 0) {{ number_format($totRLibras, 1) }} lb @endif
                                </td>
                                <td class="px-3 py-3 text-right font-bold tabular-nums text-indigo-700">${{ number_format($totRIngreso, 2) }}</td>
                                <td class="px-3 py-3 text-right font-bold tabular-nums text-slate-700">${{ number_format($totRCosto, 2) }}</td>
                                <td class="px-3 py-3 text-right font-bold tabular-nums text-emerald-700">${{ number_format($totRGan, 2) }}</td>
                                <td class="px-3 py-3 text-right font-bold tabular-nums">{{ number_format($totRIngreso > 0 ? ($totRGan / $totRIngreso) * 100 : 0, 1) }}%</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if($remitentesEnPerdida > 0)
                <p class="mt-2 text-sm text-rose-700"><i class="fas fa-triangle-exclamation"></i> {{ $remitentesEnPerdida }} remitente(s) con margen negativo. Revisá las tarifas que les estás cobrando.</p>
            @endif
        </section>
    @endif

    {{-- ╔═════════════ Gastos por categoría ═════════════╗ --}}
    <section class="grid grid-cols-1 gap-6 lg:grid-cols-5">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 lg:col-span-2">
            <h3 class="mb-3 flex items-center gap-2 text-base font-semibold text-slate-800"><i class="fas fa-chart-pie text-rose-600"></i> Gastos por categoría</h3>
            @if($gastosPorCategoria->isEmpty())
                <p class="py-10 text-center text-sm text-slate-500">Sin gastos en el período.</p>
            @else
                <div class="h-64"><canvas id="chartGastosCat"></canvas></div>
            @endif
        </div>
        <div class="lg:col-span-3 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h3 class="mb-3 flex items-center gap-2 text-base font-semibold text-slate-800"><i class="fas fa-list-ol text-rose-600"></i> Detalle de gastos del período</h3>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-3 py-2 font-semibold">Categoría</th>
                            <th class="px-3 py-2 font-semibold text-center">Mov.</th>
                            <th class="px-3 py-2 font-semibold text-right">Monto</th>
                            <th class="px-3 py-2 font-semibold text-right">%</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($gastosPorCategoria as $g)
                        @php $pct = $rentabilidadActual['gastos'] > 0 ? ((float) $g->total / $rentabilidadActual['gastos']) * 100 : 0; @endphp
                        <tr class="border-b border-slate-100">
                            <td class="px-3 py-2 font-medium text-slate-800">
                                @if($g->icono)<i class="fas {{ $g->icono }} mr-1 text-slate-400"></i>@endif
                                {{ $g->nombre }}
                            </td>
                            <td class="px-3 py-2 text-center text-slate-600 tabular-nums">{{ $g->cantidad }}</td>
                            <td class="px-3 py-2 text-right font-bold tabular-nums text-rose-700">${{ number_format((float) $g->total, 2) }}</td>
                            <td class="px-3 py-2 text-right text-slate-600 tabular-nums">{{ number_format($pct, 1) }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-3 py-10 text-center text-slate-500">Sin gastos cargados en el período. <a href="{{ route('contabilidad.gastos.create') }}" class="font-semibold text-[#15537c] hover:underline">Cargar el primero →</a></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- ╔═════════════ Estado de resultados ═════════════╗ --}}
    <section class="rounded-xl border-2 {{ $rentabilidadActual['neto'] >= 0 ? 'border-emerald-300' : 'border-rose-300' }} bg-gradient-to-br from-slate-50 to-white p-6 shadow-md">
        <div class="mb-4 flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $rentabilidadActual['neto'] >= 0 ? 'bg-emerald-500' : 'bg-rose-500' }} text-white shadow"><i class="fas fa-calculator"></i></span>
            <h2 class="text-xl font-bold text-slate-800">Estado de resultados del período</h2>
            <span class="ml-auto text-sm text-slate-500">{{ $rangoLabel }}</span>
        </div>

        <dl class="mx-auto max-w-xl space-y-2 font-mono text-base">
            {{-- Paquetería --}}
            <div class="flex items-center justify-between border-b border-dashed border-slate-200 pb-1">
                <dt class="text-slate-700"><i class="fas fa-box mr-1 text-[#15537c]"></i> Ingresos paquetería</dt>
                <dd class="font-bold tabular-nums text-[#15537c]">${{ number_format($rentaPaqueteria['ingreso'], 2) }}</dd>
            </div>
            <div class="flex items-center justify-between border-b border-dashed border-slate-100 pb-1 pl-4 text-xs text-slate-500">
                <dt>− Costo ({{ number_format($rentaPaqueteria['libras'], 1) }} lb)</dt>
                <dd class="tabular-nums">(${{ number_format($rentaPaqueteria['costo'], 2) }})</dd>
            </div>

            {{-- Encomiendas familiares --}}
            <div class="flex items-center justify-between border-b border-dashed border-slate-200 pb-1 pt-1">
                <dt class="text-slate-700"><i class="fas fa-people-carry-box mr-1 text-indigo-600"></i> Ingresos encomiendas familiares</dt>
                <dd class="font-bold tabular-nums text-indigo-700">${{ number_format($rentaEncomiendas['ingreso'], 2) }}</dd>
            </div>
            @if($rentaEncomiendas['pies3'] > 0 || $rentaEncomiendas['libras'] > 0)
                <div class="flex items-center justify-between border-b border-dashed border-slate-100 pb-1 pl-4 text-xs text-slate-500">
                    <dt>
                        − Costo
                        @if($rentaEncomiendas['pies3'] > 0) ({{ number_format($rentaEncomiendas['pies3'], 1) }} pie³ @endif
                        @if($rentaEncomiendas['libras'] > 0) {{ $rentaEncomiendas['pies3'] > 0 ? ' + ' : '(' }}{{ number_format($rentaEncomiendas['libras'], 1) }} lb @endif
                        )
                    </dt>
                    <dd class="tabular-nums">(${{ number_format($rentaEncomiendas['costo'], 2) }})</dd>
                </div>
            @endif

            {{-- Totales --}}
            <div class="flex items-center justify-between border-b border-dashed border-slate-200 pb-1 pt-2">
                <dt class="font-semibold text-slate-700">= Ingresos totales</dt>
                <dd class="font-bold tabular-nums text-[#15537c]">${{ number_format($rentabilidadActual['ingreso'], 2) }}</dd>
            </div>
            <div class="flex items-center justify-between border-b border-dashed border-slate-200 pb-1">
                <dt class="font-semibold text-slate-700">− Costo operativo total</dt>
                <dd class="font-bold tabular-nums text-slate-700">(${{ number_format($rentabilidadActual['costo'], 2) }})</dd>
            </div>
            <div class="flex items-center justify-between border-b-2 border-slate-300 pb-2 pt-1">
                <dt class="font-bold text-slate-800">Ganancia bruta</dt>
                <dd class="font-bold tabular-nums text-emerald-700">${{ number_format($rentabilidadActual['ganancia_bruta'], 2) }}</dd>
            </div>
            <div class="flex items-center justify-between border-b border-dashed border-slate-200 pb-1 pt-2">
                <dt class="text-slate-700">− Gastos extras del período</dt>
                <dd class="font-bold tabular-nums text-rose-700">(${{ number_format($rentabilidadActual['gastos'], 2) }})</dd>
            </div>
            <div class="mt-2 flex items-center justify-between rounded-lg {{ $rentabilidadActual['neto'] >= 0 ? 'bg-emerald-100' : 'bg-rose-100' }} p-3">
                <dt class="text-lg font-bold {{ $rentabilidadActual['neto'] >= 0 ? 'text-emerald-900' : 'text-rose-900' }}">RESULTADO NETO</dt>
                <dd class="text-2xl font-bold tabular-nums {{ $rentabilidadActual['neto'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">${{ number_format($rentabilidadActual['neto'], 2) }}</dd>
            </div>
            @if($rentabilidadActual['ingreso'] > 0)
                <div class="flex items-center justify-between pt-1 text-sm">
                    <dt class="text-slate-500">Margen neto</dt>
                    <dd class="font-semibold tabular-nums text-slate-700">{{ number_format($rentabilidadActual['margen_neto'], 2) }}%</dd>
                </div>
            @endif
        </dl>
    </section>

    {{-- ╔═════════════ Simulador de tarifa mínima ═════════════╗ --}}
    <section class="rounded-xl border border-violet-200 bg-gradient-to-br from-violet-50 to-white p-6 shadow-sm">
        <div class="mb-4 flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-500 text-white"><i class="fas fa-calculator"></i></span>
            <h2 class="text-xl font-bold text-slate-800">Simulador de tarifa mínima</h2>
        </div>
        <p class="mb-4 text-sm text-slate-600">Usalo cuando un cliente te pida descuento o vayas a negociar una tarifa nueva. Te dice cuál es la tarifa mínima a cobrar para mantener un margen objetivo.</p>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700">Servicio</label>
                <select id="simServicio" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-base">
                    @foreach($serviciosConCosto as $sc)
                        <option value="{{ $sc['costo'] }}" data-nombre="{{ $sc['nombre'] }}">{{ $sc['nombre'] }} (${{ number_format($sc['costo'], 4) }}/lb)</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">Margen objetivo (%)</label>
                <input id="simMargen" type="number" min="0" max="95" step="1" value="40" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-base">
                <input id="simMargenRange" type="range" min="0" max="80" step="5" value="40" class="mt-2 w-full">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">Costo / lb del servicio</label>
                <div class="mt-1 flex items-center rounded-lg border border-slate-300 bg-slate-100 px-3 py-2 text-base">
                    <span class="text-slate-500">$</span>
                    <span id="simCosto" class="ml-1 font-bold tabular-nums">0.0000</span>
                </div>
                <p class="mt-1 text-xs text-slate-500">Valor vigente del servicio</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">Tarifa mínima sugerida</label>
                <div class="mt-1 flex items-center rounded-lg bg-violet-600 px-3 py-2 text-base text-white shadow">
                    <span>$</span>
                    <span id="simTarifa" class="ml-1 text-xl font-bold tabular-nums">0.0000</span>
                    <span class="ml-2 text-sm">/lb</span>
                </div>
                <p class="mt-1 text-xs text-slate-500">Para el margen objetivo</p>
            </div>
        </div>
    </section>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ────── Donut de gastos por categoría ──────
    const ctxCat = document.getElementById('chartGastosCat');
    const gastosCat = @json($gastosPorCategoria->map(fn ($g) => ['nombre' => $g->nombre, 'total' => (float) $g->total])->values());
    if (ctxCat && gastosCat.length > 0) {
        const palette = ['#e11d48', '#f59e0b', '#7c3aed', '#0ea5e9', '#10b981', '#14b8a6', '#a855f7', '#f97316', '#ef4444', '#64748b'];
        new Chart(ctxCat, {
            type: 'doughnut',
            data: {
                labels: gastosCat.map(c => c.nombre),
                datasets: [{ data: gastosCat.map(c => c.total), backgroundColor: gastosCat.map((_, i) => palette[i % palette.length]), borderWidth: 2, borderColor: '#fff' }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '60%',
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 11, weight: '600' }, boxWidth: 12 } },
                    tooltip: { callbacks: { label: (ctx) => {
                        const total = ctx.dataset.data.reduce((a,b)=>a+b,0);
                        const p = total>0 ? (ctx.parsed/total*100).toFixed(1) : 0;
                        return `${ctx.label}: $${ctx.parsed.toLocaleString('en-US',{minimumFractionDigits:2})} (${p}%)`;
                    }}}
                }
            }
        });
    }

    // ────── Simulador de tarifa mínima (por servicio) ──────
    const simServicio = document.getElementById('simServicio');
    const simMargen = document.getElementById('simMargen');
    const simRange = document.getElementById('simMargenRange');
    const simCosto = document.getElementById('simCosto');
    const simTarifa = document.getElementById('simTarifa');

    function costoActual() {
        if (!simServicio) return 0;
        return parseFloat(simServicio.value) || 0;
    }

    function recalcSim() {
        const m = Math.min(95, Math.max(0, parseFloat(simMargen.value) || 0));
        const costo = costoActual();
        if (simCosto) simCosto.textContent = costo.toFixed(4);
        const tarifa = m >= 100 ? 0 : (costo > 0 ? costo / (1 - m/100) : 0);
        simTarifa.textContent = tarifa.toFixed(4);
    }
    if (simMargen && simRange) {
        simMargen.addEventListener('input', () => { simRange.value = simMargen.value; recalcSim(); });
        simRange.addEventListener('input', () => { simMargen.value = simRange.value; recalcSim(); });
    }
    if (simServicio) simServicio.addEventListener('change', recalcSim);
    recalcSim();
});
</script>
@endpush
@endsection
