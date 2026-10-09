{{--
  Línea de tiempo de avance del paquete.
  Vars:
    $estadoActual (string)
    $eventos (iterable opcional)
    $ingresadoLocal (bool) — true solo si el paquete ya está en nuestro inventario/CRM.
      "Listo" se completa únicamente con esto; CargoTrack no puede marcar Listo.
--}}
@php
    $stages = [
        ['key' => 'recibido', 'label' => 'Recibido', 'icon' => 'fa-box'],
        ['key' => 'en_transito', 'label' => 'En tránsito', 'icon' => 'fa-truck'],
        ['key' => 'en_aduana', 'label' => 'En destino', 'icon' => 'fa-flag'],
        ['key' => 'listo_entrega', 'label' => 'Listo', 'icon' => 'fa-clipboard-check'],
        ['key' => 'entregado', 'label' => 'Entregado', 'icon' => 'fa-circle-check'],
    ];

    $orden = [
        'pendiente' => 0,
        'recibido' => 0,
        'en_bodega' => 0,
        'en_transito' => 1,
        'en_camino' => 1,
        'en_aduana' => 2,
        'listo_entrega' => 3,
        'listo_para_entrega' => 3,
        'entregado' => 4,
        'devuelto' => 4,
        'cancelado' => -1,
    ];

    $ingresadoLocal = (bool) ($ingresadoLocal ?? false);
    $estadoNorm = strtolower(str_replace(' ', '_', (string) ($estadoActual ?? 'pendiente')));
    $estadoIdx = $orden[$estadoNorm] ?? 0;

    // Avance desde carrier / estado: sin ingreso local no puede pasar de "En destino".
    $carrierIdx = $estadoIdx;
    if (! $ingresadoLocal && $carrierIdx >= 3) {
        $carrierIdx = 2;
    }
    if ($ingresadoLocal && in_array($estadoNorm, ['listo_entrega', 'listo_para_entrega'], true)) {
        $carrierIdx = 3;
    }
    if ($ingresadoLocal && in_array($estadoNorm, ['entregado', 'devuelto'], true)) {
        $carrierIdx = 4;
    }

    $listaEventos = collect($eventos ?? [])->values();
@endphp

<div class="space-y-6">
    {{-- Stepper de avance --}}
    <div>
        <p class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-400">Avance del envío</p>
        <ol class="grid grid-cols-5 gap-1 sm:gap-2">
            @foreach($stages as $i => $stage)
                @php
                    if ($i < 3) {
                        // Recibido / tránsito / destino: según estado del carrier o CRM
                        $done = $carrierIdx > $i || ($ingresadoLocal && $carrierIdx >= 3 && $i <= 2);
                        $active = ! $done && $carrierIdx === $i && ! ($ingresadoLocal && $carrierIdx >= 3);
                    } elseif ($i === 3) {
                        // Listo: solo cuando nosotros ingresamos el paquete
                        $done = $ingresadoLocal && $carrierIdx > 3;
                        $active = $ingresadoLocal && $carrierIdx === 3;
                        // Si ya está en inventario pero el estado aún no es listo/entregado,
                        // igual marcamos Listo como completado por el ingreso.
                        if ($ingresadoLocal && $carrierIdx < 3) {
                            $done = true;
                            $active = false;
                        }
                    } else {
                        // Entregado
                        $done = false;
                        $active = $ingresadoLocal && $carrierIdx === 4;
                    }

                    // Conectores: tramo previo completado si este paso ya pasó o está activo tras uno done
                    $lineLeftOn = $i > 0 && (
                        ($i <= 3 && ($carrierIdx >= $i || ($ingresadoLocal && $i === 3)))
                        || ($i === 4 && $carrierIdx >= 4)
                        || ($i === 3 && $ingresadoLocal)
                    );
                    $lineRightOn = $i < 4 && (
                        ($i < 2 && $carrierIdx > $i)
                        || ($i === 2 && $ingresadoLocal)
                        || ($i === 3 && $carrierIdx > 3)
                    );
                @endphp
                <li class="flex flex-col items-center text-center">
                    <div class="relative mb-2 flex w-full items-center justify-center">
                        @if($i > 0)
                            <span class="absolute left-0 right-1/2 top-1/2 h-0.5 -translate-y-1/2 {{ $lineLeftOn ? 'bg-[#15537c]' : 'bg-slate-200' }}"></span>
                        @endif
                        @if($i < count($stages) - 1)
                            <span class="absolute left-1/2 right-0 top-1/2 h-0.5 -translate-y-1/2 {{ $lineRightOn ? 'bg-[#15537c]' : 'bg-slate-200' }}"></span>
                        @endif
                        <span class="relative z-10 flex h-9 w-9 items-center justify-center rounded-full text-sm
                            {{ $done ? 'bg-[#15537c] text-white' : ($active ? 'bg-[#F0A63A] text-[#0d3452] ring-4 ring-[#F0A63A]/25' : 'bg-slate-100 text-slate-400') }}">
                            <i class="fas {{ $stage['icon'] }}"></i>
                        </span>
                    </div>
                    <span class="text-[10px] font-semibold leading-tight sm:text-xs
                        {{ $done || $active ? 'text-slate-800' : 'text-slate-400' }}">
                        {{ $stage['label'] }}
                    </span>
                </li>
            @endforeach
        </ol>
        @unless($ingresadoLocal)
            <p class="mt-3 text-center text-xs text-slate-400">“Listo” se marca cuando el paquete se ingresa en CH Logistics.</p>
        @endunless
    </div>

    {{-- Historial cronológico --}}
    @if($listaEventos->isNotEmpty())
        <div>
            <p class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-400">Línea de tiempo</p>
            <ol class="relative space-y-0 border-l-2 border-[#15537c]/20 pl-0">
                @foreach($listaEventos as $idx => $evt)
                    @php
                        $isLast = $idx === $listaEventos->count() - 1;
                        if (is_array($evt)) {
                            $titulo = $evt['status'] ?? $evt['description'] ?? 'Evento';
                            $detalle = $evt['description'] ?? null;
                            $fecha = $evt['date'] ?? ($evt['evento_at'] ?? null);
                            $origen = $evt['origen'] ?? null;
                        } else {
                            $titulo = str_replace('_', ' ', $evt->estado ?? 'Evento');
                            $detalle = $evt->descripcion ?? null;
                            $fecha = optional($evt->evento_at)->format('d/m/Y H:i');
                            $origen = $evt->estado_origen ?? null;
                            if ($origen === 'cargotrack') $origen = 'CargoTrack';
                            elseif ($origen === 'primetrack') $origen = 'Primetrack';
                            elseif ($origen === 'local') $origen = 'Local';
                        }
                    @endphp
                    <li class="relative flex gap-4 pb-6 last:pb-0">
                        <span class="absolute -left-[5px] top-1.5 h-2.5 w-2.5 rounded-full
                            {{ $isLast ? 'bg-[#F0A63A] ring-4 ring-[#F0A63A]/20' : 'bg-[#15537c]' }}"></span>
                        <div class="ml-5 min-w-0 flex-1">
                            <div class="flex flex-wrap items-baseline justify-between gap-2">
                                <p class="font-semibold capitalize text-slate-800">{{ $titulo }}</p>
                                @if($fecha)
                                    <time class="shrink-0 text-xs font-medium text-slate-400">{{ $fecha }}</time>
                                @endif
                            </div>
                            @if($detalle && $detalle !== $titulo)
                                <p class="mt-0.5 text-sm text-slate-600">{{ $detalle }}</p>
                            @endif
                            @if($origen)
                                <p class="mt-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ $origen }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    @endif
</div>
