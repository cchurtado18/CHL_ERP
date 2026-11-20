@extends('layouts.app-new')

@section('title', $voucher->folio.' - Voucher')
@section('navbar-title', 'Contabilidad')

@section('content')
<div class="mx-auto w-full max-w-3xl space-y-6">
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ $voucher->etiquetaTipo() }}</p>
                <h1 class="text-2xl font-bold text-slate-800">{{ $voucher->folio }}</h1>
                <p class="mt-1 text-base text-slate-600">{{ $voucher->concepto }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('contabilidad.vouchers.pdf', $voucher->id) }}" class="inline-flex items-center gap-2 rounded-xl bg-[#15537c] px-5 py-2.5 text-base font-semibold text-white hover:bg-[#0f3d5c]"><i class="fas fa-file-pdf"></i> PDF</a>
                <a href="{{ route('contabilidad.vouchers.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-base font-medium text-slate-700 hover:bg-slate-50">Lista</a>
            </div>
        </div>
        <dl class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-sm text-slate-500">Fecha</dt>
                <dd class="text-base font-medium">{{ $voucher->fecha?->format('d/m/Y') }}</dd>
            </div>
            <div>
                <dt class="text-sm text-slate-500">Monto</dt>
                <dd class="text-xl font-bold text-[#15537c]">${{ number_format((float)$voucher->monto, 2) }} {{ $voucher->moneda }}</dd>
            </div>
            <div>
                <dt class="text-sm text-slate-500">Tercero</dt>
                <dd class="text-base font-medium">{{ $voucher->tercero_nombre }}</dd>
            </div>
            <div>
                <dt class="text-sm text-slate-500">Método</dt>
                <dd class="text-base font-medium">{{ ucfirst($voucher->metodo ?: '—') }}</dd>
            </div>
            @if($voucher->referencia)
            <div class="sm:col-span-2">
                <dt class="text-sm text-slate-500">Referencia</dt>
                <dd class="text-base">{{ $voucher->referencia }}</dd>
            </div>
            @endif
        </dl>
    </div>
</div>
@endsection
