@extends('layouts.app-new')

@section('title', 'Períodos - Contabilidad - CH LOGISTICS ERP')
@section('navbar-title', 'Contabilidad')

@section('content')
<div class="mx-auto w-full max-w-[1400px] space-y-8">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-base text-emerald-800" role="alert">
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h1 class="text-2xl font-bold text-slate-800">Períodos contables</h1>
        <p class="mt-1 text-base text-slate-600">Cierre y reapertura controlada de períodos mensuales.</p>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-4">
        <a href="{{ route('contabilidad.dashboard') }}" class="inline-flex items-center gap-2 text-base font-medium text-[#15537c] hover:underline"><i class="fas fa-arrow-left"></i> Tablero contabilidad</a>
        <p class="text-sm font-medium text-slate-600">Total: <span class="font-bold text-slate-900">{{ $periodos->total() }}</span> períodos</p>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-base text-black">
                <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                    <tr>
                        <th class="px-4 py-2.5 font-semibold text-center">Año</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Mes</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Estado</th>
                        <th class="px-4 py-2.5 font-semibold">Fecha cierre</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periodos as $p)
                        <tr class="border-b border-slate-100 {{ $loop->iteration % 2 === 0 ? 'bg-slate-50' : 'bg-white' }} hover:bg-slate-100">
                            <td class="px-4 py-2 text-center font-medium text-slate-900">{{ $p->anio }}</td>
                            <td class="px-4 py-2 text-center font-medium text-slate-900">{{ str_pad((string)$p->mes, 2, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-4 py-2 text-center">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-sm font-semibold {{ $p->estado === 'abierto' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-800' }}">{{ ucfirst($p->estado) }}</span>
                            </td>
                            <td class="px-4 py-2 text-slate-800">{{ $p->fecha_cierre?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-2 text-right">
                                <form action="{{ route('contabilidad.periodos.toggle', $p->id) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ $p->estado === 'abierto' ? 'Cerrar' : 'Reabrir' }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-12 text-center text-base text-slate-600">No hay períodos generados aún.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($periodos->hasPages())
        <div class="flex justify-center border-t border-slate-100 px-4 py-4">
            {{ $periodos->links('vendor.pagination.custom') }}
        </div>
        @endif
    </div>
</div>
@endsection
