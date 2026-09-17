@extends('layouts.app-new')

@section('title', 'Registrar gasto - CH Logistics')
@section('navbar-title', 'Registrar gasto')

@section('content')
<div class="mx-auto w-full max-w-[800px] space-y-6">
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <nav class="mb-2 flex items-center gap-2 text-sm text-slate-500">
                    <a href="{{ route('contabilidad.dashboard') }}" class="hover:text-[#15537c]">Contabilidad</a>
                    <i class="fas fa-chevron-right text-xs"></i>
                    <a href="{{ route('contabilidad.gastos.index') }}" class="hover:text-[#15537c]">Gastos</a>
                    <i class="fas fa-chevron-right text-xs"></i>
                    <span class="font-semibold text-slate-700">Nuevo</span>
                </nav>
                <h1 class="flex items-center gap-3 text-2xl font-bold text-slate-800">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-rose-500 to-pink-600 text-white shadow"><i class="fas fa-plus-circle text-xl"></i></span>
                    Registrar gasto
                </h1>
            </div>
            <a href="{{ route('contabilidad.gastos.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 sm:self-start">
                <i class="fas fa-arrow-left text-[#15537c]"></i> Volver a gastos
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
            <p class="mb-1 font-semibold"><i class="fas fa-exclamation-triangle"></i> Revisá los siguientes errores:</p>
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('contabilidad.gastos.store') }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-semibold text-slate-700">Fecha <span class="text-rose-500">*</span></label>
                <input type="date" name="fecha" required value="{{ old('fecha', now()->toDateString()) }}" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">Categoría <span class="text-rose-500">*</span></label>
                <select name="categoria_id" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
                    <option value="">Elegí una...</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" {{ old('categoria_id') == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-4">
            <label class="block text-sm font-semibold text-slate-700">Descripción <span class="text-rose-500">*</span></label>
            <input type="text" name="descripcion" required maxlength="500" value="{{ old('descripcion') }}" placeholder="Ej. Pago de combustible camión #2" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-slate-700">Monto <span class="text-rose-500">*</span></label>
                <div class="mt-1 flex">
                    <span class="inline-flex items-center rounded-l-lg border border-r-0 border-slate-300 bg-slate-100 px-3 text-slate-600">$</span>
                    <input type="number" step="0.01" min="0.01" name="monto" required value="{{ old('monto') }}" class="block w-full rounded-r-lg border border-slate-300 px-3 py-2 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">Moneda</label>
                <select name="moneda" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
                    <option value="USD" {{ old('moneda', 'USD') === 'USD' ? 'selected' : '' }}>USD</option>
                    <option value="NIO" {{ old('moneda') === 'NIO' ? 'selected' : '' }}>NIO</option>
                </select>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-semibold text-slate-700">Pagado desde <span class="text-rose-500">*</span></label>
                <select name="cuenta_pago_id" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
                    <option value="">Elegí caja o banco...</option>
                    @foreach($cuentasPago as $c)
                        <option value="{{ $c->id }}" {{ old('cuenta_pago_id') == $c->id ? 'selected' : '' }}>{{ $c->nombre }} ({{ $c->codigo }})</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">El sistema generará el asiento contable automáticamente.</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">Referencia (opcional)</label>
                <input type="text" name="referencia" maxlength="120" value="{{ old('referencia') }}" placeholder="# factura, # comprobante" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-2 border-t border-slate-100 pt-4">
            <a href="{{ route('contabilidad.gastos.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancelar</a>
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-[#15537c] px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#0f3d5c]"><i class="fas fa-save"></i> Registrar gasto</button>
        </div>
    </form>
</div>
@endsection
