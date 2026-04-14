@extends('layouts.app-new')

@section('title', 'Nuevo asiento - Contabilidad - CH LOGISTICS ERP')
@section('navbar-title', 'Contabilidad')

@section('content')
@php
    $selSvg = "background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%2364758b%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 9l-7 7-7-7%22/%3E%3C/svg%3E');";
    $in = 'w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]';
@endphp
<div class="mx-auto w-full max-w-[1100px] space-y-8 pb-10">
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-base text-red-800" role="alert">
            <ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h1 class="text-2xl font-bold text-slate-800">Nuevo asiento manual</h1>
        <p class="mt-1 text-base text-slate-600">Registre líneas de débito y crédito balanceadas.</p>
    </div>

    <form method="POST" action="{{ route('contabilidad.asientos.store') }}" class="space-y-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
        @csrf
        <h2 class="flex items-center gap-2 border-b border-slate-100 pb-4 text-lg font-semibold text-slate-800"><i class="fas fa-edit text-[#15537c]"></i> Encabezado</h2>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Fecha</label>
                <input type="date" name="fecha" value="{{ old('fecha', now()->toDateString()) }}" required class="{{ $in }}">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Moneda</label>
                <select name="moneda" class="w-full appearance-none rounded-lg border border-slate-300 bg-white bg-[length:1.25rem] bg-[right_0.75rem_center] bg-no-repeat px-4 py-2.5 pr-10 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" style="{{ $selSvg }}"><option>USD</option><option>NIO</option></select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Tasa cambio</label>
                <input type="number" step="0.0001" min="0" name="tasa_cambio" class="{{ $in }}">
            </div>
            <div class="md:col-span-4">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Descripción</label>
                <input type="text" name="descripcion" value="{{ old('descripcion') }}" class="{{ $in }}">
            </div>
        </div>

        <h2 class="flex items-center gap-2 border-b border-slate-100 pb-4 text-lg font-semibold text-slate-800"><i class="fas fa-list text-[#15537c]"></i> Líneas contables</h2>
        <div class="space-y-4">
            @for($i=0; $i<3; $i++)
                <div class="grid grid-cols-1 gap-3 rounded-xl border border-slate-100 bg-slate-50/80 p-4 md:grid-cols-5">
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Cuenta</label>
                        <select name="lineas[{{ $i }}][cuenta_id]" class="w-full appearance-none rounded-lg border border-slate-300 bg-white bg-[length:1.25rem] bg-[right_0.75rem_center] bg-no-repeat px-4 py-2.5 pr-10 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" style="{{ $selSvg }}">
                            <option value="">Seleccione</option>
                            @foreach($cuentas as $c)
                                <option value="{{ $c->id }}">{{ $c->codigo }} — {{ $c->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Débito</label>
                        <input type="number" step="0.01" min="0" name="lineas[{{ $i }}][debito]" class="{{ $in }}">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Crédito</label>
                        <input type="number" step="0.01" min="0" name="lineas[{{ $i }}][credito]" class="{{ $in }}">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Glosa</label>
                        <input type="text" name="lineas[{{ $i }}][glosa]" class="{{ $in }}">
                    </div>
                </div>
            @endfor
        </div>

        <div class="flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-6">
            <a href="{{ route('contabilidad.asientos.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-5 py-2.5 text-base font-medium text-slate-600 hover:bg-slate-50">Cancelar</a>
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#15537c] px-6 py-2.5 text-base font-semibold text-white shadow-sm hover:bg-[#0f3d5c]"><i class="fas fa-save"></i> Guardar asiento</button>
        </div>
    </form>
</div>
@endsection
