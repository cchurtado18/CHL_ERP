@extends('layouts.portal')

@section('title', 'Detalle de paquete')
@section('navbar-title', 'Detalle de paquete')

@section('content')
@php
    $estadoBadge = match($paquete->estado) {
        'recibido' => 'bg-amber-200 text-amber-900',
        'entregado' => 'bg-emerald-200 text-emerald-900',
        'en_transito', 'en_camino' => 'bg-sky-200 text-sky-900',
        default => 'bg-slate-200 text-slate-900',
    };
@endphp
<div class="mx-auto w-full max-w-[1400px] space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('portal.paquetes.index') }}" class="text-sm font-semibold text-[#15537c] hover:underline"><i class="fas fa-arrow-left mr-1"></i> Volver</a>
            <h1 class="mt-2 text-xl font-bold text-slate-800 sm:text-2xl">{{ $paquete->tracking_codigo ?: $paquete->numero_guia ?: ('Paquete #'.$paquete->id) }}</h1>
        </div>
        <span class="rounded-full px-3 py-1 text-sm font-semibold {{ $estadoBadge }}">{{ ucfirst(str_replace('_',' ', $paquete->estado)) }}</span>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="mb-4 font-bold text-slate-800">Información</h2>
                <dl class="grid gap-4 sm:grid-cols-2 text-sm">
                    <div>
                        <dt class="text-slate-500">Servicio</dt>
                        <dd class="mt-1 font-semibold capitalize">{{ $paquete->servicio->tipo_servicio ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Peso</dt>
                        <dd class="mt-1 font-semibold">{{ number_format($paquete->peso_lb ?? 0, 2) }} lb</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Tracking</dt>
                        <dd class="mt-1 font-mono font-semibold text-[#15537c]">{{ $paquete->tracking_codigo ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Nº guía</dt>
                        <dd class="mt-1"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-semibold">{{ $paquete->numero_guia ?: '—' }}</code></dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Fecha ingreso</dt>
                        <dd class="mt-1 font-semibold">{{ \Carbon\Carbon::parse($paquete->fecha_ingreso)->format('d/m/Y') }}</dd>
                    </div>
                    @if($paquete->factura_id)
                    <div>
                        <dt class="text-slate-500">Factura</dt>
                        <dd class="mt-1">
                            <a href="{{ route('portal.facturas.show', $paquete->factura_id) }}" class="font-semibold text-[#15537c] hover:underline">Ver factura</a>
                        </dd>
                    </div>
                    @endif
                </dl>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="mb-4 font-bold text-slate-800">Historial de estados</h2>
                @if($paquete->estadoEventos->isEmpty())
                    <p class="text-sm text-slate-500">Aún no hay eventos sincronizados. Estado actual: <strong class="capitalize">{{ str_replace('_',' ', $paquete->estado) }}</strong>.</p>
                @else
                    <ol class="relative space-y-4 border-l-2 border-slate-200 pl-5">
                        @foreach($paquete->estadoEventos as $evt)
                            <li class="relative">
                                <span class="absolute -left-[1.4rem] top-1 h-3 w-3 rounded-full bg-[#F0A63A] ring-4 ring-white"></span>
                                <p class="font-semibold capitalize text-slate-800">{{ str_replace('_',' ', $evt->estado) }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ optional($evt->evento_at)->format('d/m/Y H:i') ?? '—' }}
                                    · {{ $evt->estado_origen === 'primetrack' ? 'Primetrack' : 'Local' }}
                                    @if($evt->ubicacion) · {{ $evt->ubicacion }} @endif
                                </p>
                                @if($evt->descripcion)
                                    <p class="mt-1 text-sm text-slate-600">{{ $evt->descripcion }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                @endif
            </section>
        </div>

        <aside class="h-fit rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="mb-2 font-bold text-slate-800">Notas</h2>
            <p class="text-sm text-slate-600">{{ $paquete->notas ?: 'Sin notas adicionales.' }}</p>
        </aside>
    </div>
</div>
@endsection
