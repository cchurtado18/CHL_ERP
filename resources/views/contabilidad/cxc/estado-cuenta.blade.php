@extends('layouts.app-new')

@section('title', 'Estado de cuenta — '.$cliente->nombre_completo)
@section('navbar-title', 'Contabilidad')

@section('content')
<div class="mx-auto w-full max-w-[1400px] space-y-8 pb-10 print:max-w-none print:space-y-4">
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-[#15537c] to-[#0f3d5c] p-6 text-white shadow-md sm:p-8 print:rounded-none print:border-0 print:bg-none print:p-0 print:text-slate-900 print:shadow-none">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm font-medium text-white/80 print:text-slate-500">Cuentas por cobrar</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight sm:text-3xl">Estado de cuenta</h1>
                <p class="mt-2 text-lg font-semibold">{{ $cliente->nombre_completo }}</p>
                <p class="mt-1 text-sm text-white/85 print:text-slate-600">
                    @if($cliente->telefono) Tel. {{ $cliente->telefono }} @endif
                    @if($cliente->telefono && $cliente->direccion) · @endif
                    @if($cliente->direccion) {{ $cliente->direccion }} @endif
                </p>
            </div>
            <div class="flex flex-wrap gap-2 print:hidden">
                <a href="{{ route('contabilidad.cxc.estados-cuenta') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-medium text-white backdrop-blur-sm transition hover:bg-white/20"><i class="fas fa-arrow-left"></i> Clientes</a>
                <button type="button" onclick="window.print()" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/30 bg-white px-4 py-2.5 text-sm font-semibold text-[#15537c] transition hover:bg-slate-50"><i class="fas fa-print"></i> Imprimir</button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Facturas pendientes</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $pendientes->count() }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cobrado en esas facturas</p>
            <p class="mt-1 text-2xl font-bold text-emerald-700">${{ number_format($totalCobrado, 2) }}</p>
        </div>
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-rose-700">Total que debe</p>
            <p class="mt-1 text-3xl font-bold text-rose-800">${{ number_format($totalDebe, 2) }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="flex items-center gap-2 text-lg font-semibold text-slate-800"><i class="fas fa-file-invoice-dollar text-[#15537c]"></i> Facturas pendientes</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] border-collapse text-left text-base text-black">
                <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                    <tr>
                        <th class="px-4 py-2.5 font-semibold">Factura</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Emisión</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Vencimiento</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Monto</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Cobrado</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Saldo</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Estado</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Mora</th>
                        <th class="px-4 py-2.5 font-semibold text-right print:hidden">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendientes as $cxc)
                        @php
                            $original = (float) $cxc->monto_original;
                            $saldo = (float) $cxc->saldo_actual;
                            $cobrado = max(0, $original - $saldo);
                        @endphp
                        <tr class="border-b border-slate-100 {{ $loop->iteration % 2 === 0 ? 'bg-slate-50' : 'bg-white' }}">
                            <td class="px-4 py-2 font-semibold whitespace-nowrap text-[#15537c]">Folio {{ $cxc->factura?->etiquetaFolio() ?? $cxc->factura_id }}</td>
                            <td class="px-4 py-2 text-center text-slate-900">{{ $cxc->fecha_emision?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-4 py-2 text-center text-slate-900">{{ $cxc->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-4 py-2 text-right tabular-nums">${{ number_format($original, 2) }}</td>
                            <td class="px-4 py-2 text-right font-semibold tabular-nums text-emerald-700">${{ number_format($cobrado, 2) }}</td>
                            <td class="px-4 py-2 text-right font-bold tabular-nums text-rose-700">${{ number_format($saldo, 2) }}</td>
                            <td class="px-4 py-2 text-center text-slate-800">{{ ucwords(str_replace('_', ' ', $cxc->estado_cobro)) }}</td>
                            <td class="px-4 py-2 text-center text-slate-900">{{ (int) $cxc->dias_mora }}</td>
                            <td class="px-4 py-2 text-right whitespace-nowrap print:hidden">
                                <div class="inline-flex flex-col items-end gap-2 sm:flex-row sm:items-center sm:justify-end">
                                    <a href="{{ route('contabilidad.cxc.show', $cxc->factura_id) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-[#15537c]/30 bg-[#15537c]/5 px-3 py-1.5 text-sm font-semibold text-[#15537c] hover:bg-[#15537c]/10">Detalle CxC</a>
                                    <a href="{{ route('contabilidad.cobros.create', ['factura_id' => $cxc->factura_id]) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-[#15537c] px-3 py-1.5 text-sm font-semibold text-white hover:bg-[#0f3d5c]">Cobrar</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-base text-slate-600">Este cliente no tiene facturas pendientes. Saldo $0.00.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($pendientes->isNotEmpty())
                    <tfoot>
                        <tr class="border-t-2 border-slate-300 bg-slate-50">
                            <td class="px-4 py-3 font-bold text-slate-900" colspan="3">Total</td>
                            <td class="px-4 py-3 text-right font-bold tabular-nums text-slate-900">${{ number_format($totalOriginal, 2) }}</td>
                            <td class="px-4 py-3 text-right font-bold tabular-nums text-emerald-800">${{ number_format($totalCobrado, 2) }}</td>
                            <td class="px-4 py-3 text-right text-lg font-bold tabular-nums text-rose-800">${{ number_format($totalDebe, 2) }}</td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
