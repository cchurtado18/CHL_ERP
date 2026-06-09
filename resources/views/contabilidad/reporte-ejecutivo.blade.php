@extends('layouts.app-new')

@section('title', 'Reporte Ejecutivo - Contabilidad')
@section('navbar-title', 'Reporte Ejecutivo')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="mx-auto w-full max-w-[1400px] space-y-8">

    {{-- ─── Breadcrumb / Cabecera ─── --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <nav class="mb-2 flex items-center gap-2 text-sm text-slate-500">
                    <a href="{{ route('contabilidad.dashboard') }}" class="hover:text-[#15537c]">Contabilidad</a>
                    <i class="fas fa-chevron-right text-xs"></i>
                    <span class="font-semibold text-slate-700">Reporte ejecutivo</span>
                </nav>
                <h1 class="flex items-center gap-3 text-2xl font-bold text-slate-800">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-violet-600 to-fuchsia-600 text-white shadow"><i class="fas fa-chart-line text-xl"></i></span>
                    Reporte ejecutivo
                </h1>
                <p class="mt-1 text-base text-slate-600">Comparativo mes vs mes, ranking de clientes y cobros por cuenta.</p>
            </div>
            <a href="{{ route('contabilidad.dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-base font-medium text-slate-700 shadow-sm hover:bg-slate-50 lg:self-end">
                <i class="fas fa-arrow-left text-[#15537c]"></i> Volver al panel
            </a>
        </div>
    </div>

    @if(!empty($setupPendiente))
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-base text-amber-800" role="alert">
            Módulo contable pendiente de migración. Algunos indicadores aparecerán en cero.
        </div>
    @endif

    {{-- ╔═══════════════════════════════════════════════╗
         ║ SECCIÓN 1 — Comparativo Mes Actual vs Mes Anterior
         ╚═══════════════════════════════════════════════╝ --}}
    <section>
        <div class="mb-4 flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#15537c]/10 text-[#15537c]"><i class="fas fa-arrows-left-right"></i></span>
            <h2 class="text-xl font-bold text-slate-800">Mes actual vs mes anterior</h2>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600">
                {{ $labelMesActual }} <span class="text-slate-400">vs</span> {{ $labelMesAnterior }}
            </span>
        </div>

        @php
            $kpis = [
                ['titulo' => 'Facturado',       'actual' => $facturadoActual,   'anterior' => $facturadoAnterior,   'var' => $variaciones['facturado'], 'icon' => 'fa-file-invoice', 'bg' => 'bg-[#15537c]/10', 'text' => 'text-[#15537c]', 'formato' => 'money'],
                ['titulo' => 'Cobrado',         'actual' => $cobradoActual,     'anterior' => $cobradoAnterior,     'var' => $variaciones['cobrado'],   'icon' => 'fa-coins',         'bg' => 'bg-emerald-100',  'text' => 'text-emerald-700', 'formato' => 'money'],
                ['titulo' => 'Saldo CxC',       'actual' => $saldoCxcHoy,       'anterior' => $saldoCxcMesAnt,      'var' => $variaciones['cxc'],       'icon' => 'fa-balance-scale', 'bg' => 'bg-rose-100',     'text' => 'text-rose-700',     'formato' => 'money', 'inverso' => true],
                ['titulo' => 'Ticket promedio', 'actual' => $ticketActual,      'anterior' => $ticketAnterior,      'var' => $variaciones['ticket'],    'icon' => 'fa-receipt',       'bg' => 'bg-violet-100',   'text' => 'text-violet-700',   'formato' => 'money'],
            ];
        @endphp

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($kpis as $k)
                @php
                    $pct = $k['var']['pct'];
                    $dir = $k['var']['direccion'];
                    $inverso = $k['inverso'] ?? false;
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
                <div class="flex h-full flex-col justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $k['bg'] }} text-lg {{ $k['text'] }}"><i class="fas {{ $k['icon'] }}"></i></div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $k['titulo'] }}</p>
                            </div>
                            <span class="inline-flex shrink-0 items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-bold {{ $badgeClase }}" title="{{ $sinDato ? 'No hay datos comparables del mes anterior' : 'Cambio respecto al mes anterior' }}">
                                <i class="fas {{ $arrow }} text-[9px]"></i>
                                <span>{{ $badgeText }}</span>
                            </span>
                        </div>
                        <p class="mt-3 whitespace-nowrap text-2xl font-bold tabular-nums text-slate-900">${{ number_format($k['actual'], 2) }}</p>
                    </div>
                    <div class="mt-4 flex items-center justify-between gap-2 border-t border-slate-100 pt-3 text-xs text-slate-500">
                        <span class="shrink-0">Mes anterior</span>
                        <span class="whitespace-nowrap font-semibold tabular-nums text-slate-700">${{ number_format($k['anterior'], 2) }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Gráfica de 6 meses --}}
        <div class="mt-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h3 class="mb-4 flex items-center gap-2 border-b border-slate-100 pb-3 text-base font-semibold text-slate-800">
                <i class="fas fa-chart-column text-[#15537c]"></i>
                Evolución últimos 6 meses (Facturado vs Cobrado)
            </h3>
            <div class="h-80">
                <canvas id="chartMeses"></canvas>
            </div>
        </div>
    </section>

    {{-- ╔═══════════════════════════════════════════════╗
         ║ SECCIÓN 2 — Filtro de período para análisis
         ╚═══════════════════════════════════════════════╝ --}}
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="mb-4 flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#15537c]/10 text-[#15537c]"><i class="fas fa-filter"></i></span>
            <h2 class="text-xl font-bold text-slate-800">Análisis por período</h2>
        </div>

        <form method="GET" action="{{ route('contabilidad.reporte') }}" class="space-y-4">
            <div class="flex flex-wrap gap-2">
                @php
                    $presets = [
                        'mes_actual'   => ['label' => 'Este mes',       'icon' => 'fa-calendar-day'],
                        'mes_anterior' => ['label' => 'Mes anterior',   'icon' => 'fa-calendar-minus'],
                        'ultimos_30'   => ['label' => 'Últimos 30 días','icon' => 'fa-clock'],
                        'trimestre'    => ['label' => 'Trimestre',      'icon' => 'fa-calendar-week'],
                        'anio'         => ['label' => 'Año actual',     'icon' => 'fa-calendar'],
                    ];
                @endphp
                @foreach($presets as $key => $info)
                    <a href="{{ route('contabilidad.reporte', ['preset' => $key]) }}"
                       class="inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm font-semibold transition {{ $preset === $key ? 'border-[#15537c] bg-[#15537c] text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50' }}">
                        <i class="fas {{ $info['icon'] }}"></i> {{ $info['label'] }}
                    </a>
                @endforeach
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Desde</label>
                    <input type="date" name="desde" value="{{ $preset === 'custom' ? $desde->toDateString() : '' }}" class="mt-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Hasta</label>
                    <input type="date" name="hasta" value="{{ $preset === 'custom' ? $hasta->toDateString() : '' }}" class="mt-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
                </div>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#15537c] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#0f3d5c]"><i class="fas fa-search"></i> Aplicar rango</button>
            </div>
        </form>

        <div class="mt-4 rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-600">
            <i class="fas fa-info-circle mr-1 text-[#15537c]"></i>
            Período analizado: <strong class="text-slate-800">{{ $rangoLabel }}</strong> · Facturado: <strong class="text-[#15537c]">${{ number_format($facturadoRango, 2) }}</strong> · Cobrado: <strong class="text-emerald-700">${{ number_format($totalCobradoRango, 2) }}</strong>
        </div>
    </section>

    {{-- ╔═══════════════════════════════════════════════╗
         ║ SECCIÓN 3 — Cobros por cuenta
         ╚═══════════════════════════════════════════════╝ --}}
    <section>
        <div class="mb-4 flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700"><i class="fas fa-piggy-bank"></i></span>
            <h2 class="text-xl font-bold text-slate-800">¿Dónde entra más dinero? — Cobros por cuenta</h2>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 lg:col-span-2">
                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Distribución</h3>
                @if($cobrosPorCuenta->isEmpty())
                    <p class="py-10 text-center text-sm text-slate-500">Sin cobros en este período.</p>
                @else
                    <div class="relative h-64">
                        <canvas id="chartCuentas"></canvas>
                    </div>
                    <p class="mt-4 text-center text-sm text-slate-600">Total cobrado: <strong class="text-emerald-700">${{ number_format($totalCobradoRango, 2) }}</strong></p>
                @endif
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 lg:col-span-3">
                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Ranking de cuentas</h3>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-3 py-2 font-semibold">#</th>
                                <th class="px-3 py-2 font-semibold">Cuenta</th>
                                <th class="px-3 py-2 font-semibold text-center">Movs.</th>
                                <th class="px-3 py-2 font-semibold text-right">Cobrado</th>
                                <th class="px-3 py-2 font-semibold text-right">% del total</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($cobrosPorCuenta as $i => $cuenta)
                            @php
                                $total = (float) $cuenta->total;
                                $pct = $totalCobradoRango > 0 ? ($total / $totalCobradoRango) * 100 : 0;
                                $subtipoLabel = match($cuenta->subtipo) {
                                    'caja' => ['Caja', 'bg-amber-100 text-amber-800'],
                                    'banco' => ['Banco', 'bg-sky-100 text-sky-800'],
                                    default => [ucfirst($cuenta->subtipo ?? '—'), 'bg-slate-100 text-slate-700'],
                                };
                            @endphp
                            <tr class="border-b border-slate-100 {{ $i % 2 === 0 ? 'bg-white' : 'bg-slate-50/50' }}">
                                <td class="px-3 py-2.5 font-bold text-slate-400">{{ $i + 1 }}</td>
                                <td class="px-3 py-2.5">
                                    <div class="font-semibold text-slate-800">{{ $cuenta->nombre }}</div>
                                    <div class="mt-0.5 flex items-center gap-2">
                                        <span class="text-xs text-slate-500">{{ $cuenta->codigo }}</span>
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $subtipoLabel[1] }}">{{ $subtipoLabel[0] }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-2.5 text-center font-medium text-slate-700">{{ $cuenta->movimientos }}</td>
                                <td class="px-3 py-2.5 text-right font-bold text-emerald-700">${{ number_format($total, 2) }}</td>
                                <td class="px-3 py-2.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <span class="font-semibold text-slate-700">{{ number_format($pct, 1) }}%</span>
                                        <div class="hidden h-2 w-20 overflow-hidden rounded-full bg-slate-200 sm:block">
                                            <div class="h-full bg-[#15537c]" style="width: {{ min(100, $pct) }}%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-10 text-center text-slate-500">Sin movimientos en el período.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    {{-- ╔═══════════════════════════════════════════════╗
         ║ SECCIÓN 4 — Top 10 clientes facturados
         ╚═══════════════════════════════════════════════╝ --}}
    <section>
        <div class="mb-4 flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-100 text-violet-700"><i class="fas fa-crown"></i></span>
            <h2 class="text-xl font-bold text-slate-800">Top 10 clientes facturados</h2>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px] border-collapse text-left text-base text-black">
                    <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                        <tr>
                            <th class="px-4 py-3 font-semibold">#</th>
                            <th class="px-4 py-3 font-semibold">Cliente / Remitente</th>
                            <th class="px-4 py-3 font-semibold text-center">Facturas</th>
                            <th class="px-4 py-3 font-semibold text-right">Facturado</th>
                            <th class="px-4 py-3 font-semibold text-right">Cobrado</th>
                            <th class="px-4 py-3 font-semibold text-right">Saldo</th>
                            <th class="px-4 py-3 font-semibold text-right">% facturado</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($topClientes as $i => $c)
                        @php
                            $pct = $facturadoRango > 0 ? ($c['facturado'] / $facturadoRango) * 100 : 0;
                            $tipoLabel = match($c['tipo']) {
                                'cliente'   => ['Cliente empresa', 'bg-emerald-100 text-emerald-800'],
                                'remitente' => ['Remitente encomienda', 'bg-amber-100 text-amber-900'],
                                default     => ['Sin identificar', 'bg-slate-100 text-slate-700'],
                            };
                            $medalla = match($i) {
                                0 => 'bg-yellow-400 text-white',
                                1 => 'bg-slate-400 text-white',
                                2 => 'bg-amber-600 text-white',
                                default => 'bg-slate-100 text-slate-500',
                            };
                        @endphp
                        <tr class="border-b border-slate-100 {{ $i % 2 === 0 ? 'bg-white' : 'bg-slate-50' }} hover:bg-slate-100">
                            <td class="px-4 py-3">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold {{ $medalla }}">{{ $i + 1 }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-900">{{ $c['nombre'] }}</div>
                                <span class="mt-1 inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $tipoLabel[1] }}">{{ $tipoLabel[0] }}</span>
                            </td>
                            <td class="px-4 py-3 text-center font-medium">{{ $c['facturas'] }}</td>
                            <td class="px-4 py-3 text-right font-bold text-[#15537c]">${{ number_format($c['facturado'], 2) }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-700">${{ number_format($c['cobrado'], 2) }}</td>
                            <td class="px-4 py-3 text-right font-semibold {{ $c['saldo'] > 0 ? 'text-rose-700' : 'text-slate-400' }}">${{ number_format($c['saldo'], 2) }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <span class="font-semibold text-slate-700">{{ number_format($pct, 1) }}%</span>
                                    <div class="hidden h-2 w-24 overflow-hidden rounded-full bg-slate-200 sm:block">
                                        <div class="h-full bg-violet-500" style="width: {{ min(100, $pct) }}%"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center text-slate-500">Sin facturas en el período seleccionado.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const serieMeses = @json($serieMeses);
    const cuentas    = @json($cobrosPorCuenta->map(fn($c) => ['nombre' => $c->nombre, 'total' => (float) $c->total])->values());
    const palette = ['#15537c', '#10b981', '#f59e0b', '#7c3aed', '#0ea5e9', '#e11d48', '#14b8a6', '#a855f7'];

    const ctxMeses = document.getElementById('chartMeses');
    if (ctxMeses && serieMeses.length > 0) {
        new Chart(ctxMeses, {
            type: 'bar',
            data: {
                labels: serieMeses.map(m => m.label),
                datasets: [
                    { label: 'Facturado', data: serieMeses.map(m => m.facturado), backgroundColor: '#15537c', borderRadius: 6 },
                    { label: 'Cobrado',   data: serieMeses.map(m => m.cobrado),   backgroundColor: '#10b981', borderRadius: 6 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 13, weight: '600' } } },
                    tooltip: { callbacks: { label: (ctx) => `${ctx.dataset.label}: $${ctx.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}` } }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: (v) => '$' + v.toLocaleString('en-US') }, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    const ctxCuentas = document.getElementById('chartCuentas');
    if (ctxCuentas && cuentas.length > 0) {
        new Chart(ctxCuentas, {
            type: 'doughnut',
            data: {
                labels: cuentas.map(c => c.nombre),
                datasets: [{
                    data: cuentas.map(c => c.total),
                    backgroundColor: cuentas.map((_, i) => palette[i % palette.length]),
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 12, weight: '600' }, boxWidth: 14 } },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = total > 0 ? (ctx.parsed / total * 100).toFixed(1) : 0;
                                return `${ctx.label}: $${ctx.parsed.toLocaleString('en-US', {minimumFractionDigits: 2})} (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
