@extends('layouts.app-new')

@section('title', 'Proveedores - Contabilidad')
@section('navbar-title', 'Contabilidad')

@section('content')
<div class="mx-auto w-full max-w-[1400px] space-y-8">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-base text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-base text-red-800">
            <ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Proveedores</h1>
                <p class="mt-1 text-base text-slate-600">Catálogo para cuentas por pagar.</p>
            </div>
            <a href="{{ route('contabilidad.cxp.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-5 py-2.5 text-base font-medium text-slate-700 hover:bg-slate-50"><i class="fas fa-file-invoice"></i> Ir a CxP</a>
        </div>
        <form class="mt-5 flex flex-wrap items-end gap-4" method="GET">
            <div class="min-w-[200px] flex-1">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Buscar</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Nombre, RUC, teléfono..." class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
            </div>
            <button class="inline-flex items-center gap-2 rounded-lg bg-[#15537c] px-5 py-2.5 text-base font-medium text-white hover:bg-[#0f3d5c]"><i class="fas fa-search"></i> Filtrar</button>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-1">
            <h2 class="mb-4 text-lg font-semibold text-slate-800">Nuevo proveedor</h2>
            <form method="POST" action="{{ route('contabilidad.proveedores.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Nombre *</label>
                    <input type="text" name="nombre" required value="{{ old('nombre') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-base">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">RUC / cédula</label>
                    <input type="text" name="ruc" value="{{ old('ruc') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-base">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Teléfono</label>
                    <input type="text" name="telefono" value="{{ old('telefono') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-base">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Correo</label>
                    <input type="email" name="correo" value="{{ old('correo') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-base">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Dirección</label>
                    <input type="text" name="direccion" value="{{ old('direccion') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-base">
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="volver_cxp" value="1" class="rounded border-slate-300"> Luego registrar factura CxP
                </label>
                <button class="w-full rounded-lg bg-[#15537c] px-4 py-2.5 text-base font-semibold text-white hover:bg-[#0f3d5c]">Guardar</button>
            </form>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[560px] text-left text-base">
                    <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                        <tr>
                            <th class="px-4 py-2.5 font-semibold">Proveedor</th>
                            <th class="px-4 py-2.5 font-semibold">Contacto</th>
                            <th class="px-4 py-2.5 font-semibold text-right">Saldo CxP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($proveedores as $p)
                            <tr class="border-b border-slate-100 {{ $loop->even ? 'bg-slate-50' : '' }}">
                                <td class="px-4 py-2">
                                    <div class="font-semibold text-slate-900">{{ $p->nombre }}</div>
                                    <div class="text-sm text-slate-500">{{ $p->ruc ?: '—' }}</div>
                                </td>
                                <td class="px-4 py-2 text-slate-700">{{ $p->telefono ?: $p->correo ?: '—' }}</td>
                                <td class="px-4 py-2 text-right font-bold {{ (float)($p->saldo_pendiente ?? 0) > 0 ? 'text-rose-700' : 'text-slate-700' }}">${{ number_format((float) ($p->saldo_pendiente ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-10 text-center text-slate-600">No hay proveedores.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($proveedores->hasPages())
            <div class="border-t border-slate-100 px-4 py-4">{{ $proveedores->links('vendor.pagination.custom') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
