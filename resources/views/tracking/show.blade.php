@extends('layouts.app-new')

@section('title', 'Detalle del Tracking - CH LOGISTICS ERP')
@section('navbar-title', 'Detalle del Tracking')

@section('content')
<div class="mx-auto w-full max-w-7xl px-4 sm:px-6 space-y-8">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl px-5 py-4 shadow-sm" style="background: linear-gradient(90deg, #15537c 0%, #2d6a9a 100%); min-height: 90px;">
        <div class="flex items-center gap-3">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-white shadow-sm">
                <i class="fas fa-paper-plane text-[#15537c]" style="font-size: 1.75rem;"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-wide text-white">Detalle del Tracking</h1>
                <p class="text-sm text-white/80">Información completa del tracking seleccionado</p>
            </div>
        </div>
        <a href="{{ route('tracking.index') }}" class="inline-flex items-center gap-2 rounded-lg border-2 border-white/80 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/20">
            <i class="fas fa-arrow-left"></i> Volver a Trackings
        </a>
    </div>

    {{-- Card detalle --}}
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
        <h2 class="text-lg font-semibold text-slate-800 border-b border-slate-100 pb-3 mb-6">
            <i class="fas fa-info-circle text-[#15537c] mr-2"></i>Información del Tracking
        </h2>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <p class="text-sm font-medium text-slate-500"><i class="fas fa-barcode mr-1.5 text-[#15537c]"></i>Código de tracking</p>
                <p class="mt-0.5 text-slate-800 font-mono text-sm">{{ $tracking->tracking_codigo }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500"><i class="fas fa-user mr-1.5 text-[#15537c]"></i>Cliente</p>
                <p class="mt-0.5 text-slate-800">{{ $tracking->cliente->nombre_completo ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500"><i class="fas fa-info-circle mr-1.5 text-[#15537c]"></i>Estado</p>
                <p class="mt-0.5">
                    @php
                        $statusStyles = [
                            'pendiente' => 'bg-amber-100 text-amber-800 border-amber-200',
                            'en_proceso' => 'bg-sky-100 text-sky-800 border-sky-200',
                            'completado' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'vencido' => 'bg-red-100 text-red-800 border-red-200',
                        ];
                        $statusIcons = [
                            'pendiente' => 'clock',
                            'en_proceso' => 'spinner',
                            'completado' => 'check-circle',
                            'vencido' => 'exclamation-triangle',
                        ];
                        $style = $statusStyles[$tracking->estado] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                        $icon = $statusIcons[$tracking->estado] ?? 'question-circle';
                    @endphp
                    <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-sm font-medium {{ $style }}">
                        @if($icon === 'spinner')
                            <i class="fas fa-{{ $icon }} fa-spin"></i>
                        @else
                            <i class="fas fa-{{ $icon }}"></i>
                        @endif
                        {{ ucfirst(str_replace('_', ' ', $tracking->estado)) }}
                    </span>
                </p>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500"><i class="fas fa-calendar-check mr-1.5 text-[#15537c]"></i>Fecha de estado</p>
                <p class="mt-0.5 text-slate-800">{{ $tracking->fecha_estado }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500"><i class="fas fa-bell mr-1.5 text-[#15537c]"></i>Recordatorio</p>
                <p class="mt-0.5 text-slate-800">{{ $tracking->recordatorio_fecha ?? '-' }}</p>
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
                <p class="text-sm font-medium text-slate-500"><i class="fas fa-sticky-note mr-1.5 text-[#15537c]"></i>Nota</p>
                <p class="mt-0.5 text-slate-800">{{ $tracking->nota ?? 'Sin observaciones' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
