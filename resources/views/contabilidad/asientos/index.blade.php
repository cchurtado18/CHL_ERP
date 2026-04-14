@extends('layouts.app-new')

@section('title', 'Asientos - Contabilidad - CH LOGISTICS ERP')
@section('navbar-title', 'Contabilidad')

@section('content')
@php
    $selSvg = "background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%2364758b%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 9l-7 7-7-7%22/%3E%3C/svg%3E');";
@endphp
<div class="mx-auto w-full max-w-[1400px] space-y-8">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-base text-emerald-800" role="alert">
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Libro diario (asientos)</h1>
                <p class="mt-1 text-base text-slate-600">Asientos manuales y automáticos contabilizados.</p>
            </div>
            <a href="{{ route('contabilidad.asientos.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#15537c] px-5 py-2.5 text-base font-semibold text-white shadow-sm hover:bg-[#0f3d5c]"><i class="fas fa-plus"></i> Nuevo asiento</a>
        </div>
        <form method="GET" action="{{ route('contabilidad.asientos.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="w-40">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Desde</label>
                <input type="date" name="desde" value="{{ request('desde') }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
            </div>
            <div class="w-40">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Hasta</label>
                <input type="date" name="hasta" value="{{ request('hasta') }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
            </div>
            <div class="w-48">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Estado</label>
                <select name="estado" class="w-full appearance-none rounded-lg border border-slate-300 bg-white bg-[length:1.25rem] bg-[right_0.75rem_center] bg-no-repeat px-4 py-2.5 pr-10 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" style="{{ $selSvg }}">
                    <option value="">Todos</option>
                    @foreach(['borrador','contabilizado','anulado'] as $estado)
                        <option value="{{ $estado }}" @selected(request('estado')===$estado)>{{ ucfirst($estado) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-[#15537c] px-5 py-2.5 text-base font-medium text-white hover:bg-[#0f3d5c]"><i class="fas fa-search"></i> Filtrar</button>
            <a href="{{ route('contabilidad.asientos.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-5 py-2.5 text-base font-medium text-slate-600 hover:bg-slate-50">Limpiar</a>
        </form>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-4">
        <a href="{{ route('contabilidad.dashboard') }}" class="inline-flex items-center gap-2 text-base font-medium text-[#15537c] hover:underline"><i class="fas fa-arrow-left"></i> Tablero contabilidad</a>
        <p class="text-sm font-medium text-slate-600">Total: <span class="font-bold text-slate-900">{{ $asientos->total() }}</span> asientos</p>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1000px] border-collapse text-left text-base text-black">
                <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                    <tr>
                        <th class="px-4 py-2.5 font-semibold">Número</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Fecha</th>
                        <th class="px-4 py-2.5 font-semibold">Descripción</th>
                        <th class="px-4 py-2.5 font-semibold">Ref</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Débito</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Crédito</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Estado</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Acción</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($asientos as $a)
                    <tr class="border-b border-slate-100 align-top {{ $loop->iteration % 2 === 0 ? 'bg-slate-50' : 'bg-white' }} hover:bg-slate-100">
                        <td class="px-4 py-2 font-semibold text-[#15537c]">{{ $a->numero }}</td>
                        <td class="px-4 py-2 text-center text-slate-900">{{ $a->fecha?->format('d/m/Y') }}</td>
                        <td class="px-4 py-2">
                            <div class="font-medium text-black">{{ $a->descripcion ?: '—' }}</div>
                            <div class="mt-1 text-sm text-slate-600">
                                @foreach($a->detalles->take(2) as $d)
                                    <div>{{ $d->cuenta->codigo ?? '' }} · {{ $d->cuenta->nombre ?? '' }} (D:{{ number_format((float)$d->debito,2) }} C:{{ number_format((float)$d->credito,2) }})</div>
                                @endforeach
                                @if($a->detalles->count() > 2)<div class="text-slate-500">+{{ $a->detalles->count()-2 }} líneas más</div>@endif
                            </div>
                        </td>
                        <td class="px-4 py-2 text-slate-800">{{ $a->referencia_tipo }} {{ $a->referencia_id }}</td>
                        <td class="px-4 py-2 text-right font-medium">${{ number_format((float)$a->total_debito,2) }}</td>
                        <td class="px-4 py-2 text-right font-medium">${{ number_format((float)$a->total_credito,2) }}</td>
                        <td class="px-4 py-2 text-center text-slate-800">{{ ucfirst($a->estado) }}</td>
                        <td class="px-4 py-2 text-right">
                            <a href="{{ route('contabilidad.asientos.show', $a->id) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50"><i class="fas fa-eye text-[#15537c]"></i> Ver</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-12 text-center text-base text-slate-600">No hay asientos.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($asientos->hasPages())
        <div class="flex justify-center border-t border-slate-100 px-4 py-4">
            {{ $asientos->links('vendor.pagination.custom') }}
        </div>
        @endif
    </div>
</div>
@endsection
