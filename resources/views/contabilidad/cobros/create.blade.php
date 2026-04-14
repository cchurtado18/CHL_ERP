@extends('layouts.app-new')

@section('title', 'Registrar cobro - Contabilidad - CH LOGISTICS ERP')
@section('navbar-title', 'Contabilidad')

@section('content')
@php
    $selSvg = "background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%2364758b%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 9l-7 7-7-7%22/%3E%3C/svg%3E');";
    $in = 'w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]';
    $cxcSaldoMap = $facturas->mapWithKeys(fn ($f) => [$f->id => (float) optional($f->contaCxc)->saldo_actual])->all();
@endphp
<div class="mx-auto w-full max-w-[1000px] space-y-8 pb-10">
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-base text-red-800" role="alert">
            <ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h1 class="text-2xl font-bold text-slate-800">Registrar cobro</h1>
        <p class="mt-1 text-base text-slate-600">Aplicación a factura con saldo pendiente en CxC (puede ser menor al total de la factura si ya hubo cobros parciales).</p>
    </div>

    <form method="POST" action="{{ route('contabilidad.cobros.store') }}" class="space-y-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
        @csrf
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Factura</label>
                <select id="factura_id" name="factura_id" required class="w-full appearance-none rounded-lg border border-slate-300 bg-white bg-[length:1.25rem] bg-[right_0.75rem_center] bg-no-repeat px-4 py-2.5 pr-10 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" style="{{ $selSvg }}">
                    <option value="">Seleccione</option>
                    @foreach($facturas as $f)
                        @php
                            $saldoCxC = (float) optional($f->contaCxc)->saldo_actual;
                        @endphp
                        <option value="{{ $f->id }}" @selected(old('factura_id', $facturaIdPrecarga ?? null) == $f->id)>#{{ $f->id }} — {{ $f->cliente?->nombre_completo ?? $f->encomienda?->remitente?->nombre_completo ?? 'Sin cliente' }} — Total ${{ number_format((float)$f->monto_total,2) }} — Saldo pendiente ${{ number_format($saldoCxC, 2) }}</option>
                    @endforeach
                </select>
                <p id="cxc-hint" class="mt-2 text-sm text-slate-600"></p>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Fecha pago</label>
                <input type="date" name="fecha_pago" value="{{ old('fecha_pago', now()->toDateString()) }}" required class="{{ $in }}">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Monto</label>
                <input id="monto" type="number" step="0.01" min="0.01" name="monto" value="{{ old('monto') }}" required class="{{ $in }}">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Moneda</label>
                <select name="moneda" class="w-full appearance-none rounded-lg border border-slate-300 bg-white bg-[length:1.25rem] bg-[right_0.75rem_center] bg-no-repeat px-4 py-2.5 pr-10 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" style="{{ $selSvg }}"><option>USD</option><option>NIO</option></select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Tasa cambio</label>
                <input type="number" step="0.0001" min="0" name="tasa_cambio" value="{{ old('tasa_cambio') }}" class="{{ $in }}">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Método</label>
                <input type="text" name="metodo" value="{{ old('metodo') }}" required class="{{ $in }}" placeholder="Efectivo, transferencia...">
            </div>
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Cuenta banco / caja</label>
                <select name="cuenta_banco_caja_id" required class="w-full appearance-none rounded-lg border border-slate-300 bg-white bg-[length:1.25rem] bg-[right_0.75rem_center] bg-no-repeat px-4 py-2.5 pr-10 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" style="{{ $selSvg }}">
                    <option value="">Seleccione</option>
                    <optgroup label="Caja">
                        @foreach(($cuentasBancoCaja ?? collect())->where('subtipo', 'caja') as $c)
                            <option value="{{ $c->id }}" @selected(old('cuenta_banco_caja_id') == $c->id)>{{ $c->codigo }} — {{ $c->nombre }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Bancos">
                        @foreach(($cuentasBancoCaja ?? collect())->where('subtipo', 'banco') as $c)
                            <option value="{{ $c->id }}" @selected(old('cuenta_banco_caja_id') == $c->id)>{{ $c->codigo }} — {{ $c->nombre }}</option>
                        @endforeach
                    </optgroup>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Referencia</label>
                <input type="text" name="referencia" value="{{ old('referencia') }}" class="{{ $in }}">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Comisión</label>
                <input type="number" step="0.01" min="0" name="comision" value="{{ old('comision') }}" class="{{ $in }}">
            </div>
        </div>
        <div class="flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-6">
            <a href="{{ route('contabilidad.cobros.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-5 py-2.5 text-base font-medium text-slate-600 hover:bg-slate-50">Cancelar</a>
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#15537c] px-6 py-2.5 text-base font-semibold text-white shadow-sm hover:bg-[#0f3d5c]"><i class="fas fa-save"></i> Guardar cobro</button>
        </div>
    </form>
</div>

<script>
    (function () {
        const saldoPorFactura = @json($cxcSaldoMap);
        const cxcUrlTpl = @json(route('contabilidad.cxc.show', ['facturaId' => '__ID__']));
        const facturaSelect = document.getElementById('factura_id');
        const montoInput = document.getElementById('monto');
        const hint = document.getElementById('cxc-hint');

        function fmtMoney(n) {
            return new Intl.NumberFormat(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
        }

        function sync() {
            const id = facturaSelect?.value;
            const saldo = id ? Number(saldoPorFactura[id] ?? 0) : null;

            if (!id || saldo === null || Number.isNaN(saldo)) {
                if (hint) hint.textContent = '';
                if (montoInput) montoInput.removeAttribute('max');
                return;
            }

            if (hint) {
                const url = cxcUrlTpl.replace('__ID__', String(id));
                hint.innerHTML = 'Saldo pendiente en CxC para esta selección: <span class="font-semibold text-slate-900">$' + fmtMoney(saldo) + '</span> · <a class="font-semibold text-[#15537c] hover:underline" href="' + url + '">Ver detalle CxC</a>';
            }

            if (montoInput) {
                montoInput.setAttribute('max', String(saldo));
                montoInput.setAttribute('title', 'Máximo permitido: $' + fmtMoney(saldo));
            }
        }

        facturaSelect?.addEventListener('change', sync);
        sync();
    })();
</script>
@endsection
