@extends('layouts.app-new')

@section('title', 'Cobro #' . $cobro->id . ' - Contabilidad')
@section('navbar-title', 'Contabilidad')

@section('content')
<div class="mx-auto w-full max-w-[1400px] space-y-8 pb-10">
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-[#15537c] to-[#0f3d5c] p-6 text-white shadow-md sm:p-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm font-medium text-white/80">Cobros</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight sm:text-3xl">Cobro #{{ $cobro->id }}</h1>
                <p class="mt-2 text-sm text-white/85">Detalle del pago registrado.</p>
            </div>
            <a href="{{ route('contabilidad.cobros.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-medium text-white backdrop-blur-sm transition hover:bg-white/20"><i class="fas fa-arrow-left"></i> Volver a lista</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Factura relacionada</div>
            <div class="mt-1 text-xl font-bold text-[#15537c]">#{{ $cobro->factura_id }}</div>
            <div class="mt-1 text-base text-slate-700">{{ $cobro->factura->cliente?->nombre_completo ?? $cobro->factura->encomienda?->remitente?->nombre_completo ?? 'Sin cliente' }}</div>
            <a href="{{ route('facturacion.show', $cobro->factura_id) }}" class="mt-3 inline-flex items-center gap-2 text-base font-semibold text-[#15537c] hover:underline"><i class="fas fa-external-link-alt text-sm"></i> Ver factura</a>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cuenta banco / caja</div>
            <div class="mt-1 text-lg font-bold text-slate-900">{{ $cobro->cuentaBancoCaja->codigo ?? '' }} {{ $cobro->cuentaBancoCaja->nombre ?? '—' }}</div>
            <div class="mt-2 text-base text-slate-600">Método: <span class="font-medium text-slate-900">{{ $cobro->metodo }}</span></div>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="mb-5 flex items-center gap-2 border-b border-slate-100 pb-4 text-lg font-semibold text-slate-800"><i class="fas fa-info-circle text-[#15537c]"></i> Datos del cobro</h2>
        <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Fecha pago</dt>
                <dd class="mt-1 text-base font-bold text-slate-900">{{ $cobro->fecha_pago?->format('d/m/Y') }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Monto</dt>
                <dd class="mt-1 text-base font-bold text-emerald-700">${{ number_format((float)$cobro->monto,2) }} {{ $cobro->moneda }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tasa de cambio</dt>
                <dd class="mt-1 text-base font-bold text-slate-900">{{ $cobro->tasa_cambio !== null ? number_format((float)$cobro->tasa_cambio,4) : '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Referencia</dt>
                <dd class="mt-1 text-base font-bold text-slate-900">{{ $cobro->referencia ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Comisión</dt>
                <dd class="mt-1 text-base font-bold text-slate-900">{{ $cobro->comision !== null ? '$'.number_format((float)$cobro->comision,2) : '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Registrado por</dt>
                <dd class="mt-1 text-base font-bold text-slate-900">{{ $cobro->creador->nombre ?? $cobro->creador->email ?? '—' }}</dd>
            </div>
        </dl>
    </div>
</div>
@endsection
