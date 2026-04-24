@extends('layouts.app-new')

@section('title', 'Factura #' . $factura->id . ' - CH LOGISTICS ERP')
@section('navbar-title', 'Facturación')

@php
    $enc = $factura->encomienda;
    $encEstadoLabels = [
        'registrada' => 'Registrada',
        'recibida_miami' => 'Recibida Miami',
        'en_transito' => 'En tránsito',
        'recibida_nicaragua' => 'Recibida Nicaragua',
        'lista_entrega' => 'Lista para entrega',
        'entregada' => 'Entregada',
        'incidencia' => 'Incidencia',
    ];
    $histEnc = $enc ? $enc->historialEstados->sortBy('fecha_cambio')->values() : collect();
    $fechaRecepcionMiami = $histEnc->firstWhere('estado', 'recibida_miami')?->fecha_cambio;
    $fechaRecibidaNicaragua = $histEnc->firstWhere('estado', 'recibida_nicaragua')?->fecha_cambio;
    $estadoPagoLabel = str_replace('_', ' ', (string) $factura->estado_pago);
@endphp

@section('content')
<div class="mx-auto w-full max-w-[1100px] space-y-6 pb-10">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-base text-emerald-800" role="alert">
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('info_contabilidad'))
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-base text-amber-900" role="alert">
            <span class="font-medium"><i class="fas fa-calculator mr-2"></i>{{ session('info_contabilidad') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-base text-red-800" role="alert">
            <ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    @if($factura->anulada ?? false)
        <div class="rounded-xl border border-slate-300 bg-slate-100 p-5 shadow-sm sm:p-6">
            <h2 class="text-lg font-bold text-slate-900"><i class="fas fa-ban mr-2 text-slate-600"></i>Factura anulada</h2>
            <p class="mt-2 text-base text-slate-700">Esta factura no cuenta para cobros ni CxC. Los paquetes de paquetería quedaron liberados para emitir una nueva factura.</p>
            @if($factura->anulada_at)
                <p class="mt-2 text-sm text-slate-600">Fecha anulación: {{ $factura->anulada_at->format('d/m/Y H:i') }}@if($factura->anuladaPor) · {{ $factura->anuladaPor->nombre ?? $factura->anuladaPor->email }}@endif</p>
            @endif
            @if($factura->anulacion_motivo)
                <p class="mt-3 text-sm text-slate-800"><span class="font-semibold">Motivo:</span> {{ $factura->anulacion_motivo }}</p>
            @endif
        </div>
    @endif

    @if(!$factura->anulada && $factura->estado_pago === 'entregado_pagado' && ($factura->contabilidad_pendiente ?? false))
        <div class="rounded-xl border border-amber-300 bg-amber-50/90 p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-amber-950"><i class="fas fa-exclamation-circle mr-2 text-amber-600"></i>Contabilidad pendiente</h3>
                    <p class="mt-2 text-base text-amber-950/90">Esta factura está <strong>Entregada y pagada</strong>. Falta registrar el cobro en <strong>Contabilidad</strong> (cuenta banco/caja, método, referencia) para cerrar el control.</p>
                    @if(auth()->check() && auth()->user()->rol === 'admin')
                        <a href="{{ route('contabilidad.cobros.create', ['factura_id' => $factura->id]) }}" class="mt-3 inline-flex items-center gap-2 rounded-xl bg-[#15537c] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#0f3d5c]">
                            <i class="fas fa-hand-holding-usd"></i> Ir a registrar cobro
                        </a>
                    @else
                        <p class="mt-2 text-sm text-amber-900/80">Solicite al administrador que registre el cobro en Contabilidad.</p>
                    @endif
                </div>
                @if(auth()->check() && auth()->user()->rol === 'admin')
                    <form method="POST" action="{{ route('facturacion.contabilidad-marcar-verificada', $factura->id) }}" class="shrink-0" onsubmit="return confirm('¿Marcar contabilidad como completada sin registrar cobro aquí? Use solo en casos excepcionales.');">
                        @csrf
                        <button type="submit" class="rounded-lg border border-amber-600/50 bg-white px-4 py-2 text-sm font-semibold text-amber-900 hover:bg-amber-100">Marcar contabilidad OK (excepcional)</button>
                    </form>
                @endif
            </div>
        </div>
    @endif

    {{-- Cabecera --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-[#15537c] to-[#0f3d5c] p-6 text-white shadow-md sm:p-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm font-medium text-white/80">Facturación</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight sm:text-3xl">Factura #{{ $factura->id }}@if($factura->anulada ?? false)<span class="ml-2 align-middle text-base font-semibold text-amber-200">(Anulada)</span>@endif</h1>
                <p class="mt-3 max-w-2xl text-sm leading-relaxed text-white/85">Registro interno en el sistema. Montos y diseño final del documento se confirman en el PDF.</p>
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    @if(($factura->tipo_factura ?? '') === 'encomienda_familiar')
                        <span class="inline-flex items-center gap-2 rounded-full bg-amber-400/25 px-3 py-1.5 text-sm font-semibold text-amber-50 ring-1 ring-amber-300/40 backdrop-blur-sm">
                            <i class="fas fa-people-carry text-xs opacity-90"></i>
                            Encomienda familiar
                        </span>
                    @else
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1.5 text-sm font-semibold text-white ring-1 ring-white/25 backdrop-blur-sm">
                            <i class="fas fa-box text-xs opacity-90"></i>
                            Paquetería
                        </span>
                    @endif
                    <span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-sm text-white/90">
                        <i class="fas fa-calendar-day mr-2 text-xs opacity-90"></i>
                        {{ \Carbon\Carbon::parse($factura->fecha_factura)->format('d/m/Y') }}
                    </span>
                    @if($enc)
                        <span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-sm text-white/90">
                            <i class="fas fa-box mr-2 text-xs opacity-90"></i>
                            {{ $enc->codigo }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="flex flex-shrink-0 flex-wrap gap-2">
                <a href="{{ route('facturacion.index') }}" class="inline-flex items-center justify-center rounded-xl border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-medium text-white backdrop-blur-sm transition hover:bg-white/20">
                    <i class="fas fa-arrow-left mr-2 text-xs"></i> Volver al listado
                </a>
                @if(!($factura->anulada ?? false))
                <a href="{{ route('facturacion.edit', $factura->id) }}" class="inline-flex items-center justify-center rounded-xl border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-medium text-white backdrop-blur-sm transition hover:bg-white/20">
                    <i class="fas fa-edit mr-2 text-xs"></i> Editar
                </a>
                @endif
                <a href="{{ route('facturacion.preview', $factura->id) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-xl border border-white/30 bg-white px-4 py-2.5 text-sm font-semibold text-[#15537c] transition hover:bg-slate-50">
                    <i class="fas fa-eye mr-2 text-xs"></i> Ver PDF
                </a>
            </div>
        </div>
    </div>

    @if(!empty($muestraSeccionAnular))
    <div class="rounded-xl border border-rose-200 bg-rose-50/80 p-5 shadow-sm sm:p-6">
        <h2 class="text-lg font-bold text-rose-950"><i class="fas fa-undo-alt mr-2"></i>Anular factura</h2>
        @if(!empty($puedeAnular))
            <p class="mt-2 text-base text-rose-900/90">Se revierte el asiento y CxC de emisión, se quitan cobros <strong>solo importados</strong> desde pagos antiguos (si los hubiera) y se liberan los paquetes para volver a facturar. No aplica si ya registró cobros desde <strong>Contabilidad → Registrar cobro</strong>.</p>
            @if(($cobrosSoloImportados ?? 0) > 0)
                <p class="mt-2 rounded-lg border border-rose-300/60 bg-white/80 px-3 py-2 text-sm text-rose-900">Hay <strong>{{ $cobrosSoloImportados }}</strong> movimiento(s) en contabilidad importados automáticamente; al anular se eliminarán junto con sus asientos.</p>
            @endif
            <form method="POST" action="{{ route('facturacion.anular', $factura->id) }}" class="mt-4 space-y-4" onsubmit="return confirm('¿Anular esta factura? Esta acción no se puede deshacer desde la pantalla.');">
                @csrf
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-rose-900">Motivo (opcional)</label>
                    <textarea name="motivo" rows="2" class="w-full rounded-lg border border-rose-200 bg-white px-4 py-2.5 text-base text-slate-900 focus:border-rose-500 focus:ring-1 focus:ring-rose-500" placeholder="Ej.: monto incorrecto, cliente equivocado...">{{ old('motivo') }}</textarea>
                </div>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-rose-700 bg-rose-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-rose-800">
                    <i class="fas fa-ban"></i> Anular factura
                </button>
            </form>
        @else
            <p class="mt-2 text-base text-rose-900/90">No se puede anular desde aquí porque ya existe al menos un cobro registrado en <strong>Contabilidad → Registrar cobro</strong> (usuario asignado). En ese caso hay que corregir con contador o proceso manual de reverso.</p>
        @endif
    </div>
    @endif

    {{-- Resumen --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="mb-5 flex items-start gap-3 border-b border-slate-100 pb-4 text-lg font-semibold tracking-tight text-slate-900">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#15537c]/10 text-base text-[#15537c]">
                <i class="fas fa-file-invoice-dollar"></i>
            </span>
            <span>
                <span class="block leading-tight">Resumen de la factura</span>
                <span class="mt-1 block text-xs font-medium normal-case text-slate-500">Cliente, montos y vínculo con encomienda o paquetes</span>
            </span>
        </h2>
        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cliente</dt>
                <dd class="mt-1 text-base font-semibold text-slate-900">
                    {{ optional($factura->cliente)->nombre_completo
                        ?? optional(optional($enc)->remitente)->nombre_completo
                        ?? '—' }}
                </dd>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Fecha de factura</dt>
                <dd class="mt-1 text-base font-medium text-slate-900">{{ \Carbon\Carbon::parse($factura->fecha_factura)->format('d/m/Y') }}</dd>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Número de acta</dt>
                <dd class="mt-1 text-base text-slate-900">{{ $factura->numero_acta ?: '—' }}</dd>
            </div>
            <div class="rounded-xl border border-[#15537c]/15 bg-[#15537c]/5 p-4">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-600">Monto total</dt>
                <dd class="mt-1 text-xl font-bold text-[#15537c]">${{ number_format((float) $factura->monto_total, 2) }}</dd>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Moneda</dt>
                <dd class="mt-1 text-base font-medium text-slate-900">{{ $factura->moneda }}</dd>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Estado de pago</dt>
                <dd class="mt-1 text-base font-medium text-slate-900">{{ $estadoPagoLabel }}</dd>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Paquetes / ítems</dt>
                <dd class="mt-1 text-base font-medium text-slate-900">{{ $factura->cantidad_paquetes ?? 0 }}</dd>
            </div>
            @if($factura->creador)
                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Registrada por</dt>
                    <dd class="mt-1 text-base text-slate-800">{{ $factura->creador->nombre ?? $factura->creador->email }}</dd>
                </div>
            @endif
            @if($factura->encomienda_id && $enc)
                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4 sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Encomienda vinculada</dt>
                    <dd class="mt-2">
                        <a href="{{ route('encomiendas.show', $factura->encomienda_id) }}" class="inline-flex items-center gap-2 font-semibold text-[#15537c] hover:underline">
                            <i class="fas fa-external-link-alt text-xs opacity-80"></i>
                            {{ $enc->codigo }}
                        </a>
                        <span class="text-slate-600"> — {{ optional($enc->remitente)->nombre_completo ?? '—' }} <span class="text-slate-400">→</span> {{ optional($enc->destinatario)->nombre_completo ?? '—' }}</span>
                        <p class="mt-2 text-xs text-slate-500">Abre el detalle completo para historial, fotos de bultos y edición.</p>
                    </dd>
                </div>
            @endif
        </dl>
    </div>

    @if($enc)
        {{-- Datos de la encomienda (remitente / destinatario / fechas) --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="mb-5 flex items-start gap-3 border-b border-slate-100 pb-4 text-lg font-semibold tracking-tight text-slate-900">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#15537c]/10 text-base text-[#15537c]">
                    <i class="fas fa-people-carry-box"></i>
                </span>
                <span>
                    <span class="block leading-tight">Datos del envío (encomienda)</span>
                    <span class="mt-1 block text-xs font-medium normal-case text-slate-500">Contactos, direcciones y fechas según el estado en el sistema</span>
                </span>
            </h2>

            <div class="mb-6 grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Estado actual</dt>
                    <dd class="mt-1 text-base font-semibold text-slate-900">{{ $encEstadoLabels[$enc->estado_actual] ?? ucwords(str_replace('_', ' ', (string) $enc->estado_actual)) }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Servicio</dt>
                    <dd class="mt-1 text-base text-slate-900">{{ ($enc->tipo_servicio ?? 'maritimo') === 'aereo' ? 'Aéreo' : 'Marítimo' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Bultos registrados</dt>
                    <dd class="mt-1 text-base font-medium text-slate-900">{{ $enc->items->count() }}</dd>
                </div>
            </div>

            <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Fechas de seguimiento</h3>
            <div class="mb-6 grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Registro</dt>
                    <dd class="mt-1 text-sm font-medium text-slate-900">{{ $enc->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Recepción Miami</dt>
                    <dd class="mt-1 text-sm font-medium text-slate-900">{{ $fechaRecepcionMiami ? $fechaRecepcionMiami->timezone(config('app.timezone'))->format('d/m/Y H:i') : '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Recibida Nicaragua</dt>
                    <dd class="mt-1 text-sm font-medium text-slate-900">{{ $fechaRecibidaNicaragua ? $fechaRecibidaNicaragua->timezone(config('app.timezone'))->format('d/m/Y H:i') : '—' }}</dd>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div>
                    <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-800">
                        <span class="flex h-8 w-10 items-center justify-center rounded-lg bg-[#15537c]/10 text-[#15537c]"><i class="fas fa-user text-xs"></i></span>
                        Remitente
                    </h3>
                    @if($enc->remitente)
                        @php $r = $enc->remitente; @endphp
                        <dl class="space-y-3">
                            <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nombre</dt>
                                <dd class="mt-1 text-base font-medium text-slate-900">{{ $r->nombre_completo }}</dd>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Teléfono</dt>
                                    <dd class="mt-1 text-sm">
                                        @if($r->telefono)
                                            <a href="tel:{{ preg_replace('/\s+/', '', $r->telefono) }}" class="font-medium text-[#15537c] hover:underline">{{ $r->telefono }}</a>
                                        @else <span class="text-slate-400">—</span> @endif
                                    </dd>
                                </div>
                                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Correo</dt>
                                    <dd class="mt-1 break-all text-sm">
                                        @if($r->correo)
                                            <a href="mailto:{{ $r->correo }}" class="font-medium text-[#15537c] hover:underline">{{ $r->correo }}</a>
                                        @else <span class="text-slate-400">—</span> @endif
                                    </dd>
                                </div>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Dirección</dt>
                                <dd class="mt-1 text-sm leading-relaxed text-slate-800 whitespace-pre-wrap">{{ trim((string) ($r->direccion ?? '')) !== '' ? $r->direccion : '—' }}</dd>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Ciudad</dt>
                                    <dd class="mt-1 text-sm text-slate-800">{{ $r->ciudad ?: '—' }}</dd>
                                </div>
                                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Estado / país</dt>
                                    <dd class="mt-1 text-sm text-slate-800">{{ $r->estado ?: '—' }}</dd>
                                </div>
                            </div>
                        </dl>
                    @else
                        <div class="rounded-xl border border-dashed border-slate-200 p-4 text-sm text-slate-500">Sin remitente.</div>
                    @endif
                </div>

                <div>
                    <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-800">
                        <span class="flex h-8 w-10 items-center justify-center rounded-lg bg-[#15537c]/10 text-[#15537c]"><i class="fas fa-location-dot text-xs"></i></span>
                        Destinatario
                    </h3>
                    @if($enc->destinatario)
                        @php $d = $enc->destinatario; @endphp
                        <dl class="space-y-3">
                            <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nombre</dt>
                                <dd class="mt-1 text-base font-medium text-slate-900">{{ $d->nombre_completo }}</dd>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Teléfono 1</dt>
                                    <dd class="mt-1 text-sm">
                                        @if($d->telefono_1)
                                            <a href="tel:{{ preg_replace('/\s+/', '', $d->telefono_1) }}" class="font-medium text-[#15537c] hover:underline">{{ $d->telefono_1 }}</a>
                                        @else <span class="text-slate-400">—</span> @endif
                                    </dd>
                                </div>
                                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Teléfono 2</dt>
                                    <dd class="mt-1 text-sm">
                                        @if($d->telefono_2)
                                            <a href="tel:{{ preg_replace('/\s+/', '', $d->telefono_2) }}" class="font-medium text-[#15537c] hover:underline">{{ $d->telefono_2 }}</a>
                                        @else <span class="text-slate-400">—</span> @endif
                                    </dd>
                                </div>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Dirección de entrega</dt>
                                <dd class="mt-1 text-sm leading-relaxed text-slate-800 whitespace-pre-wrap">{{ trim((string) ($d->direccion ?? '')) !== '' ? $d->direccion : '—' }}</dd>
                            </div>
                            @if(trim((string) ($d->referencias ?? '')) !== '')
                                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Referencias</dt>
                                    <dd class="mt-1 text-sm text-slate-800 whitespace-pre-wrap">{{ $d->referencias }}</dd>
                                </div>
                            @endif
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Ciudad</dt>
                                    <dd class="mt-1 text-sm text-slate-800">{{ $d->ciudad ?: '—' }}</dd>
                                </div>
                                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Departamento</dt>
                                    <dd class="mt-1 text-sm text-slate-800">{{ $d->departamento ?: '—' }}</dd>
                                </div>
                            </div>
                        </dl>
                    @else
                        <div class="rounded-xl border border-dashed border-slate-200 p-4 text-sm text-slate-500">Sin destinatario.</div>
                    @endif
                </div>
            </div>

            @php
                $desc = trim((string) ($enc->descripcion_general ?? ''));
            @endphp
            @if($desc !== '')
                <div class="mt-6 rounded-xl border border-sky-100 bg-sky-50/50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-sky-800">Descripción general del envío</p>
                    <p class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-slate-800">{{ $desc }}</p>
                </div>
            @endif
        </div>
    @endif

    {{-- Nota interna --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="mb-5 flex items-start gap-3 border-b border-slate-100 pb-4 text-lg font-semibold tracking-tight text-slate-900">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500/15 text-base text-amber-700">
                <i class="fas fa-sticky-note"></i>
            </span>
            <span>
                <span class="block leading-tight">Nota interna</span>
                <span class="mt-1 block text-xs font-medium normal-case text-slate-500">No se incluye en el PDF de factura / nota de cobro</span>
            </span>
        </h2>
        <div class="rounded-xl border border-amber-200/70 bg-amber-50/60 p-4 text-sm leading-relaxed text-slate-800 whitespace-pre-wrap">{{ $factura->nota ? trim($factura->nota) : 'Sin nota registrada para esta factura.' }}</div>
    </div>

    @if(trim((string) ($factura->entrega_nombre ?? '')) !== '' || trim((string) ($factura->entrega_cedula ?? '')) !== '' || trim((string) ($factura->entrega_telefono ?? '')) !== '')
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="mb-5 flex items-start gap-3 border-b border-slate-100 pb-4 text-lg font-semibold tracking-tight text-slate-900">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#15537c]/10 text-base text-[#15537c]">
                    <i class="fas fa-hand-holding-box"></i>
                </span>
                <span>
                    <span class="block leading-tight">Datos de entrega (factura)</span>
                    <span class="mt-1 block text-xs font-medium normal-case text-slate-500">Receptor registrado en esta factura</span>
                </span>
            </h2>
            <dl class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4 sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Receptor</dt>
                    <dd class="mt-1 text-base font-medium text-slate-900">{{ $factura->entrega_nombre ?: '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cédula</dt>
                    <dd class="mt-1 text-base text-slate-900">{{ $factura->entrega_cedula ?: '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Teléfono</dt>
                    <dd class="mt-1 text-base text-slate-900">
                        @if($factura->entrega_telefono)
                            <a href="tel:{{ preg_replace('/\s+/', '', $factura->entrega_telefono) }}" class="font-medium text-[#15537c] hover:underline">{{ $factura->entrega_telefono }}</a>
                        @else — @endif
                    </dd>
                </div>
            </dl>
        </div>
    @endif

    @if($factura->paquetes && $factura->paquetes->isNotEmpty())
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="mb-5 flex items-start gap-3 border-b border-slate-100 pb-4 text-lg font-semibold tracking-tight text-slate-900">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#15537c]/10 text-base text-[#15537c]">
                    <i class="fas fa-boxes-stacked"></i>
                </span>
                <span>
                    <span class="block leading-tight">Paquetes facturados</span>
                    <span class="mt-1 block text-xs font-medium normal-case text-slate-500">Inventario vinculado a esta factura</span>
                </span>
            </h2>
            <div class="overflow-hidden rounded-xl border border-slate-200 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[480px] text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-gradient-to-r from-[#15537c]/12 via-slate-50 to-slate-50">
                                <th class="px-4 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-600">Guía</th>
                                <th class="px-4 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-600">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($factura->paquetes as $idx => $p)
                                <tr class="transition hover:bg-slate-50/80 {{ $idx % 2 === 1 ? 'bg-slate-50/40' : '' }}">
                                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $p->numero_guia ?? $p->id }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $p->estado ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
