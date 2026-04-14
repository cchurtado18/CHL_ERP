@extends('layouts.app-new')

@section('title', 'Encomiendas Familiares')
@section('navbar-title', 'Encomiendas Familiares')

@section('content')
@php $esAgente = auth()->check() && (auth()->user()->rol ?? null) === 'agente'; @endphp
<div class="mx-auto w-full max-w-[1400px] space-y-6">
    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><p class="text-sm text-slate-500">Total</p><p class="text-2xl font-bold">{{ $totales['total'] }}</p></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><p class="text-sm text-slate-500">Registradas</p><p class="text-2xl font-bold">{{ $totales['registradas'] }}</p></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><p class="text-sm text-slate-500">Entregadas</p><p class="text-2xl font-bold">{{ $totales['entregadas'] }}</p></div>
        @unless($esAgente)
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><p class="text-sm text-slate-500">Monto total</p><p class="text-2xl font-bold">${{ number_format($totales['monto'], 2) }}</p></div>
        @endunless
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div class="min-w-[240px] flex-1">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Buscar</label>
                <input type="text" name="busqueda" value="{{ $busqueda }}" placeholder="Código, remitente o destinatario..." class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
            </div>
            <div class="w-52">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Estado</label>
                <select name="estado" class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
                    <option value="">Todos</option>
                    @foreach(['registrada','recibida_miami','en_transito','recibida_nicaragua','lista_entrega','entregada','incidencia'] as $estadoOpt)
                        <option value="{{ $estadoOpt }}" {{ $estado === $estadoOpt ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ', $estadoOpt)) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-[#15537c] px-5 py-2.5 text-white">Filtrar</button>
            <a href="{{ route('encomiendas.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5">Limpiar</a>
        </form>
    </div>

    <div class="flex flex-wrap justify-end gap-2">
        <a href="{{ route('remitentes.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 font-semibold text-slate-700">
            <i class="fas fa-user-tag mr-1"></i> Lista de remitentes
        </a>
        <a href="{{ route('destinatarios.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 font-semibold text-slate-700">
            <i class="fas fa-user-check mr-1"></i> Lista de destinatarios
        </a>
        <a href="{{ route('encomiendas.create') }}" class="rounded-xl bg-[#15537c] px-5 py-2.5 font-semibold text-white"><i class="fas fa-plus mr-1"></i> Nueva Encomienda</a>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[#15537c] text-white">
                    <tr>
                        <th class="px-4 py-2">Código</th>
                        <th class="px-4 py-2">Servicio</th>
                        <th class="px-4 py-2">Remitente</th>
                        <th class="px-4 py-2">Destinatario</th>
                        <th class="px-4 py-2">Estado</th>
                        @unless($esAgente)
                            <th class="px-4 py-2">Total</th>
                        @endunless
                        <th class="px-4 py-2 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($encomiendas as $encomienda)
                        <tr class="border-b border-slate-100">
                            <td class="px-4 py-2 font-semibold">{{ $encomienda->codigo }}</td>
                            <td class="px-4 py-2">{{ ($encomienda->tipo_servicio ?? 'maritimo') === 'aereo' ? 'Aéreo' : 'Marítimo' }}</td>
                            <td class="px-4 py-2">{{ $encomienda->remitente->nombre_completo ?? '—' }}</td>
                            <td class="px-4 py-2">{{ $encomienda->destinatario->nombre_completo ?? '—' }}</td>
                            <td class="px-4 py-2">{{ ucwords(str_replace('_',' ', $encomienda->estado_actual)) }}</td>
                            @unless($esAgente)
                                <td class="px-4 py-2">${{ number_format($encomienda->total, 2) }}</td>
                            @endunless
                            <td class="px-4 py-2 text-right">
                                <a href="{{ route('encomiendas.show', $encomienda->id) }}" class="px-2 text-slate-700"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('encomiendas.edit', $encomienda->id) }}" class="px-2 text-slate-700"><i class="fas fa-edit"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $esAgente ? '6' : '7' }}" class="px-4 py-10 text-center text-slate-600">No hay encomiendas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($encomiendas->hasPages())
        <div class="flex justify-center">{{ $encomiendas->links('vendor.pagination.custom') }}</div>
    @endif
</div>
@endsection
