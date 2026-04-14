@extends('layouts.app-new')

@section('title', 'Detalle del Paquete - CH LOGISTICS ERP')
@section('navbar-title', 'Detalle del Paquete')

@section('content')
@php $esAgente = auth()->check() && (auth()->user()->rol ?? null) === 'agente'; @endphp
<div class="mx-auto w-full max-w-7xl px-4 sm:px-6 space-y-8">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl px-5 py-4 shadow-sm" style="background: linear-gradient(90deg, #15537c 0%, #2d6a9a 100%); min-height: 90px;">
        <div class="flex items-center gap-3">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-white shadow-sm">
                <i class="fas fa-box-open text-[#15537c]" style="font-size: 1.75rem;"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-wide text-white">Detalle del Paquete</h1>
                <p class="text-sm text-white/80">Información completa del paquete seleccionado</p>
            </div>
        </div>
        <a href="{{ route('inventario.index') }}" class="inline-flex items-center gap-2 rounded-lg border-2 border-white/80 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/20">
            <i class="fas fa-arrow-left"></i> Volver al Inventario
        </a>
    </div>

    {{-- Card detalle --}}
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
        <h2 class="text-lg font-semibold text-slate-800 border-b border-slate-100 pb-3 mb-6">
            <i class="fas fa-info-circle text-[#15537c] mr-2"></i>Información del Paquete
        </h2>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <p class="text-sm font-medium text-slate-500"><i class="fas fa-user mr-1.5 text-[#15537c]"></i>Cliente</p>
                <p class="mt-0.5 text-slate-800">{{ $paquete->cliente->nombre_completo }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500"><i class="fas fa-shipping-fast mr-1.5 text-[#15537c]"></i>Servicio</p>
                <p class="mt-0.5 text-slate-800">{{ $paquete->servicio ? $paquete->servicio->tipo_servicio : 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500"><i class="fas fa-weight-hanging mr-1.5 text-[#15537c]"></i>Peso (lb)</p>
                <p class="mt-0.5 text-slate-800">{{ number_format($paquete->peso_lb, 2) }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500"><i class="fas fa-cube mr-1.5 text-[#15537c]"></i>Volumen (ft³)</p>
                <p class="mt-0.5 text-slate-800">{{ number_format($paquete->volumen_pie3, 2) }}</p>
            </div>
            @unless($esAgente)
                <div>
                    <p class="text-sm font-medium text-slate-500"><i class="fas fa-dollar-sign mr-1.5 text-[#15537c]"></i>Tarifa manual</p>
                    <p class="mt-0.5 text-slate-800">{{ $paquete->tarifa_manual ? '$' . number_format($paquete->tarifa_manual, 2) : 'No aplica' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500"><i class="fas fa-calculator mr-1.5 text-[#15537c]"></i>Monto calculado</p>
                    <p class="mt-0.5 text-lg font-semibold text-[#15537c]">${{ number_format($paquete->monto_calculado, 2) }}</p>
                </div>
            @endunless
            <div>
                <p class="text-sm font-medium text-slate-500"><i class="fas fa-calendar mr-1.5 text-[#15537c]"></i>Fecha de ingreso</p>
                <p class="mt-0.5 text-slate-800">{{ \Carbon\Carbon::parse($paquete->fecha_ingreso)->format('d/m/Y') }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500"><i class="fas fa-info-circle mr-1.5 text-[#15537c]"></i>Estado</p>
                <p class="mt-0.5">
                    @php
                        $statusStyles = [
                            'recibido' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'en_transito' => 'bg-amber-100 text-amber-800 border-amber-200',
                            'entregado' => 'bg-[#15537c]/10 text-[#15537c] border-[#15537c]/30',
                            'pendiente' => 'bg-slate-100 text-slate-700 border-slate-200',
                            'en_oficina' => 'bg-sky-100 text-sky-800 border-sky-200',
                        ];
                        $statusIcons = [
                            'recibido' => 'check-circle',
                            'en_transito' => 'truck',
                            'entregado' => 'box-open',
                            'pendiente' => 'clock',
                            'en_oficina' => 'building',
                        ];
                        $style = $statusStyles[$paquete->estado] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                        $icon = $statusIcons[$paquete->estado] ?? 'question-circle';
                    @endphp
                    <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-sm font-medium {{ $style }}">
                        <i class="fas fa-{{ $icon }}"></i>
                        {{ ucfirst(str_replace('_', ' ', $paquete->estado)) }}
                    </span>
                </p>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500"><i class="fas fa-barcode mr-1.5 text-[#15537c]"></i>Número de guía</p>
                <p class="mt-0.5 text-slate-800">{{ $paquete->numero_guia ?? 'No asignado' }}</p>
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
                <p class="text-sm font-medium text-slate-500"><i class="fas fa-sticky-note mr-1.5 text-[#15537c]"></i>Notas</p>
                <p class="mt-0.5 text-slate-800">{{ $paquete->notas ?? 'Sin observaciones' }}</p>
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
                <p class="text-sm font-medium text-slate-500"><i class="fas fa-file-invoice-dollar mr-1.5 text-[#15537c]"></i>Factura asociada</p>
                <p class="mt-0.5">
                    @if($paquete->factura)
                        <a href="{{ route('facturacion.preview', $paquete->factura->id) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-[#15537c]/40 bg-[#15537c]/5 px-3 py-1.5 text-sm font-medium text-[#15537c] hover:bg-[#15537c]/10">
                            <i class="fas fa-file-invoice-dollar"></i> Ver factura #{{ $paquete->factura->id }}
                        </a>
                    @else
                        <span class="text-slate-500">No asignada</span>
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
