@extends('layouts.app-new')

@section('title', 'Detalles de la Notificación - CH LOGISTICS ERP')
@section('navbar-title', 'Detalles de la Notificación')

@section('content')
<div class="mx-auto w-full max-w-7xl px-4 sm:px-6 space-y-8">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl px-5 py-4 shadow-sm" style="background: linear-gradient(90deg, #15537c 0%, #2d6a9a 100%); min-height: 90px;">
        <div class="flex items-center gap-3">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-white shadow-sm">
                <i class="fas fa-bell text-[#15537c]" style="font-size: 1.75rem;"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-wide text-white">Detalles de la Notificación</h1>
                <p class="text-sm text-white/80">Vista detallada de la notificación seleccionada</p>
            </div>
        </div>
        <a href="{{ route('notificaciones.index') }}" class="inline-flex items-center gap-2 rounded-lg border-2 border-white/80 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/20">
            <i class="fas fa-arrow-left"></i> Volver a Notificaciones
        </a>
    </div>

    {{-- Card detalle --}}
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-[#15537c]">{{ $notificacion->titulo }}</h2>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-sm text-slate-600">
                        <i class="fas fa-calendar-alt"></i>
                        Enviada el {{ \Carbon\Carbon::parse($notificacion->fecha)->format('d/m/Y H:i') }}
                    </span>
                    <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-sm font-medium {{ $notificacion->leido ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-amber-100 text-amber-800 border-amber-200' }}">
                        <i class="fas {{ $notificacion->leido ? 'fa-check' : 'fa-bell' }}"></i>
                        {{ $notificacion->leido ? 'Leída' : 'No leída' }}
                    </span>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('notificaciones.edit', $notificacion) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-[#15537c]/40 bg-[#15537c]/5 px-3 py-2 text-sm font-semibold text-[#15537c] hover:bg-[#15537c]/10">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <form action="{{ route('notificaciones.destroy', $notificacion) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta notificación?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </form>
            </div>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 mb-6">
            <div>
                <p class="text-sm font-medium text-slate-500"><i class="fas fa-info-circle mr-1.5 text-[#15537c]"></i>Estado</p>
                <p class="mt-0.5">
                    <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-sm font-medium {{ $notificacion->leido ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-amber-100 text-amber-800 border-amber-200' }}">
                        <i class="fas {{ $notificacion->leido ? 'fa-check' : 'fa-exclamation-triangle' }}"></i>
                        {{ $notificacion->leido ? 'Leída' : 'No leída' }}
                    </span>
                </p>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500"><i class="fas fa-calendar mr-1.5 text-[#15537c]"></i>Fecha de creación</p>
                <p class="mt-0.5 text-slate-800">{{ \Carbon\Carbon::parse($notificacion->fecha)->format('d/m/Y H:i:s') }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500"><i class="fas fa-user mr-1.5 text-[#15537c]"></i>Destinatario</p>
                <p class="mt-0.5 font-semibold text-slate-800">{{ $notificacion->usuario->name ?? 'Usuario no encontrado' }}</p>
                <p class="text-sm text-slate-500">{{ $notificacion->usuario->email ?? '' }}</p>
            </div>
        </div>

        <div>
            <p class="text-sm font-medium text-slate-500 mb-1.5"><i class="fas fa-envelope mr-1.5 text-[#15537c]"></i>Mensaje</p>
            <div class="min-h-[120px] rounded-lg border border-slate-200 bg-slate-50 px-4 py-4 text-base leading-relaxed text-slate-800">
                @php
                    $mensajeSeguro = e($notificacion->mensaje);
                    $mensajeConLinks = preg_replace(
                        '/(https?:\/\/[^\s<]+)/i',
                        '<a href="$1" target="_blank" rel="noopener" class="font-medium text-[#15537c] underline decoration-[#15537c]/30 underline-offset-2 hover:decoration-[#15537c]">$1</a>',
                        $mensajeSeguro
                    );
                @endphp
                {!! nl2br($mensajeConLinks) !!}
            </div>
        </div>
    </div>
</div>
@endsection
