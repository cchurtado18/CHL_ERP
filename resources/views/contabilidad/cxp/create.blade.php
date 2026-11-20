@extends('layouts.app-new')

@section('title', 'Registrar factura proveedor - Contabilidad')
@section('navbar-title', 'Contabilidad')

@section('content')
<div class="mx-auto w-full max-w-3xl space-y-6">
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-base text-red-800">
            <ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h1 class="text-2xl font-bold text-slate-800">Registrar factura de proveedor</h1>
        <p class="mt-1 text-base text-slate-600">Registra el monto de la factura del proveedor. Luego, en inventario, eliges este número y vas asignando paquetes (pueden llegar en varios días). El sistema compara lo asignado con este monto.</p>
    </div>

    <form method="POST" action="{{ route('contabilidad.cxp.store') }}" class="space-y-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        @csrf
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-600">Proveedor</label>
            <select name="proveedor_id" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-base">
                <option value="">— Seleccione o escriba uno nuevo —</option>
                @foreach($proveedores as $p)
                    <option value="{{ $p->id }}" @selected((string)old('proveedor_id', $proveedorId ?? '') === (string)$p->id)>{{ $p->nombre }}{{ $p->ruc ? ' · '.$p->ruc : '' }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-600">O nuevo proveedor (nombre)</label>
            <input type="text" name="proveedor_nombre" value="{{ old('proveedor_nombre') }}" placeholder="Si no está en la lista" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base">
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-600">Número de factura *</label>
            <input type="text" name="numero_documento" required value="{{ old('numero_documento') }}" placeholder="El que viene en la hoja del proveedor" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base">
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-600">Descripción *</label>
            <input type="text" name="descripcion" required value="{{ old('descripcion') }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base">
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-600">Concepto del costo</label>
            <select name="cuenta_gasto_id" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-base">
                @foreach($cuentasGasto as $c)
                    <option value="{{ $c->id }}" @selected((string)old('cuenta_gasto_id', $cuentaCostoFleteId ?? '') === (string)$c->id)>{{ $c->nombre }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-500">No es la deuda. La deuda es Cuentas por pagar. Aquí se anota en qué se usó (flete, bodega, etc.). Si no estás seguro, déjalo en costo de flete.</p>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Monto *</label>
                <input type="number" step="0.01" min="0.01" name="monto_original" required value="{{ old('monto_original') }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Moneda</label>
                <select name="moneda" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-base">
                    <option value="USD" @selected(old('moneda', 'USD')==='USD')>USD</option>
                    <option value="NIO" @selected(old('moneda')==='NIO')>NIO</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Fecha emisión *</label>
                <input type="date" name="fecha_emision" required value="{{ old('fecha_emision', now()->toDateString()) }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Vencimiento</label>
                <input type="date" name="fecha_vencimiento" value="{{ old('fecha_vencimiento') }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base">
            </div>
        </div>
        <div class="flex flex-wrap gap-3">
            <button class="rounded-lg bg-[#15537c] px-5 py-2.5 text-base font-semibold text-white hover:bg-[#0f3d5c]">Registrar factura</button>
            <a href="{{ route('contabilidad.cxp.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-base font-medium text-slate-700 hover:bg-slate-50">Cancelar</a>
        </div>
    </form>
</div>
@endsection
