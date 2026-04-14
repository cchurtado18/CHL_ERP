@extends('layouts.app-new')

@section('title', 'Detalle Remitente')
@section('navbar-title', 'Detalle Remitente')

@section('content')
<div class="mx-auto w-full max-w-4xl">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-bold text-slate-800">{{ $remitente->nombre_completo }}</h1>
            <a href="{{ route('remitentes.index') }}" class="rounded-lg border border-slate-300 px-4 py-2">Volver</a>
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div><span class="text-slate-500">Teléfono:</span> {{ $remitente->telefono }}</div>
            <div><span class="text-slate-500">Correo:</span> {{ $remitente->correo ?: '—' }}</div>
            <div><span class="text-slate-500">Ciudad:</span> {{ $remitente->ciudad ?: '—' }}</div>
            <div><span class="text-slate-500">Estado:</span> {{ $remitente->estado ?: '—' }}</div>
            <div class="md:col-span-2"><span class="text-slate-500">Dirección:</span> {{ $remitente->direccion ?: '—' }}</div>
            <div><span class="text-slate-500">Identificación:</span> {{ $remitente->identificacion ?: '—' }}</div>
        </div>
    </div>
</div>
@endsection
