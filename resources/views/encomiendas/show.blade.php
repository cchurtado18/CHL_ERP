@extends('layouts.app-new')

@section('title', 'Detalle Encomienda')
@section('navbar-title', 'Detalle Encomienda')

@php
    $estadoLabels = [
        'registrada' => 'Registrada',
        'recibida_miami' => 'Recibida Miami',
        'en_transito' => 'En tránsito',
        'recibida_nicaragua' => 'Recibida Nicaragua',
        'lista_entrega' => 'Lista para entrega',
        'entregada' => 'Entregada',
        'incidencia' => 'Incidencia',
    ];
    $estadoActual = $encomienda->estado_actual ?? 'registrada';
    $histOrdenado = $encomienda->historialEstados->sortBy('fecha_cambio')->values();
    $fechaRecepcionMiami = $histOrdenado->firstWhere('estado', 'recibida_miami')?->fecha_cambio;
    $fechaRecibidaNicaragua = $histOrdenado->firstWhere('estado', 'recibida_nicaragua')?->fecha_cambio;
    $fechaEntregada = $histOrdenado->firstWhere('estado', 'entregada')?->fecha_cambio;
    $esAgente = auth()->check() && (auth()->user()->rol ?? null) === 'agente';
    $puedeVerFactura = auth()->check() && in_array(auth()->user()->rol ?? '', ['admin', 'agente'], true);

    $inputClass = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-[#15537c] focus:outline-none focus:ring-2 focus:ring-[#15537c]/20';
@endphp

@section('content')
<div class="mx-auto w-full max-w-[1300px] space-y-6 pb-10">
    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800 shadow-sm">{{ session('success') }}</div>
    @endif

    {{-- Cabecera --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-[#15537c] to-[#0f3d5c] p-6 text-white shadow-md sm:p-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm font-medium text-white/80">Código de encomienda</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight sm:text-3xl">{{ $encomienda->codigo }}</h1>
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-sm font-medium backdrop-blur-sm">
                        {{ $estadoLabels[$estadoActual] ?? ucwords(str_replace('_', ' ', $estadoActual)) }}
                    </span>
                    <span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-sm text-white/90">
                        <i class="fas fa-ship mr-2 opacity-80"></i>
                        {{ ($encomienda->tipo_servicio ?? 'maritimo') === 'aereo' ? 'Servicio aéreo' : 'Servicio marítimo' }}
                    </span>
                    <span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-sm text-white/90">
                        <i class="fas fa-box mr-2 opacity-80"></i>
                        {{ $encomienda->items->count() }} {{ $encomienda->items->count() === 1 ? 'bulto' : 'bultos' }}
                    </span>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 lg:shrink-0">
                <a href="{{ route('encomiendas.edit', $encomienda->id) }}" class="inline-flex items-center justify-center rounded-xl border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-medium text-white backdrop-blur-sm transition hover:bg-white/20">
                    <i class="fas fa-pen mr-2 text-xs"></i> Editar
                </a>
                <a href="{{ route('encomiendas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-white/30 bg-white px-4 py-2.5 text-sm font-semibold text-[#15537c] transition hover:bg-slate-50">
                    <i class="fas fa-arrow-left mr-2 text-xs"></i> Volver al listado
                </a>
            </div>
        </div>
    </div>

    {{-- Fechas y montos --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 {{ $esAgente ? 'lg:col-span-3' : 'lg:col-span-2' }}">
            <h2 class="mb-5 flex items-start gap-3 border-b border-slate-100 pb-4 text-lg font-semibold tracking-tight text-slate-900">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#15537c]/10 text-base text-[#15537c]">
                    <i class="fas fa-calendar-alt"></i>
                </span>
                <span>
                    <span class="block leading-tight">Fechas y seguimiento</span>
                    <span class="mt-1 block text-xs font-medium normal-case text-slate-500">Tiempos clave del envío y del registro</span>
                </span>
            </h2>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Registro en sistema</dt>
                    <dd class="mt-1 text-base font-medium text-slate-900">{{ $encomienda->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '—' }}</dd>
                    @if($encomienda->creador)
                        <dd class="mt-1 text-sm text-slate-600">Por {{ $encomienda->creador->nombre ?? $encomienda->creador->email }}</dd>
                    @endif
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Recepción / recolecta (Miami)</dt>
                    <dd class="mt-1 text-base font-medium text-slate-900">
                        {{ $fechaRecepcionMiami ? $fechaRecepcionMiami->timezone(config('app.timezone'))->format('d/m/Y H:i') : '—' }}
                    </dd>
                    <dd class="mt-1 text-xs leading-relaxed text-slate-500">Según primer cambio a «Recibida Miami» en el historial.</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Recibida en Nicaragua</dt>
                    <dd class="mt-1 text-base font-medium text-slate-900">
                        {{ $fechaRecibidaNicaragua ? $fechaRecibidaNicaragua->timezone(config('app.timezone'))->format('d/m/Y H:i') : '—' }}
                    </dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Entrega al destinatario</dt>
                    <dd class="mt-1 text-base font-medium text-slate-900">
                        {{ $fechaEntregada ? $fechaEntregada->timezone(config('app.timezone'))->format('d/m/Y H:i') : '—' }}
                    </dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4 sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Última modificación del registro</dt>
                    <dd class="mt-1 text-base font-medium text-slate-900">{{ $encomienda->updated_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '—' }}</dd>
                    @if($encomienda->editor && $encomienda->updated_at && $encomienda->updated_at->ne($encomienda->created_at))
                        <dd class="mt-1 text-sm text-slate-600">Por {{ $encomienda->editor->nombre ?? $encomienda->editor->email }}</dd>
                    @endif
                </div>
            </dl>
        </div>
        @unless($esAgente)
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="mb-5 flex items-start gap-3 border-b border-slate-100 pb-4 text-lg font-semibold tracking-tight text-slate-900">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#15537c]/10 text-base text-[#15537c]">
                    <i class="fas fa-dollar-sign"></i>
                </span>
                <span>
                    <span class="block leading-tight">Montos</span>
                    <span class="mt-1 block text-xs font-medium normal-case text-slate-500">Totales calculados de la encomienda</span>
                </span>
            </h2>
            <ul class="space-y-3 text-sm">
                <li class="flex justify-between rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3">
                    <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Subtotal</span>
                    <span class="font-semibold text-slate-900">${{ number_format((float) $encomienda->subtotal, 2) }}</span>
                </li>
                <li class="flex justify-between rounded-xl border border-[#15537c]/15 bg-[#15537c]/5 px-4 py-3">
                    <span class="text-xs font-semibold uppercase tracking-wide text-slate-600">Total</span>
                    <span class="text-lg font-bold text-[#15537c]">${{ number_format((float) $encomienda->total, 2) }}</span>
                </li>
                @if($encomienda->valor_declarado !== null && (float) $encomienda->valor_declarado > 0)
                    <li class="flex justify-between rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Valor declarado</span>
                        <span class="font-semibold text-slate-900">${{ number_format((float) $encomienda->valor_declarado, 2) }}</span>
                    </li>
                @endif
            </ul>
            @if($encomienda->factura)
                <div class="mt-4 rounded-xl border border-[#15537c]/20 bg-[#15537c]/5 p-4 text-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#15537c]">Factura asociada</p>
                    <p class="mt-2 text-slate-700">
                        @if($puedeVerFactura)
                            <a href="{{ route('facturacion.show', $encomienda->factura->id) }}" class="font-semibold text-[#15537c] underline decoration-[#15537c]/30 underline-offset-2 hover:decoration-[#15537c]">Ver factura #{{ $encomienda->factura->id }}</a>
                            @if($encomienda->factura->numero_acta)
                                <span class="mt-1 block text-xs text-slate-600">Acta: {{ $encomienda->factura->numero_acta }}</span>
                            @endif
                        @else
                            <span>Registrada (consulta con administración para abrirla).</span>
                        @endif
                    </p>
                </div>
            @endif
        </div>
        @endunless
    </div>

    {{-- Remitente y destinatario --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="mb-5 flex items-start gap-3 border-b border-slate-100 pb-4 text-lg font-semibold tracking-tight text-slate-900">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#15537c]/10 text-base text-[#15537c]">
                    <i class="fas fa-user"></i>
                </span>
                <span>
                    <span class="block leading-tight">Remitente</span>
                    <span class="mt-1 block text-xs font-medium normal-case text-slate-500">Origen del envío</span>
                </span>
            </h2>
            @if($encomienda->remitente)
                @php $r = $encomienda->remitente; @endphp
                <dl class="space-y-4">
                    <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nombre completo</dt>
                        <dd class="mt-1 text-base font-medium text-slate-900">{{ $r->nombre_completo }}</dd>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Teléfono</dt>
                            <dd class="mt-1 text-base">
                                @if($r->telefono)
                                    <a href="tel:{{ preg_replace('/\s+/', '', $r->telefono) }}" class="font-medium text-[#15537c] hover:underline">{{ $r->telefono }}</a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </dd>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Correo</dt>
                            <dd class="mt-1 break-all text-base">
                                @if($r->correo)
                                    <a href="mailto:{{ $r->correo }}" class="font-medium text-[#15537c] hover:underline">{{ $r->correo }}</a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </dd>
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Dirección</dt>
                        <dd class="mt-1 text-sm leading-relaxed text-slate-800 whitespace-pre-wrap">{{ trim((string) ($r->direccion ?? '')) !== '' ? $r->direccion : '—' }}</dd>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Ciudad</dt>
                            <dd class="mt-1 text-base text-slate-800">{{ $r->ciudad ?: '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Estado / provincia</dt>
                            <dd class="mt-1 text-base text-slate-800">{{ $r->estado ?: '—' }}</dd>
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Identificación</dt>
                        <dd class="mt-1 text-base text-slate-800">{{ $r->identificacion ?: '—' }}</dd>
                    </div>
                </dl>
            @else
                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-6 text-center text-sm text-slate-500">Sin datos de remitente.</div>
            @endif
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="mb-5 flex items-start gap-3 border-b border-slate-100 pb-4 text-lg font-semibold tracking-tight text-slate-900">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#15537c]/10 text-base text-[#15537c]">
                    <i class="fas fa-location-dot"></i>
                </span>
                <span>
                    <span class="block leading-tight">Destinatario</span>
                    <span class="mt-1 block text-xs font-medium normal-case text-slate-500">Datos de entrega</span>
                </span>
            </h2>
            @if($encomienda->destinatario)
                @php $d = $encomienda->destinatario; @endphp
                <dl class="space-y-4">
                    <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nombre completo</dt>
                        <dd class="mt-1 text-base font-medium text-slate-900">{{ $d->nombre_completo }}</dd>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Teléfono principal</dt>
                            <dd class="mt-1 text-base">
                                @if($d->telefono_1)
                                    <a href="tel:{{ preg_replace('/\s+/', '', $d->telefono_1) }}" class="font-medium text-[#15537c] hover:underline">{{ $d->telefono_1 }}</a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </dd>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Teléfono alterno</dt>
                            <dd class="mt-1 text-base">
                                @if($d->telefono_2)
                                    <a href="tel:{{ preg_replace('/\s+/', '', $d->telefono_2) }}" class="font-medium text-[#15537c] hover:underline">{{ $d->telefono_2 }}</a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
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
                            <dd class="mt-1 text-sm leading-relaxed text-slate-800 whitespace-pre-wrap">{{ $d->referencias }}</dd>
                        </div>
                    @endif
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Ciudad</dt>
                            <dd class="mt-1 text-base text-slate-800">{{ $d->ciudad ?: '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Departamento</dt>
                            <dd class="mt-1 text-base text-slate-800">{{ $d->departamento ?: '—' }}</dd>
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cédula / ID</dt>
                            <dd class="mt-1 text-base text-slate-800">{{ $d->cedula ?: '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Autorizado para recibir</dt>
                            <dd class="mt-1 text-base font-medium text-slate-800">{{ ($d->autorizado_para_recibir ?? false) ? 'Sí' : 'No' }}</dd>
                        </div>
                    </div>
                </dl>
            @else
                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-6 text-center text-sm text-slate-500">Sin datos de destinatario.</div>
            @endif
        </div>
    </div>

    @if(trim((string) ($encomienda->observaciones ?? '')) !== '')
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="mb-5 flex items-start gap-3 border-b border-slate-100 pb-4 text-lg font-semibold tracking-tight text-slate-900">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500/15 text-base text-amber-700">
                    <i class="fas fa-note-sticky"></i>
                </span>
                <span>
                    <span class="block leading-tight">Observaciones internas</span>
                    <span class="mt-1 block text-xs font-medium normal-case text-slate-500">Notas del equipo (no visibles al cliente)</span>
                </span>
            </h2>
            <div class="rounded-xl border border-amber-200/60 bg-amber-50/40 p-4 text-sm leading-relaxed text-slate-800 whitespace-pre-wrap">{{ trim($encomienda->observaciones) }}</div>
        </div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="mb-5 flex items-start gap-3 border-b border-slate-100 pb-4 text-lg font-semibold tracking-tight text-slate-900">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#15537c]/10 text-base text-[#15537c]">
                <i class="fas fa-align-left"></i>
            </span>
            <span>
                <span class="block leading-tight">Descripción del envío</span>
                <span class="mt-1 block text-xs font-medium normal-case text-slate-500">Resumen general; el detalle por bulto está en la tabla inferior</span>
            </span>
        </h2>
        @if(trim((string) ($encomienda->descripcion_general ?? '')) !== '')
            <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4 text-base leading-relaxed text-slate-800 whitespace-pre-wrap">{{ trim($encomienda->descripcion_general) }}</div>
        @else
            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-6 text-center text-sm italic text-slate-500">Sin descripción registrada.</div>
        @endif
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="mb-5 flex items-start gap-3 border-b border-slate-100 pb-4 text-lg font-semibold tracking-tight text-slate-900">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#15537c]/10 text-base text-[#15537c]">
                <i class="fas fa-boxes-stacked"></i>
            </span>
            <span>
                <span class="block leading-tight">Bultos e ítems</span>
                <span class="mt-1 block text-xs font-medium normal-case text-slate-500">Cada fila corresponde a un bulto registrado</span>
            </span>
        </h2>
        <div class="overflow-hidden rounded-xl border border-slate-200 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[920px] text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-gradient-to-r from-[#15537c]/12 via-slate-50 to-slate-50">
                            <th class="px-4 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-600">Tipo / descripción</th>
                            <th class="px-4 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-600">Método</th>
                            <th class="px-4 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-600">Cant.</th>
                            <th class="px-4 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-600">Peso</th>
                            <th class="px-4 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-600">Dimensiones</th>
                            <th class="px-4 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-600">Pie³</th>
                            @unless($esAgente)
                                <th class="px-4 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-600">Tarifa</th>
                                <th class="px-4 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-600">Delivery</th>
                                <th class="px-4 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-600">Total</th>
                            @endunless
                            <th class="px-4 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-600">Fotos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($encomienda->items as $loopIndex => $item)
                        <tr class="align-top transition hover:bg-slate-50/80 {{ $loopIndex % 2 === 1 ? 'bg-slate-50/40' : '' }}">
                            <td class="px-4 py-4">
                                <span class="font-semibold text-slate-900">{{ $item->tipo_item }}</span>
                                @if(trim((string) ($item->descripcion ?? '')) !== '')
                                    <p class="mt-1.5 text-xs leading-relaxed text-slate-600 whitespace-pre-wrap">{{ $item->descripcion }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-slate-700">{{ $item->metodo_cobro === 'peso' ? 'Peso' : 'Pie cúbico' }}</td>
                            <td class="px-4 py-4 text-slate-700">{{ $item->cantidad }}</td>
                            <td class="px-4 py-4 text-slate-700">{{ $item->peso_lb ?? '—' }}</td>
                            <td class="px-4 py-4 text-slate-700">{{ $item->largo_in && $item->ancho_in && $item->alto_in ? $item->largo_in . ' × ' . $item->ancho_in . ' × ' . $item->alto_in . ' in' : '—' }}</td>
                            <td class="px-4 py-4 text-slate-700">{{ $item->pie_cubico ?? '—' }}</td>
                            @unless($esAgente)
                                <td class="px-4 py-4 font-medium text-slate-800">${{ number_format((float) $item->tarifa_manual, 2) }}</td>
                                <td class="px-4 py-4 text-slate-700">
                                    @if(!empty($item->incluye_delivery))
                                        <span class="font-medium text-emerald-700">Sí</span>
                                        @if($item->delivery_monto !== null && (float) $item->delivery_monto > 0)
                                            <span class="mt-0.5 block text-xs text-slate-600">${{ number_format((float) $item->delivery_monto, 2) }}</span>
                                        @endif
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-base font-bold text-[#15537c]">${{ number_format((float) $item->monto_total_item, 2) }}</td>
                            @endunless
                            <td class="px-4 py-4">
                                @php $fotosItem = $item->fotoPathsList(); @endphp
                                @if(count($fotosItem) > 0)
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($fotosItem as $fi => $_path)
                                            @php $fotoUrl = route('encomiendas.item-foto', ['encomienda' => $encomienda->id, 'item' => $item->id, 'index' => $fi]); @endphp
                                            <a href="{{ $fotoUrl }}" target="_blank" rel="noopener" class="inline-block overflow-hidden rounded-lg border border-slate-200 shadow-sm ring-2 ring-transparent transition hover:ring-[#15537c]/30" title="Foto {{ $fi + 1 }}">
                                                <img src="{{ $fotoUrl }}" alt="" class="h-11 w-11 object-cover" loading="lazy">
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="mb-5 flex items-start gap-3 border-b border-slate-100 pb-4 text-lg font-semibold tracking-tight text-slate-900">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#15537c]/10 text-base text-[#15537c]">
                    <i class="fas fa-clipboard-check"></i>
                </span>
                <span>
                    <span class="block leading-tight">Actualizar estado</span>
                    <span class="mt-1 block text-xs font-medium normal-case text-slate-500">Registra el siguiente paso del envío</span>
                </span>
            </h2>
            <form method="POST" action="{{ route('encomiendas.estados.store', $encomienda->id) }}" class="space-y-5">
                @csrf
                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                    <label for="estado_encomienda" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nuevo estado</label>
                    <select id="estado_encomienda" name="estado" required class="{{ $inputClass }}">
                        @foreach(['registrada','recibida_miami','en_transito','recibida_nicaragua','lista_entrega','entregada','incidencia'] as $estadoOpt)
                            <option value="{{ $estadoOpt }}" @selected($estadoOpt === $estadoActual)>{{ $estadoLabels[$estadoOpt] ?? ucwords(str_replace('_',' ', $estadoOpt)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                    <label for="comentario_estado" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Comentario</label>
                    <textarea id="comentario_estado" name="comentario" rows="3" placeholder="Detalle del cambio de estado…" class="{{ $inputClass }} resize-y"></textarea>
                </div>
                <button type="submit" class="w-full rounded-xl bg-[#15537c] px-5 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#124865] sm:w-auto">
                    <i class="fas fa-save mr-2 text-xs opacity-90"></i> Guardar estado
                </button>
            </form>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="mb-5 flex items-start gap-3 border-b border-slate-100 pb-4 text-lg font-semibold tracking-tight text-slate-900">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#15537c]/10 text-base text-[#15537c]">
                    <i class="fas fa-clock-rotate-left"></i>
                </span>
                <span>
                    <span class="block leading-tight">Historial de estados</span>
                    <span class="mt-1 block text-xs font-medium normal-case text-slate-500">Cronología de cambios y responsables</span>
                </span>
            </h2>
            <div class="max-h-[28rem] space-y-3 overflow-y-auto pr-1">
                @forelse($encomienda->historialEstados->sortByDesc('fecha_cambio') as $historial)
                    <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                        <div class="text-base font-semibold text-slate-900">{{ $estadoLabels[$historial->estado] ?? ucwords(str_replace('_',' ', $historial->estado)) }}</div>
                        <div class="mt-1.5 text-xs font-medium uppercase tracking-wide text-slate-500">{{ $historial->fecha_cambio?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '—' }}</div>
                        <div class="mt-1 text-sm text-slate-600">
                            <i class="fas fa-user mr-1 text-xs text-slate-400"></i>{{ $historial->usuario?->nombre ?? $historial->usuario?->email ?? 'Sistema' }}
                        </div>
                        <div class="mt-3 border-t border-slate-200/80 pt-3 text-sm leading-relaxed text-slate-700">{{ $historial->comentario ?: 'Sin comentario' }}</div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-8 text-center text-sm text-slate-500">Sin movimientos de estado.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
