@extends('layouts.app-new')

@section('title', 'Cobros - Contabilidad - CH LOGISTICS ERP')
@section('navbar-title', 'Contabilidad')

@section('content')
<div class="mx-auto w-full max-w-[1400px] space-y-8">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-base text-emerald-800" role="alert">
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Cobros</h1>
                <p class="mt-1 text-base text-slate-600">Pagos recibidos con impacto automático en CxC y contabilidad.</p>
            </div>
            <a href="{{ route('contabilidad.cobros.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#15537c] px-5 py-2.5 text-base font-semibold text-white shadow-sm hover:bg-[#0f3d5c]"><i class="fas fa-hand-holding-usd"></i> Registrar cobro</a>
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-4">
        <a href="{{ route('contabilidad.dashboard') }}" class="inline-flex items-center gap-2 text-base font-medium text-[#15537c] hover:underline"><i class="fas fa-arrow-left"></i> Tablero contabilidad</a>
        <p class="text-sm font-medium text-slate-600">Total: <span class="font-bold text-slate-900">{{ $cobros->total() }}</span> cobros</p>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] border-collapse text-left text-base text-black">
                <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                    <tr>
                        <th class="px-4 py-2.5 font-semibold text-center">Fecha</th>
                        <th class="px-4 py-2.5 font-semibold">Factura</th>
                        <th class="px-4 py-2.5 font-semibold">Cliente</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Monto</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Moneda</th>
                        <th class="px-4 py-2.5 font-semibold">Método</th>
                        <th class="px-4 py-2.5 font-semibold">Cuenta</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cobros as $c)
                        <tr class="border-b border-slate-100 {{ $loop->iteration % 2 === 0 ? 'bg-slate-50' : 'bg-white' }} hover:bg-slate-100">
                            <td class="px-4 py-2 text-center text-slate-900">{{ $c->fecha_pago?->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 font-semibold text-[#15537c]">#{{ $c->factura_id }}</td>
                            <td class="px-4 py-2 font-medium text-black">{{ $c->factura->cliente?->nombre_completo ?? $c->factura->encomienda?->remitente?->nombre_completo ?? '—' }}</td>
                            <td class="px-4 py-2 text-right font-semibold text-emerald-700">${{ number_format((float)$c->monto,2) }}</td>
                            <td class="px-4 py-2 text-center text-slate-800">{{ $c->moneda }}</td>
                            <td class="px-4 py-2 text-slate-800">{{ $c->metodo }}</td>
                            <td class="px-4 py-2 text-sm text-slate-700">{{ $c->cuentaBancoCaja->codigo ?? '' }} {{ $c->cuentaBancoCaja->nombre ?? '' }}</td>
                            <td class="px-4 py-2 text-right">
                                <a href="{{ route('contabilidad.cobros.show', $c->id) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50"><i class="fas fa-eye text-[#15537c]"></i> Ver</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-12 text-center text-base text-slate-600">No hay cobros registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($cobros->hasPages())
        <div class="flex justify-center border-t border-slate-100 px-4 py-4">
            {{ $cobros->links('vendor.pagination.custom') }}
        </div>
        @endif
    </div>
</div>
@endsection
