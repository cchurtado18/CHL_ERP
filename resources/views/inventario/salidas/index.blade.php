@extends('layouts.app-new')

@section('title', 'Salidas entre sucursales - CH Logistics')
@section('navbar-title', 'Inventario')

@section('content')
<div class="mx-auto w-full max-w-[1100px] space-y-6 pb-10">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Salidas entre sucursales</h1>
            <p class="mt-1 text-base text-slate-600">Registro histórico de paquetes enviados de una sucursal a otra.</p>
        </div>
        <a href="{{ route('inventario.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2.5 text-base font-medium text-slate-700 hover:bg-slate-50"><i class="fas fa-arrow-left"></i> Volver al inventario</a>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] border-collapse text-left text-base text-black">
                <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                    <tr>
                        <th class="px-4 py-2.5 font-semibold">#</th>
                        <th class="px-4 py-2.5 font-semibold">Fecha</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Paquetes</th>
                        <th class="px-4 py-2.5 font-semibold">Origen → destino</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salidas as $s)
                    <tr class="border-b border-slate-100 {{ $loop->iteration % 2 === 0 ? 'bg-slate-50' : 'bg-white' }} hover:bg-slate-100">
                        <td class="px-4 py-2 font-semibold text-[#15537c]">{{ $s->id }}</td>
                        <td class="px-4 py-2 text-slate-800">{{ $s->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="px-4 py-2 text-center font-medium text-slate-800">{{ $s->paquetes_count }}</td>
                        <td class="px-4 py-2 text-slate-800">
                            <span class="font-medium">{{ $s->sucursal_origen ?: '—' }}</span>
                            <span class="mx-1 text-slate-400">→</span>
                            <span class="font-medium">{{ $s->sucursal_destino ?: '—' }}</span>
                        </td>
                        <td class="px-4 py-2 text-right whitespace-nowrap">
                            <a href="{{ route('inventario.salidas.show', $s) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-[#15537c]/30 bg-[#15537c]/5 px-3 py-1.5 text-sm font-semibold text-[#15537c] hover:bg-[#15537c]/10">Ver</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-slate-600">No hay salidas registradas. Desde <a href="{{ route('inventario.index') }}" class="font-semibold text-[#15537c] hover:underline">Inventario</a> active el modo «Registrar salida entre sucursales».</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($salidas->hasPages())
        <div class="flex justify-center border-t border-slate-100 px-4 py-4">
            {{ $salidas->links('vendor.pagination.custom') }}
        </div>
        @endif
    </div>
</div>
@endsection
