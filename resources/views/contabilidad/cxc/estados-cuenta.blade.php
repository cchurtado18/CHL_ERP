@extends('layouts.app-new')

@section('title', 'Estado de cuenta por cliente - CxC')
@section('navbar-title', 'Contabilidad')

@section('content')
<div class="mx-auto w-full max-w-[1400px] space-y-8">
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h1 class="text-2xl font-bold text-slate-800">Estado de cuenta por cliente</h1>
        <p class="mt-1 text-base text-slate-600">Lo que debe cada cliente, sus facturas pendientes y el total de cartera.</p>
        <form class="mt-5 flex flex-wrap items-end gap-4" method="GET" action="{{ route('contabilidad.cxc.estados-cuenta') }}">
            <div class="min-w-[200px] flex-1">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Cliente</label>
                <input type="text" name="cliente" value="{{ request('cliente') }}" placeholder="Nombre..." class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
            </div>
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-[#15537c] px-5 py-2.5 text-base font-medium text-white hover:bg-[#0f3d5c]"><i class="fas fa-search"></i> Buscar</button>
            <a href="{{ route('contabilidad.cxc.estados-cuenta') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-5 py-2.5 text-base font-medium text-slate-600 hover:bg-slate-50">Limpiar</a>
        </form>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-4">
        <a href="{{ route('contabilidad.cxc.index') }}" class="inline-flex items-center gap-2 text-base font-medium text-[#15537c] hover:underline"><i class="fas fa-arrow-left"></i> Volver a CxC</a>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium uppercase tracking-wide text-slate-500">Clientes con saldo</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($clientesConSaldo) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium uppercase tracking-wide text-slate-500">Facturas pendientes</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($facturasPendientes) }}</p>
        </div>
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
            <p class="text-sm font-medium uppercase tracking-wide text-rose-700">Total por cobrar</p>
            <p class="mt-1 text-2xl font-bold text-rose-800">${{ number_format($totalCartera, 2) }}</p>
        </div>
    </div>

    @if($sinClienteCount > 0)
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-base text-amber-950">
            Hay <strong>{{ $sinClienteCount }}</strong> factura(s) de encomienda sin cliente de catálogo, por
            <strong>${{ number_format($sinClienteTotal, 2) }}</strong>.
            <a href="{{ route('contabilidad.cxc.index') }}" class="font-semibold text-[#15537c] hover:underline">Verlas en la lista CxC</a>.
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] border-collapse text-left text-base text-black">
                <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                    <tr>
                        <th class="px-4 py-2.5 font-semibold">Cliente</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Facturas pendientes</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Facturado</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Saldo que debe</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $row)
                        @php
                            $nombre = $row->cliente?->nombre_completo ?? 'Cliente #'.$row->cliente_id;
                            $original = (float) $row->total_original;
                            $debe = (float) $row->total_debe;
                        @endphp
                        <tr class="border-b border-slate-100 {{ $loop->iteration % 2 === 0 ? 'bg-slate-50' : 'bg-white' }} hover:bg-slate-100">
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $nombre }}</td>
                            <td class="px-4 py-3 text-center text-slate-800">{{ (int) $row->facturas_pendientes }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-slate-800">${{ number_format($original, 2) }}</td>
                            <td class="px-4 py-3 text-right text-lg font-bold tabular-nums text-rose-700">${{ number_format($debe, 2) }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('contabilidad.cxc.estado-cuenta', $row->cliente_id) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-[#15537c]/30 bg-[#15537c]/5 px-3 py-1.5 text-sm font-semibold text-[#15537c] hover:bg-[#15537c]/10">
                                    <i class="fas fa-file-invoice"></i> Ver estado de cuenta
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-base text-slate-600">Ningún cliente tiene facturas pendientes en CxC.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($items->isNotEmpty())
                    <tfoot>
                        <tr class="border-t-2 border-slate-300 bg-slate-50">
                            <td class="px-4 py-3 font-bold text-slate-900">Total</td>
                            <td class="px-4 py-3 text-center font-bold text-slate-900">{{ number_format($facturasPendientes) }}</td>
                            <td class="px-4 py-3 text-right font-bold tabular-nums text-slate-900">—</td>
                            <td class="px-4 py-3 text-right text-lg font-bold tabular-nums text-rose-800">${{ number_format($totalCartera, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
        @if($items->hasPages())
        <div class="flex justify-center border-t border-slate-100 px-4 py-4">
            {{ $items->links('vendor.pagination.custom') }}
        </div>
        @endif
    </div>
</div>
@endsection
