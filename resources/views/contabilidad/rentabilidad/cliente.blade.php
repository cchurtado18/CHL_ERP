@extends('layouts.app-new')

@section('title', 'Rentabilidad de '.$cliente->nombre_completo)
@section('navbar-title', $cliente->nombre_completo)

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="mx-auto w-full max-w-[1400px] space-y-8">

    {{-- Cabecera --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <nav class="mb-2 flex items-center gap-2 text-sm text-slate-500">
                    <a href="{{ route('contabilidad.dashboard') }}" class="hover:text-[#15537c]">Contabilidad</a>
                    <i class="fas fa-chevron-right text-xs"></i>
                    <a href="{{ route('contabilidad.rentabilidad.index', ['preset' => $preset]) }}" class="hover:text-[#15537c]">Rentabilidad</a>
                    <i class="fas fa-chevron-right text-xs"></i>
                    <span class="font-semibold text-slate-700">{{ $cliente->nombre_completo }}</span>
                </nav>
                <h1 class="flex items-center gap-3 text-2xl font-bold text-slate-800">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow"><i class="fas fa-user-tag text-xl"></i></span>
                    {{ $cliente->nombre_completo }}
                </h1>
                <p class="mt-1 text-base text-slate-600">{{ $cliente->tipo_cliente }} · {{ $rangoLabel }}</p>
            </div>
            <a href="{{ route('contabilidad.rentabilidad.index', ['preset' => $preset, 'desde' => request('desde'), 'hasta' => request('hasta')]) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"><i class="fas fa-arrow-left"></i> Volver al reporte</a>
        </div>
    </div>

    {{-- KPIs del cliente --}}
    @php
        $estadoCfg = match($estado) {
            'perdida'   => ['Pérdida', 'border-rose-300 bg-rose-50', 'text-rose-700', 'fa-circle-xmark'],
            'bajo'      => ['Margen bajo', 'border-amber-300 bg-amber-50', 'text-amber-700', 'fa-triangle-exclamation'],
            'saludable' => ['Saludable', 'border-emerald-300 bg-emerald-50', 'text-emerald-700', 'fa-circle-check'],
        };
    @endphp

    @if($estado === 'perdida')
        <div class="rounded-xl border-2 border-rose-300 bg-rose-50 px-5 py-4 text-base text-rose-900 shadow-sm">
            <p class="font-semibold"><i class="fas fa-circle-exclamation mr-1"></i> Estás perdiendo dinero con este cliente.</p>
            <p class="mt-1 text-sm">Su tarifa promedio (${{ number_format($tarifaPromedio, 4) }}/lb) está por debajo de tu costo promedio ponderado (${{ number_format($costoPromedio, 4) }}/lb). Renegociá su tarifa o evaluá si conviene seguir atendiéndolo.</p>
        </div>
    @endif

    <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500"><i class="fas fa-weight-hanging mr-1 text-sky-600"></i> Libras</p>
            <p class="mt-1 text-2xl font-bold tabular-nums text-slate-900">{{ number_format($totalLibras, 1) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500"><i class="fas fa-tag mr-1 text-slate-500"></i> Tarifa prom.</p>
            <p class="mt-1 text-2xl font-bold tabular-nums text-slate-900">${{ number_format($tarifaPromedio, 2) }}<span class="text-sm font-medium text-slate-500">/lb</span></p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500"><i class="fas fa-dollar-sign mr-1 text-[#15537c]"></i> Ingreso</p>
            <p class="mt-1 text-2xl font-bold tabular-nums text-[#15537c]">${{ number_format($totalIngreso, 2) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500"><i class="fas fa-minus-circle mr-1 text-slate-500"></i> Costo op.</p>
            <p class="mt-1 text-2xl font-bold tabular-nums text-slate-700">${{ number_format($totalCosto, 2) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500"><i class="fas fa-arrow-trend-up mr-1 text-emerald-600"></i> Ganancia</p>
            <p class="mt-1 text-2xl font-bold tabular-nums {{ $totalGanancia >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">${{ number_format($totalGanancia, 2) }}</p>
            <p class="text-xs font-semibold {{ $margen >= 15 ? 'text-emerald-600' : ($margen >= 0 ? 'text-amber-600' : 'text-rose-600') }}">Margen {{ number_format($margen, 1) }}%</p>
        </div>
        <div class="rounded-xl border-2 {{ $estadoCfg[1] }} p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500"><i class="fas fa-flag mr-1"></i> Estado</p>
            <p class="mt-1 text-base font-bold {{ $estadoCfg[2] }}"><i class="fas {{ $estadoCfg[3] }}"></i> {{ $estadoCfg[0] }}</p>
        </div>
    </div>

    {{-- Detalle de paquetes --}}
    <section>
        <div class="mb-3 flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#15537c]/10 text-[#15537c]"><i class="fas fa-boxes-stacked"></i></span>
            <h2 class="text-xl font-bold text-slate-800">Paquetes del cliente en el período</h2>
            <span class="ml-auto text-sm text-slate-500">{{ count($paquetes) }} paquete(s)</span>
        </div>
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1000px] border-collapse text-left text-sm">
                    <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                        <tr>
                            <th class="px-3 py-2.5 font-semibold">Fecha</th>
                            <th class="px-3 py-2.5 font-semibold">Guía / Tracking</th>
                            <th class="px-3 py-2.5 font-semibold">Servicio</th>
                            <th class="px-3 py-2.5 font-semibold text-right">Peso</th>
                            <th class="px-3 py-2.5 font-semibold text-right">Tarifa</th>
                            <th class="px-3 py-2.5 font-semibold text-right">Ingreso</th>
                            <th class="px-3 py-2.5 font-semibold text-right">Costo</th>
                            <th class="px-3 py-2.5 font-semibold text-right">Ganancia</th>
                            <th class="px-3 py-2.5 font-semibold text-right">Margen</th>
                            <th class="px-3 py-2.5 font-semibold text-right">Factura</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($paquetes as $p)
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="px-3 py-2 font-medium text-slate-800">{{ \Carbon\Carbon::parse($p->fecha_factura)->format('d/m/Y') }}</td>
                            <td class="px-3 py-2 text-slate-700">{{ $p->numero_guia ?? $p->tracking_codigo ?? '#'.$p->id }}</td>
                            <td class="px-3 py-2 text-slate-600">{{ $p->servicio ?? '—' }}</td>
                            <td class="px-3 py-2 text-right tabular-nums text-slate-800">{{ number_format($p->peso, 1) }} lb</td>
                            <td class="px-3 py-2 text-right tabular-nums text-slate-700">${{ number_format($p->tarifa_usada, 2) }}/lb</td>
                            <td class="px-3 py-2 text-right font-semibold tabular-nums text-[#15537c]">${{ number_format($p->ingreso, 2) }}</td>
                            <td class="px-3 py-2 text-right tabular-nums text-slate-600">
                                ${{ number_format($p->costo, 2) }}
                                <span class="block text-[10px] text-slate-400">${{ number_format($p->costo_unit, 4) }}/lb</span>
                            </td>
                            <td class="px-3 py-2 text-right font-bold tabular-nums {{ $p->ganancia >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">${{ number_format($p->ganancia, 2) }}</td>
                            <td class="px-3 py-2 text-right font-semibold tabular-nums {{ $p->margen >= 15 ? 'text-emerald-700' : ($p->margen >= 0 ? 'text-amber-700' : 'text-rose-700') }}">{{ number_format($p->margen, 1) }}%</td>
                            <td class="px-3 py-2 text-right">
                                <a href="{{ route('facturacion.show', $p->factura_id) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-[#15537c] hover:underline">
                                    F-{{ $p->folio ?? $p->factura_id }} <i class="fas fa-external-link-alt text-[10px]"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="px-4 py-12 text-center text-slate-500">Sin paquetes de paquetería en el período seleccionado.</td></tr>
                    @endforelse
                    </tbody>
                    @if(count($paquetes) > 0)
                        <tfoot class="border-t-2 border-slate-300 bg-slate-100">
                            <tr>
                                <td class="px-3 py-3 font-bold text-slate-800" colspan="3">TOTALES</td>
                                <td class="px-3 py-3 text-right font-bold tabular-nums text-slate-900">{{ number_format($totalLibras, 1) }} lb</td>
                                <td class="px-3 py-3 text-right font-bold tabular-nums text-slate-700">${{ number_format($tarifaPromedio, 2) }}/lb</td>
                                <td class="px-3 py-3 text-right font-bold tabular-nums text-[#15537c]">${{ number_format($totalIngreso, 2) }}</td>
                                <td class="px-3 py-3 text-right font-bold tabular-nums text-slate-700">${{ number_format($totalCosto, 2) }}</td>
                                <td class="px-3 py-3 text-right font-bold tabular-nums {{ $totalGanancia >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">${{ number_format($totalGanancia, 2) }}</td>
                                <td class="px-3 py-3 text-right font-bold tabular-nums">{{ number_format($margen, 1) }}%</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </section>

    {{-- Histórico 6 meses --}}
    <section>
        <div class="mb-3 flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-100 text-violet-700"><i class="fas fa-chart-line"></i></span>
            <h2 class="text-xl font-bold text-slate-800">Histórico últimos 6 meses</h2>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="h-80">
                <canvas id="chartHistorico"></canvas>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="w-full border-collapse text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-3 py-2 font-semibold">Mes</th>
                            <th class="px-3 py-2 font-semibold text-right">Libras</th>
                            <th class="px-3 py-2 font-semibold text-right">Ingreso</th>
                            <th class="px-3 py-2 font-semibold text-right">Costo</th>
                            <th class="px-3 py-2 font-semibold text-right">Ganancia</th>
                            <th class="px-3 py-2 font-semibold text-right">Margen %</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($historico as $h)
                        <tr class="border-b border-slate-100">
                            <td class="px-3 py-2 font-medium text-slate-800">{{ $h['label'] }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ number_format($h['libras'], 1) }} lb</td>
                            <td class="px-3 py-2 text-right tabular-nums text-[#15537c]">${{ number_format($h['ingreso'], 2) }}</td>
                            <td class="px-3 py-2 text-right tabular-nums text-slate-600">${{ number_format($h['costo'], 2) }}</td>
                            <td class="px-3 py-2 text-right font-semibold tabular-nums {{ $h['ganancia'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">${{ number_format($h['ganancia'], 2) }}</td>
                            <td class="px-3 py-2 text-right font-semibold tabular-nums {{ $h['margen'] >= 15 ? 'text-emerald-700' : ($h['margen'] >= 0 ? 'text-amber-700' : 'text-rose-700') }}">{{ number_format($h['margen'], 1) }}%</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const historico = @json($historico);
    const ctx = document.getElementById('chartHistorico');
    if (!ctx || historico.length === 0) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: historico.map(h => h.label),
            datasets: [
                { label: 'Libras', data: historico.map(h => h.libras), backgroundColor: '#0ea5e9', borderRadius: 6, yAxisID: 'y' },
                { type: 'line', label: 'Ganancia ($)', data: historico.map(h => h.ganancia), borderColor: '#10b981', backgroundColor: '#10b981', tension: 0.3, yAxisID: 'y1', pointRadius: 4 },
                { type: 'line', label: 'Margen %', data: historico.map(h => h.margen), borderColor: '#7c3aed', backgroundColor: '#7c3aed', borderDash: [5, 5], tension: 0.3, yAxisID: 'y2', pointRadius: 4 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'bottom', labels: { font: { size: 12, weight: '600' } } } },
            scales: {
                y:  { type: 'linear', position: 'left', title: { display: true, text: 'Libras' }, beginAtZero: true },
                y1: { type: 'linear', position: 'right', title: { display: true, text: 'Ganancia ($)' }, beginAtZero: true, grid: { drawOnChartArea: false } },
                y2: { type: 'linear', display: false, beginAtZero: true }
            }
        }
    });
});
</script>
@endpush
@endsection
