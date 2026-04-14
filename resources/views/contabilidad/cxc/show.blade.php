@extends('layouts.app-new')

@section('title', 'Detalle CxC - Factura #' . $cxc->factura_id)
@section('navbar-title', 'Contabilidad')

@section('content')
@php
    $clienteNombre = $cxc->factura->cliente?->nombre_completo ?? null;
    $remitenteNombre = $cxc->factura->encomienda?->remitente?->nombre_completo ?? null;
    $nombreMostrado = $clienteNombre ?? $remitenteNombre ?? '—';
    $esCliente = !empty($clienteNombre);
@endphp
<div class="mx-auto w-full max-w-[1400px] space-y-8 pb-10">
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-[#15537c] to-[#0f3d5c] p-6 text-white shadow-md sm:p-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm font-medium text-white/80">Cuentas por cobrar</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight sm:text-3xl">Detalle CxC — Factura #{{ $cxc->factura_id }}</h1>
                <p class="mt-2 text-sm text-white/85">Saldos y cobros aplicados a esta factura.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('contabilidad.cxc.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-medium text-white backdrop-blur-sm transition hover:bg-white/20"><i class="fas fa-arrow-left"></i> Lista CxC</a>
                <a href="{{ route('facturacion.show', $cxc->factura_id) }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/30 bg-white px-4 py-2.5 text-sm font-semibold text-[#15537c] transition hover:bg-slate-50"><i class="fas fa-file-invoice"></i> Ver factura</a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cliente / Remitente</div>
            <div class="mt-1 text-lg font-bold text-slate-900">{{ $nombreMostrado }}</div>
            <span class="mt-2 inline-flex items-center rounded-full px-2.5 py-1 text-sm font-semibold {{ $esCliente ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900' }}">
                {{ $esCliente ? 'Cliente empresa' : 'Remitente encomienda' }}
            </span>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Monto original</div>
            <div class="mt-1 text-lg font-bold text-slate-900">${{ number_format((float) $original, 2) }}</div>
            <div class="mt-2 text-sm text-slate-600">Emisión: {{ $cxc->fecha_emision?->format('d/m/Y') ?? '—' }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Faltante por cobrar</div>
            <div class="mt-1 text-lg font-bold {{ $faltante > 0 ? 'text-rose-700' : 'text-emerald-700' }}">${{ number_format((float) $faltante, 2) }}</div>
            <div class="mt-2 text-sm text-slate-600">Vencimiento: {{ $cxc->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</div>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-2 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="flex items-center gap-2 text-lg font-semibold text-slate-800"><i class="fas fa-hand-holding-usd text-[#15537c]"></i> Cobros aplicados</h2>
            <p class="text-base text-slate-600">Total cobrado: <span class="font-bold text-emerald-700">${{ number_format((float) $cobrado, 2) }}</span></p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] border-collapse text-left text-base text-black">
                <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                    <tr>
                        <th class="px-4 py-2.5 font-semibold text-center">Fecha</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Monto</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Moneda</th>
                        <th class="px-4 py-2.5 font-semibold">Método</th>
                        <th class="px-4 py-2.5 font-semibold">Cuenta</th>
                        <th class="px-4 py-2.5 font-semibold">Referencia</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cobros as $i => $c)
                        <tr class="border-b border-slate-100 {{ $i % 2 === 1 ? 'bg-slate-50' : 'bg-white' }}">
                            <td class="px-4 py-2 text-center text-slate-900">{{ $c->fecha_pago?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-4 py-2 text-right font-semibold text-emerald-700">${{ number_format((float)$c->monto,2) }}</td>
                            <td class="px-4 py-2 text-center text-slate-800">{{ $c->moneda }}</td>
                            <td class="px-4 py-2 text-slate-800">{{ $c->metodo }}</td>
                            <td class="px-4 py-2 text-sm text-slate-700">{{ $c->cuentaBancoCaja->codigo ?? '' }} {{ $c->cuentaBancoCaja->nombre ?? '' }}</td>
                            <td class="px-4 py-2 text-slate-700">{{ $c->referencia ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-base text-slate-600">
                                No hay cobros en <code class="rounded bg-slate-100 px-1 text-sm">conta_cobros</code> para esta factura (puede haber pagos históricos en <code class="rounded bg-slate-100 px-1 text-sm">pagos</code>).
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
