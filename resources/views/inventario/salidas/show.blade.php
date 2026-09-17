@extends('layouts.app-new')

@section('title', 'Salida #'.$salida->id.' - CH Logistics')
@section('navbar-title', 'Inventario')

@section('content')
<div class="mx-auto w-full max-w-[1200px] space-y-6 pb-10">
    @if(session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-base text-emerald-800" role="alert">
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-slate-500">Salida entre sucursales</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Registro #{{ $salida->id }}</h1>
            <p class="mt-2 text-sm text-slate-600">Creada {{ $salida->created_at?->format('d/m/Y H:i') ?? '—' }}@if($salida->creador) · {{ $salida->creador->nombre ?? $salida->creador->email }}@endif</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('inventario.salidas.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50"><i class="fas fa-list"></i> Historial</a>
            <a href="{{ route('inventario.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50"><i class="fas fa-boxes"></i> Inventario</a>
            <a href="{{ route('inventario.salidas.export', $salida) }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700"><i class="fas fa-file-excel"></i> Excel esta salida</a>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="text-lg font-semibold text-slate-800 border-b border-slate-100 pb-3 mb-4">Descripción y ruta</h2>
        <dl class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Descripción</dt>
                <dd class="mt-1 whitespace-pre-wrap text-slate-900">{{ $salida->descripcion }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sucursal origen</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ $salida->sucursal_origen ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sucursal destino</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ $salida->sucursal_destino ?: '—' }}</dd>
            </div>
        </dl>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-slate-50 px-4 py-3">
            <h2 class="text-lg font-semibold text-slate-800">Paquetes incluidos ({{ $salida->paquetes->count() }})</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] border-collapse text-left text-sm text-black">
                <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                    <tr>
                        <th class="px-3 py-2 font-semibold">Guía</th>
                        <th class="px-3 py-2 font-semibold">Tracking</th>
                        <th class="px-3 py-2 font-semibold">Cliente</th>
                        <th class="px-3 py-2 font-semibold">Servicio</th>
                        <th class="px-3 py-2 font-semibold text-center">Peso</th>
                        <th class="px-3 py-2 font-semibold text-center">Estado</th>
                        <th class="px-3 py-2 font-semibold text-right">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salida->paquetes as $p)
                    <tr class="border-b border-slate-100 {{ $loop->iteration % 2 === 0 ? 'bg-slate-50' : 'bg-white' }}">
                        <td class="px-3 py-2 font-mono font-medium">{{ $p->numero_guia }}</td>
                        <td class="px-3 py-2 font-mono text-xs">{{ $p->tracking_codigo ?: '—' }}</td>
                        <td class="px-3 py-2">{{ $p->cliente?->nombre_completo ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $p->servicio?->tipo_servicio ?? '—' }}</td>
                        <td class="px-3 py-2 text-center">{{ number_format((float) $p->peso_lb, 2) }} lb</td>
                        <td class="px-3 py-2 text-center">{{ $p->estado }}</td>
                        <td class="px-3 py-2 text-right">
                            <a href="{{ route('inventario.show', $p->id) }}" class="text-[#15537c] font-medium hover:underline">Ver paquete</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
