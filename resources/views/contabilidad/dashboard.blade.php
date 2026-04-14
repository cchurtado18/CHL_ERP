@extends('layouts.app-new')

@section('title', 'Contabilidad - CH LOGISTICS ERP')
@section('navbar-title', 'Contabilidad')

@section('content')
<div class="mx-auto w-full max-w-[1400px] space-y-8">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-base text-emerald-800" role="alert">
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if(!empty($setupPendiente))
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-base text-amber-800" role="alert">
            Módulo contable pendiente de migración. Ejecuta <code class="rounded bg-amber-100 px-1">php artisan migrate</code> y luego <code class="rounded bg-amber-100 px-1">php artisan db:seed --class=ContaCuentaSeeder</code>.
        </div>
    @endif
    @if(empty($setupPendiente) && ($facturasContabilidadPendiente ?? 0) > 0)
        <div class="rounded-xl border border-amber-300 bg-amber-50/90 px-5 py-4 text-base text-amber-950 shadow-sm" role="alert">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="font-medium">
                    <i class="fas fa-calculator mr-2 text-amber-700"></i>
                    Hay <strong>{{ $facturasContabilidadPendiente }}</strong> factura(s) en <strong>Entregado y pagado</strong> con registro de cobro pendiente en Contabilidad.
                </p>
                <a href="{{ route('facturacion.index') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-[#15537c] px-4 py-2 text-sm font-semibold text-white hover:bg-[#0f3d5c]">Ver facturación</a>
            </div>
        </div>
    @endif

    {{-- Cabecera y acciones --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Contabilidad</h1>
                <p class="mt-1 text-base text-slate-600">Tablero financiero y control de cartera.</p>
            </div>
            <div class="flex flex-wrap gap-2 lg:justify-end">
                <a href="{{ route('contabilidad.asientos.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-base font-medium text-slate-700 shadow-sm hover:bg-slate-50"><i class="fas fa-list-alt text-[#15537c]"></i> Lista de asientos</a>
                <a href="{{ route('contabilidad.cobros.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-base font-medium text-slate-700 shadow-sm hover:bg-slate-50"><i class="fas fa-money-check-alt text-[#15537c]"></i> Lista de cobros</a>
                <a href="{{ route('contabilidad.cxc.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-base font-medium text-slate-700 shadow-sm hover:bg-slate-50"><i class="fas fa-file-invoice-dollar text-[#15537c]"></i> Lista CxC</a>
                <a href="{{ route('contabilidad.asientos.create') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-base font-medium text-slate-700 shadow-sm hover:bg-slate-50"><i class="fas fa-plus-circle text-[#15537c]"></i> Nuevo asiento</a>
                <a href="{{ route('contabilidad.cobros.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#15537c] px-5 py-2.5 text-base font-semibold text-white shadow-sm hover:bg-[#0f3d5c]"><i class="fas fa-hand-holding-usd"></i> Registrar cobro</a>
            </div>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-[#15537c]/10 text-2xl text-[#15537c]"><i class="fas fa-file-invoice"></i></div>
                <div>
                    <p class="text-sm font-medium uppercase tracking-wide text-slate-500">Facturado del mes</p>
                    <p class="text-2xl font-bold text-slate-900">${{ number_format($facturadoMes, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-2xl text-emerald-600"><i class="fas fa-coins"></i></div>
                <div>
                    <p class="text-sm font-medium uppercase tracking-wide text-slate-500">Cobrado del mes</p>
                    <p class="text-2xl font-bold text-emerald-700">${{ number_format($cobradoMes, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-2xl text-rose-600"><i class="fas fa-balance-scale"></i></div>
                <div>
                    <p class="text-sm font-medium uppercase tracking-wide text-slate-500">Saldo CxC total</p>
                    <p class="text-2xl font-bold text-rose-700">${{ number_format($saldoCxc, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Aging --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4 text-lg font-semibold text-slate-800">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#15537c]/10 text-[#15537c]"><i class="fas fa-hourglass-half"></i></span>
            Aging de cartera (cantidad de facturas)
        </h2>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4 text-center shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">0-30 días</div>
                <div class="mt-2 text-2xl font-bold text-slate-900">{{ $aging['0_30'] }}</div>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4 text-center shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">31-60 días</div>
                <div class="mt-2 text-2xl font-bold text-slate-900">{{ $aging['31_60'] }}</div>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4 text-center shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">61-90 días</div>
                <div class="mt-2 text-2xl font-bold text-slate-900">{{ $aging['61_90'] }}</div>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4 text-center shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">+90 días</div>
                <div class="mt-2 text-2xl font-bold text-slate-900">{{ $aging['90_plus'] }}</div>
            </div>
        </div>
    </div>

    {{-- Tabla CxC --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-2 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="flex items-center gap-2 text-lg font-semibold text-slate-800">
                <i class="fas fa-table text-[#15537c]"></i>
                Detalle CxC por factura (pendientes)
            </h2>
            <a href="{{ route('contabilidad.cxc.index') }}" class="inline-flex items-center gap-2 text-base font-semibold text-[#15537c] hover:underline">Ver lista completa <i class="fas fa-arrow-right text-sm"></i></a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] border-collapse text-left text-base text-black">
                <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                    <tr>
                        <th class="px-4 py-2.5 font-semibold">Factura</th>
                        <th class="px-4 py-2.5 font-semibold">Cliente</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Monto original</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Cobrado</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Faltante</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Acción</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($cxcPendientes as $cxc)
                    @php
                        $original = (float) $cxc->monto_original;
                        $faltante = (float) $cxc->saldo_actual;
                        $cobrado = max(0, $original - $faltante);
                    @endphp
                    <tr class="border-b border-slate-100 {{ $loop->iteration % 2 === 0 ? 'bg-slate-50' : 'bg-white' }} hover:bg-slate-100">
                        <td class="px-4 py-2 font-semibold text-[#15537c]">#{{ $cxc->factura_id }}</td>
                        <td class="px-4 py-2">
                            @php
                                $clienteNombre = $cxc->factura->cliente?->nombre_completo ?? null;
                                $remitenteNombre = $cxc->factura->encomienda?->remitente?->nombre_completo ?? null;
                                $nombreMostrado = $clienteNombre ?? $remitenteNombre ?? '—';
                                $esCliente = ! empty($clienteNombre);
                            @endphp
                            <div class="space-y-1">
                                <div class="font-medium text-black">{{ $nombreMostrado }}</div>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-sm font-semibold {{ $esCliente ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900' }}">
                                    {{ $esCliente ? 'Cliente empresa' : 'Remitente encomienda' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-2 text-center font-medium text-slate-900">${{ number_format($original, 2) }}</td>
                        <td class="px-4 py-2 text-center font-semibold text-emerald-700">${{ number_format($cobrado, 2) }}</td>
                        <td class="px-4 py-2 text-center font-semibold text-rose-700">${{ number_format($faltante, 2) }}</td>
                        <td class="px-4 py-2 text-right">
                            <div class="inline-flex flex-wrap items-center justify-end gap-2">
                                <a href="{{ route('facturacion.show', $cxc->factura_id) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50"><i class="fas fa-file-invoice text-[#15537c]"></i> Ver factura</a>
                                <a href="{{ route('contabilidad.cxc.show', $cxc->factura_id) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-[#15537c]/30 bg-[#15537c]/5 px-3 py-1.5 text-sm font-semibold text-[#15537c] hover:bg-[#15537c]/10"><i class="fas fa-search-dollar"></i> Detalle CxC</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-base text-slate-600">No hay facturas con saldo pendiente por cobrar.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
