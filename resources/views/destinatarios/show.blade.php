@extends('layouts.app-new')

@section('title', 'Detalle Destinatario')
@section('navbar-title', 'Detalle Destinatario')

@section('content')
<div class="mx-auto w-full max-w-4xl">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-bold text-slate-800">{{ $destinatario->nombre_completo }}</h1>
            <a href="{{ route('destinatarios.index') }}" class="rounded-lg border border-slate-300 px-4 py-2">Volver</a>
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div><span class="text-slate-500">Teléfono 1:</span> {{ $destinatario->telefono_1 }}</div>
            <div><span class="text-slate-500">Teléfono 2:</span> {{ $destinatario->telefono_2 ?: '—' }}</div>
            <div class="md:col-span-2"><span class="text-slate-500">Dirección:</span> {{ $destinatario->direccion }}</div>
            <div class="md:col-span-2"><span class="text-slate-500">Referencias:</span> {{ $destinatario->referencias ?: '—' }}</div>
            <div><span class="text-slate-500">Ciudad:</span> {{ $destinatario->ciudad ?: '—' }}</div>
            <div><span class="text-slate-500">Departamento:</span> {{ $destinatario->departamento ?: '—' }}</div>
            <div><span class="text-slate-500">Cédula:</span> {{ $destinatario->cedula ?: '—' }}</div>
            <div><span class="text-slate-500">Autorizado:</span> {{ $destinatario->autorizado_para_recibir ? 'Sí' : 'No' }}</div>
        </div>
    </div>
</div>
@endsection
