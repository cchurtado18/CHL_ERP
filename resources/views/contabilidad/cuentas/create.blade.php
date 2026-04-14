@extends('layouts.app-new')

@section('title', 'Nueva cuenta - Contabilidad - CH LOGISTICS ERP')
@section('navbar-title', 'Contabilidad')

@section('content')
@php
    $selSvg = "background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%2364758b%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 9l-7 7-7-7%22/%3E%3C/svg%3E');";
    $in = 'w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]';
@endphp
<div class="mx-auto w-full max-w-[900px] space-y-8 pb-10">
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-base text-red-800" role="alert">
            <ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h1 class="text-2xl font-bold text-slate-800">Nueva cuenta contable</h1>
        <p class="mt-1 text-base text-slate-600">Alta en el plan de cuentas.</p>
    </div>

    <form method="POST" action="{{ route('contabilidad.cuentas.store') }}" class="space-y-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
        @csrf
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Código</label>
                <input type="text" name="codigo" value="{{ old('codigo') }}" required class="{{ $in }}">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Nombre</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" required class="{{ $in }}">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Tipo</label>
                <select name="tipo" class="w-full appearance-none rounded-lg border border-slate-300 bg-white bg-[length:1.25rem] bg-[right_0.75rem_center] bg-no-repeat px-4 py-2.5 pr-10 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" style="{{ $selSvg }}">
                    @foreach(['activo','pasivo','patrimonio','ingreso','gasto','costo'] as $tipo)
                        <option value="{{ $tipo }}" @selected(old('tipo', 'activo') === $tipo)>{{ ucfirst($tipo) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Subtipo</label>
                <input type="text" name="subtipo" value="{{ old('subtipo') }}" class="{{ $in }}">
            </div>
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Cuenta padre</label>
                <select name="cuenta_padre_id" class="w-full appearance-none rounded-lg border border-slate-300 bg-white bg-[length:1.25rem] bg-[right_0.75rem_center] bg-no-repeat px-4 py-2.5 pr-10 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" style="{{ $selSvg }}">
                    <option value="">Sin padre</option>
                    @foreach($padres as $p)
                        <option value="{{ $p->id }}" @selected(old('cuenta_padre_id') == $p->id)>{{ $p->codigo }} — {{ $p->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2 flex flex-wrap items-center gap-6 pt-2">
                <label class="inline-flex items-center gap-2 text-base text-slate-800"><input type="checkbox" name="acepta_movimiento" value="1" class="h-4 w-4 rounded border-slate-300 text-[#15537c] focus:ring-[#15537c]" checked> Acepta movimiento</label>
                <label class="inline-flex items-center gap-2 text-base text-slate-800"><input type="checkbox" name="activa" value="1" class="h-4 w-4 rounded border-slate-300 text-[#15537c] focus:ring-[#15537c]" checked> Activa</label>
            </div>
        </div>
        <div class="flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-6">
            <a href="{{ route('contabilidad.cuentas.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-5 py-2.5 text-base font-medium text-slate-600 hover:bg-slate-50">Cancelar</a>
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#15537c] px-6 py-2.5 text-base font-semibold text-white shadow-sm hover:bg-[#0f3d5c]"><i class="fas fa-save"></i> Guardar</button>
        </div>
    </form>
</div>
@endsection
