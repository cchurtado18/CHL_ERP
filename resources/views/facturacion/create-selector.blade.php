@extends('layouts.app-new')

@section('title', 'Nueva Factura')
@section('navbar-title', 'Nueva Factura')

@section('content')
<div class="mx-auto w-full max-w-4xl space-y-6">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-800">Seleccione el tipo de factura</h1>
        <p class="mt-2 text-slate-600">Elija el flujo que desea crear. En el listado, <span class="font-medium text-[#15537c]">paquetería</span> se distingue con acento azul y <span class="font-medium text-amber-700">encomienda familiar</span> con acento ámbar.</p>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <a href="{{ route('facturacion.create', ['tipo' => 'paqueteria']) }}"
           class="group relative overflow-hidden rounded-xl border border-slate-200 border-l-[5px] border-l-[#15537c] bg-white p-6 shadow-sm transition hover:border-slate-300 hover:shadow-md">
            <div class="mb-3 inline-flex h-11 w-11 items-center justify-center rounded-full bg-[#15537c]/10 text-[#15537c]">
                <i class="fas fa-boxes text-lg"></i>
            </div>
            <h2 class="text-lg font-semibold text-slate-800">Factura de paquetería</h2>
            <p class="mt-2 text-sm text-slate-600">Cliente del catálogo y paquetes de inventario (mismo criterio que en el listado).</p>
            <div class="mt-4 text-sm font-semibold text-[#15537c]">Abrir formulario <i class="fas fa-arrow-right ml-1"></i></div>
        </a>

        <a href="{{ route('facturacion.create', ['tipo' => 'encomienda_familiar']) }}"
           class="group relative overflow-hidden rounded-xl border border-slate-200 border-l-[5px] border-l-amber-500 bg-amber-50/40 p-6 shadow-sm transition hover:border-amber-200 hover:shadow-md">
            <div class="mb-3 inline-flex h-11 w-11 items-center justify-center rounded-full bg-amber-100 text-amber-900">
                <i class="fas fa-people-carry text-lg"></i>
            </div>
            <h2 class="text-lg font-semibold text-slate-800">Factura de encomienda familiar</h2>
            <p class="mt-2 text-sm text-slate-600">Encomienda registrada, remitente/destinatario y PDF propio del flujo familiar.</p>
            <div class="mt-4 text-sm font-semibold text-amber-800">Abrir formulario <i class="fas fa-arrow-right ml-1"></i></div>
        </a>
    </div>

    <div>
        <a href="{{ route('facturacion.index') }}" class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-slate-700 hover:bg-slate-50">
            <i class="fas fa-arrow-left mr-2"></i> Volver a facturacion
        </a>
    </div>
</div>
@endsection
