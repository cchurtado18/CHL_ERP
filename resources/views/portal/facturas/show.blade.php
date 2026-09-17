@extends('layouts.portal')

@section('title', 'Detalle de factura')
@section('navbar-title', 'Detalle de factura')

@section('content')
<div class="mb-6 flex flex-wrap items-start justify-between gap-4">
    <div>
        <a href="{{ route('portal.facturas.index') }}" class="text-sm font-semibold text-[#15537c] hover:underline">&larr; Volver a facturas</a>
        <h1 class="mt-2 text-2xl font-extrabold text-slate-800">Factura {{ $factura->etiquetaFolio() }}</h1>
        <p class="text-sm text-slate-500">{{ \Carbon\Carbon::parse($factura->fecha_factura)->format('d/m/Y') }}</p>
    </div>
    <a href="{{ route('portal.facturas.pdf', $factura->id) }}"
       class="inline-flex items-center gap-2 rounded-xl bg-[#15537c] px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#0f3d5c]">
        <i class="fas fa-file-pdf"></i> Descargar PDF
    </a>
</div>

<div class="mb-6 grid gap-4 sm:grid-cols-3">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-bold uppercase text-slate-400">Monto</p>
        <p class="mt-1 text-2xl font-extrabold text-[#15537c]">${{ number_format($factura->monto_total, 2) }}</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-bold uppercase text-slate-400">Estado de pago</p>
        <p class="mt-1 text-lg font-bold capitalize text-slate-800">{{ str_replace('_',' ', $factura->estado_pago) }}</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-bold uppercase text-slate-400">Tipo</p>
        <p class="mt-1 text-lg font-bold capitalize text-slate-800">{{ str_replace('_',' ', $factura->tipo_factura ?? 'paqueteria') }}</p>
    </div>
</div>

@if($factura->paquetes->isNotEmpty())
<section class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-3 font-bold text-slate-800">Paquetes incluidos</div>
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-left text-slate-500">
            <tr>
                <th class="px-4 py-2">Tracking / Guía</th>
                <th class="px-4 py-2">Servicio</th>
                <th class="px-4 py-2">Peso</th>
                <th class="px-4 py-2">Monto</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($factura->paquetes as $p)
                <tr>
                    <td class="px-4 py-2">
                        <a href="{{ route('portal.paquetes.show', $p->id) }}" class="font-semibold text-[#15537c] hover:underline">
                            {{ $p->tracking_codigo ?: $p->numero_guia ?: ('#'.$p->id) }}
                        </a>
                    </td>
                    <td class="px-4 py-2 capitalize">{{ $p->servicio->tipo_servicio ?? '—' }}</td>
                    <td class="px-4 py-2">{{ number_format($p->peso_lb ?? 0, 2) }} lb</td>
                    <td class="px-4 py-2">${{ number_format($p->monto_calculado ?? 0, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</section>
@endif

@if($factura->pagos->isNotEmpty())
<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-3 font-bold text-slate-800">Pagos registrados</div>
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-left text-slate-500">
            <tr>
                <th class="px-4 py-2">Fecha</th>
                <th class="px-4 py-2">Monto</th>
                <th class="px-4 py-2">Método</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($factura->pagos as $pago)
                <tr>
                    <td class="px-4 py-2">{{ $pago->fecha_pago ? \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') : '—' }}</td>
                    <td class="px-4 py-2 font-semibold">${{ number_format($pago->monto_pagado ?? 0, 2) }}</td>
                    <td class="px-4 py-2 capitalize">{{ $pago->metodo_pago ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</section>
@endif
@endsection
