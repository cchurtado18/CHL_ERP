@extends('layouts.app-new')

@section('title', 'Cuentas - Contabilidad - CH LOGISTICS ERP')
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
                <h1 class="text-2xl font-bold text-slate-800">Catálogo de cuentas</h1>
                <p class="mt-1 text-base text-slate-600">Administración del plan contable.</p>
            </div>
            <a href="{{ route('contabilidad.cuentas.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#15537c] px-5 py-2.5 text-base font-semibold text-white shadow-sm hover:bg-[#0f3d5c]"><i class="fas fa-plus"></i> Nueva cuenta</a>
        </div>
        <form class="flex flex-wrap items-end gap-4" method="GET" action="{{ route('contabilidad.cuentas.index') }}">
            <div class="w-full min-w-[200px] sm:w-56">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Tipo</label>
                <select name="tipo" class="w-full appearance-none rounded-lg border border-slate-300 bg-white bg-[length:1.25rem] bg-[right_0.75rem_center] bg-no-repeat px-4 py-2.5 pr-10 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" style="{{ $selSvg }}">
                    <option value="">Todos los tipos</option>
                    @foreach(['activo','pasivo','patrimonio','ingreso','gasto','costo'] as $tipo)
                        <option value="{{ $tipo }}" @selected(request('tipo')===$tipo)>{{ ucfirst($tipo) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-[#15537c] px-5 py-2.5 text-base font-medium text-white hover:bg-[#0f3d5c]"><i class="fas fa-filter"></i> Filtrar</button>
            <a href="{{ route('contabilidad.cuentas.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-5 py-2.5 text-base font-medium text-slate-600 hover:bg-slate-50">Limpiar</a>
        </form>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-4">
        <a href="{{ route('contabilidad.dashboard') }}" class="inline-flex items-center gap-2 text-base font-medium text-[#15537c] hover:underline"><i class="fas fa-arrow-left"></i> Tablero contabilidad</a>
        <p class="text-sm font-medium text-slate-600">Total: <span class="font-bold text-slate-900">{{ $cuentas->total() }}</span> cuentas</p>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] border-collapse text-left text-base text-black">
                <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                    <tr>
                        <th class="px-4 py-2.5 font-semibold">Código</th>
                        <th class="px-4 py-2.5 font-semibold">Nombre</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Tipo</th>
                        <th class="px-4 py-2.5 font-semibold">Subtipo</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Movimientos</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Activa</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($cuentas as $c)
                    <tr class="border-b border-slate-100 {{ $loop->iteration % 2 === 0 ? 'bg-slate-50' : 'bg-white' }} hover:bg-slate-100">
                        <td class="px-4 py-2 font-semibold text-[#15537c]">{{ $c->codigo }}</td>
                        <td class="px-4 py-2 font-medium text-black">{{ $c->nombre }}</td>
                        <td class="px-4 py-2 text-center text-slate-800">{{ ucfirst($c->tipo) }}</td>
                        <td class="px-4 py-2 text-slate-700">{{ $c->subtipo ?: '—' }}</td>
                        <td class="px-4 py-2 text-center text-slate-800">{{ $c->acepta_movimiento ? 'Sí' : 'No' }}</td>
                        <td class="px-4 py-2 text-center text-slate-800">{{ $c->activa ? 'Sí' : 'No' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-base text-slate-600">No hay cuentas.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($cuentas->hasPages())
        <div class="flex justify-center border-t border-slate-100 px-4 py-4">
            {{ $cuentas->links('vendor.pagination.custom') }}
        </div>
        @endif
    </div>
</div>
@endsection
