@extends('layouts.app-new')

@section('title', 'CxP '.$cxp->etiquetaDocumento().' - Contabilidad')
@section('navbar-title', 'Contabilidad')

@section('content')
<div class="mx-auto w-full max-w-4xl space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-base text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-base text-red-800">
            <ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">{{ $cxp->etiquetaDocumento() }}</h1>
                <p class="mt-1 text-base text-slate-600">{{ $cxp->proveedor->nombre ?? 'Proveedor' }} · {{ $cxp->descripcion }}</p>
            </div>
            <a href="{{ route('contabilidad.cxp.index') }}" class="inline-flex items-center gap-2 font-medium text-[#15537c] hover:underline"><i class="fas fa-arrow-left"></i> Volver a CxP</a>
        </div>
        @php
            $montoPaquetes = $cxp->montoPaquetesAsignados();
            $diffPaquetes = $cxp->diferenciaPaquetesVsFactura();
        @endphp
        <dl class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div>
                <dt class="text-sm text-slate-500">Monto de la factura</dt>
                <dd class="text-lg font-semibold">${{ number_format((float)$cxp->monto_original, 2) }}</dd>
            </div>
            <div>
                <dt class="text-sm text-slate-500">Paquetes asignados</dt>
                <dd class="text-lg font-semibold text-[#15537c]">${{ number_format($montoPaquetes, 2) }}</dd>
                <p class="text-xs text-slate-500">{{ $cxp->paquetes->count() }} paquete(s)</p>
            </div>
            <div>
                <dt class="text-sm text-slate-500">Saldo por pagar</dt>
                <dd class="text-lg font-bold {{ (float)$cxp->saldo_actual > 0 ? 'text-rose-700' : 'text-emerald-700' }}">${{ number_format((float)$cxp->saldo_actual, 2) }}</dd>
            </div>
            <div>
                <dt class="text-sm text-slate-500">Estado</dt>
                <dd class="text-base font-medium">{{ ucwords(str_replace('_',' ', $cxp->estado_pago)) }}</dd>
            </div>
        </dl>
        @if($cxp->paquetes->isNotEmpty() || $montoPaquetes > 0 || abs($diffPaquetes) > 0.009)
            <p class="mt-4 rounded-lg px-3 py-2 text-sm {{ $diffPaquetes > 0.009 ? 'bg-amber-50 text-amber-900' : ($diffPaquetes < -0.009 ? 'bg-rose-50 text-rose-800' : 'bg-emerald-50 text-emerald-800') }}">
                @if($diffPaquetes > 0.009)
                    Aún faltan ${{ number_format($diffPaquetes, 2) }} de paquetes por ingresar a esta factura.
                @elseif($diffPaquetes < -0.009)
                    Los paquetes asignados superan el monto de la factura por ${{ number_format(abs($diffPaquetes), 2) }}.
                @else
                    El total de paquetes coincide con el monto de la factura.
                @endif
            </p>
        @endif
    </div>

    @if((float)$cxp->saldo_actual > 0.00001)
    <form method="POST" action="{{ route('contabilidad.cxp.pagar', $cxp->id) }}" enctype="multipart/form-data" class="space-y-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        @csrf
        <h2 class="text-lg font-semibold text-slate-800">Cancelar al proveedor</h2>
        <p class="text-sm text-slate-600">Número de referencia y foto del comprobante. Si el saldo queda en cero, los paquetes de esta factura se marcan cancelados.</p>
        <input type="hidden" name="moneda" value="{{ $cxp->moneda ?? 'USD' }}">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Monto *</label>
                <input type="number" step="0.01" min="0.01" max="{{ number_format((float)$cxp->saldo_actual, 2, '.', '') }}" name="monto" required value="{{ old('monto', number_format((float)$cxp->saldo_actual, 2, '.', '')) }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Fecha *</label>
                <input type="date" name="fecha_pago" required value="{{ old('fecha_pago', now()->toDateString()) }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Método *</label>
                <select name="metodo" required class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-base">
                    @foreach(['efectivo'=>'Efectivo','transferencia'=>'Transferencia','cheque'=>'Cheque','tarjeta'=>'Tarjeta','otro'=>'Otro'] as $val=>$lab)
                        <option value="{{ $val }}" @selected(old('metodo')===$val)>{{ $lab }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Banco / caja *</label>
                <select name="cuenta_banco_caja_id" required class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-base">
                    @foreach($cuentasBancoCaja as $c)
                        <option value="{{ $c->id }}" @selected((string)old('cuenta_banco_caja_id')===(string)$c->id)>{{ $c->codigo }} — {{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-600">Número de referencia *</label>
            <input type="text" name="referencia" required value="{{ old('referencia') }}" placeholder="No. transferencia, voucher banco, etc." class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base">
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-600">Foto de la referencia *</label>
            <input type="file" name="comprobante" required accept="image/jpeg,image/png,image/webp" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-base">
            <p class="mt-1 text-xs text-slate-500">JPG, PNG o WEBP. Máximo 5 MB.</p>
        </div>
        <button class="rounded-lg bg-[#15537c] px-5 py-2.5 text-base font-semibold text-white hover:bg-[#0f3d5c]">Pagar, adjuntar foto y emitir voucher</button>
    </form>
    @endif

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-3">
            <h2 class="text-lg font-semibold text-slate-800">Paquetes de esta factura ({{ $cxp->paquetes->count() }})</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-base">
                <thead class="border-b border-slate-200 bg-slate-50 text-slate-700">
                    <tr>
                        <th class="px-4 py-2 font-semibold">Guía</th>
                        <th class="px-4 py-2 font-semibold">Cliente</th>
                        <th class="px-4 py-2 font-semibold text-right">Peso</th>
                        <th class="px-4 py-2 font-semibold text-right">Monto</th>
                        <th class="px-4 py-2 font-semibold">Al proveedor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cxp->paquetes as $paq)
                        <tr class="border-b border-slate-100">
                            <td class="px-4 py-2 font-mono">{{ $paq->numero_guia }}</td>
                            <td class="px-4 py-2">{{ $paq->cliente->nombre_completo ?? '—' }}</td>
                            <td class="px-4 py-2 text-right">{{ \App\Models\Inventario::formatPeso($paq->peso_lb) }} lb</td>
                            <td class="px-4 py-2 text-right">${{ number_format((float)$paq->monto_calculado, 2) }}</td>
                            <td class="px-4 py-2">
                                @if($paq->proveedor_pagado_at)
                                    <span class="font-semibold text-emerald-700">Cancelado</span>
                                @else
                                    <span class="text-amber-800">Pendiente</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-600">Esta CxP no tiene paquetes de inventario. Si es una factura manual, el pago igual pide referencia y foto.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-3">
            <h2 class="text-lg font-semibold text-slate-800">Pagos</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-base">
                <thead class="border-b border-slate-200 bg-slate-50 text-slate-700">
                    <tr>
                        <th class="px-4 py-2 font-semibold">Fecha</th>
                        <th class="px-4 py-2 font-semibold">Método</th>
                        <th class="px-4 py-2 font-semibold text-right">Monto</th>
                        <th class="px-4 py-2 font-semibold">Voucher</th>
                        <th class="px-4 py-2 font-semibold">Referencia / foto</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cxp->pagos as $pago)
                        <tr class="border-b border-slate-100">
                            <td class="px-4 py-2">{{ $pago->fecha_pago?->format('d/m/Y') }}</td>
                            <td class="px-4 py-2">{{ ucfirst($pago->metodo) }}</td>
                            <td class="px-4 py-2 text-right font-semibold">${{ number_format((float)$pago->monto, 2) }}</td>
                            <td class="px-4 py-2">
                                @if($pago->voucher)
                                    <a href="{{ route('contabilidad.vouchers.pdf', $pago->voucher->id) }}" class="font-semibold text-[#15537c] hover:underline">{{ $pago->voucher->folio }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                <div>{{ $pago->referencia ?: '—' }}</div>
                                @if($pago->tieneComprobante())
                                    <a href="{{ route('contabilidad.cxp.comprobante', $pago->id) }}" target="_blank" class="text-sm font-semibold text-[#15537c] hover:underline">Ver foto</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-600">Aún no hay pagos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
