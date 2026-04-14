@extends('layouts.app-new')

@section('title', 'CxC - Contabilidad - CH LOGISTICS ERP')
@section('navbar-title', 'Contabilidad')

@section('content')
@php
    $selSvg = "background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%2364758b%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 9l-7 7-7-7%22/%3E%3C/svg%3E');";
@endphp
<div class="mx-auto w-full max-w-[1400px] space-y-8">
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h1 class="text-2xl font-bold text-slate-800">Cuentas por cobrar</h1>
        <p class="mt-1 text-base text-slate-600">Control de saldos pendientes por factura.</p>
        <form class="mt-5 flex flex-wrap items-end gap-4" method="GET" action="{{ route('contabilidad.cxc.index') }}">
            <div class="min-w-[200px] flex-1">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Cliente</label>
                <input type="text" name="cliente" value="{{ request('cliente') }}" placeholder="Nombre..." class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
            </div>
            <div class="w-full min-w-[200px] sm:w-56">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Estado</label>
                <select name="estado" class="w-full appearance-none rounded-lg border border-slate-300 bg-white bg-[length:1.25rem] bg-[right_0.75rem_center] bg-no-repeat px-4 py-2.5 pr-10 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" style="{{ $selSvg }}">
                    <option value="">Todos los estados</option>
                    @foreach(['al_dia','vencida','en_gestion','castigada','pagada'] as $estado)
                        <option value="{{ $estado }}" @selected(request('estado')===$estado)>{{ ucwords(str_replace('_',' ',$estado)) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-[#15537c] px-5 py-2.5 text-base font-medium text-white hover:bg-[#0f3d5c]"><i class="fas fa-search"></i> Filtrar</button>
            <a href="{{ route('contabilidad.cxc.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-5 py-2.5 text-base font-medium text-slate-600 hover:bg-slate-50">Limpiar</a>
        </form>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-4">
        <a href="{{ route('contabilidad.dashboard') }}" class="inline-flex items-center gap-2 text-base font-medium text-[#15537c] hover:underline"><i class="fas fa-arrow-left"></i> Volver al tablero</a>
        <p class="text-sm font-medium text-slate-600">Total: <span class="font-bold text-slate-900">{{ $items->total() }}</span> registros</p>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1000px] border-collapse text-left text-base text-black">
                <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                    <tr>
                        <th class="px-4 py-2.5 font-semibold">Factura</th>
                        <th class="px-4 py-2.5 font-semibold">Cliente</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Emisión</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Vencimiento</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Monto original</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Cobrado</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Faltante</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Saldo</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Estado</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Mora (días)</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $cxc)
                        @php
                            $montoOriginal = (float) $cxc->monto_original;
                            $faltante = (float) $cxc->saldo_actual;
                            $cobrado = max(0, $montoOriginal - $faltante);
                        @endphp
                        <tr class="border-b border-slate-100 {{ $loop->iteration % 2 === 0 ? 'bg-slate-50' : 'bg-white' }} hover:bg-slate-100">
                            <td class="px-4 py-2 font-semibold whitespace-nowrap text-[#15537c]">#{{ $cxc->factura_id }}</td>
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
                            <td class="px-4 py-2 text-center text-slate-900">{{ $cxc->fecha_emision?->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-center text-slate-900">{{ $cxc->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-4 py-2 text-center font-medium">${{ number_format($montoOriginal, 2) }}</td>
                            <td class="px-4 py-2 text-center font-semibold whitespace-nowrap text-emerald-700">${{ number_format($cobrado, 2) }}</td>
                            <td class="px-4 py-2 text-center font-semibold {{ $faltante > 0 ? 'text-rose-700' : 'text-emerald-700' }}">${{ number_format($faltante, 2) }}</td>
                            <td class="px-4 py-2 text-center font-semibold {{ (float)$cxc->saldo_actual > 0 ? 'text-rose-700' : 'text-emerald-700' }}">${{ number_format((float) $cxc->saldo_actual, 2) }}</td>
                            <td class="px-4 py-2 text-center text-slate-800">{{ ucwords(str_replace('_', ' ', $cxc->estado_cobro)) }}</td>
                            <td class="px-4 py-2 text-center text-slate-900">{{ $cxc->dias_mora }}</td>
                            <td class="px-4 py-2 text-right whitespace-nowrap">
                                <div class="inline-flex flex-col items-end gap-2 sm:flex-row sm:items-center sm:justify-end">
                                    <a href="{{ route('facturacion.show', $cxc->factura_id) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50"><i class="fas fa-file-invoice text-[#15537c]"></i> Ver factura</a>
                                    <a href="{{ route('contabilidad.cxc.show', $cxc->factura_id) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-[#15537c]/30 bg-[#15537c]/5 px-3 py-1.5 text-sm font-semibold text-[#15537c] hover:bg-[#15537c]/10"><i class="fas fa-search-dollar"></i> Detalle CxC</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="px-4 py-12 text-center text-base text-slate-600">No hay cuentas por cobrar registradas.</td></tr>
                    @endforelse
                </tbody>
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
