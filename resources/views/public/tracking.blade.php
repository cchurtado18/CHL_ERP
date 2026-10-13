<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rastrear paquete - CH Logistics</title>
    <link rel="icon" type="image/png" href="/CH_Logistics_Logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }</style>
</head>
<body class="min-h-screen bg-slate-50 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-3xl items-center justify-between px-4 py-4">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <img src="/CH_Logistics_Logo_3d.jpg" alt="CH Logistics" class="h-11 w-11 rounded-xl object-cover">
            </a>
            <a href="{{ route('login') }}" class="text-sm font-semibold text-[#15537c] hover:underline">Iniciar sesión</a>
        </div>
    </header>

    <main class="mx-auto max-w-3xl px-4 py-10">
        <h1 class="text-3xl font-extrabold text-slate-800">Rastrear paquete</h1>
        <p class="mt-2 text-sm text-slate-500">Ingresa el código de tracking o el número de guía (WRH). No necesitas cuenta.</p>

        <form method="GET" action="{{ route('public.tracking') }}" class="mt-6 flex flex-col gap-3 sm:flex-row">
            <input type="text" name="tracking" value="{{ $code }}" required
                placeholder="Tracking o guía"
                class="flex-1 rounded-xl border border-slate-300 px-4 py-3 text-base shadow-sm focus:border-[#15537c] focus:outline-none focus:ring-2 focus:ring-[#15537c]/20">
            <button type="submit" class="rounded-xl bg-[#15537c] px-6 py-3 font-bold text-white hover:bg-[#0f3d5c]">
                Buscar
            </button>
        </form>

        @if($buscado)
            <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                @if($paquete)
                    @php
                        $titulo = $paquete->numero_guia ?: $paquete->tracking_codigo;
                        $eventosLocal = $paquete->estadoEventos->sortBy('evento_at')->values();
                    @endphp
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Resultado · CH Logistics</p>
                            <h2 class="text-xl font-extrabold text-slate-800">Guía {{ $titulo }}</h2>
                        </div>
                        <span class="rounded-full bg-[#15537c]/10 px-3 py-1 text-sm font-bold capitalize text-[#15537c]">
                            {{ str_replace('_', ' ', $paquete->estado) }}
                        </span>
                    </div>
                    <dl class="mt-5 grid gap-3 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-slate-400">Nº guía (WRH)</dt>
                            <dd class="font-semibold font-mono">{{ $paquete->numero_guia ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Tracking</dt>
                            <dd class="font-semibold font-mono text-[#15537c]">{{ $paquete->tracking_codigo ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Servicio</dt>
                            <dd class="font-semibold capitalize">{{ $paquete->servicio->tipo_servicio ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Peso</dt>
                            <dd class="font-semibold">{{ number_format($paquete->peso_lb ?? 0, 2) }} lb</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Fecha ingreso</dt>
                            <dd class="font-semibold">{{ $paquete->fecha_ingreso ? \Carbon\Carbon::parse($paquete->fecha_ingreso)->format('d/m/Y') : '—' }}</dd>
                        </div>
                    </dl>

                    <div class="mt-8 border-t border-slate-100 pt-6">
                        @include('partials.tracking-timeline', [
                            'estadoActual' => $paquete->estado,
                            'eventos' => $eventosLocal,
                            'ingresadoLocal' => true,
                        ])
                    </div>
                @elseif($remoto)
                    @php
                        $guiaRemota = $remoto['wrh'] ?? null;
                        $trackingRemoto = $remoto['tracking_number'] ?? $code;
                        $tituloRemoto = $guiaRemota ?: $trackingRemoto;
                    @endphp
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Resultado · Tracking</p>
                            <h2 class="text-xl font-extrabold text-slate-800">
                                @if($guiaRemota) Guía {{ $guiaRemota }} @else {{ $trackingRemoto }} @endif
                            </h2>
                        </div>
                        <span class="rounded-full bg-[#15537c]/10 px-3 py-1 text-sm font-bold capitalize text-[#15537c]">
                            {{ str_replace('_', ' ', $remoto['status'] ?? '') }}
                        </span>
                    </div>
                    @if(!empty($remoto['status_raw']))
                        <p class="mt-2 text-sm text-slate-500">{{ $remoto['status_raw'] }}</p>
                    @endif
                    <dl class="mt-5 grid gap-3 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-slate-400">Nº guía (WRH)</dt>
                            <dd class="font-semibold font-mono">{{ $guiaRemota ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Tracking</dt>
                            <dd class="font-semibold font-mono text-[#15537c]">{{ $trackingRemoto ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Peso</dt>
                            <dd class="font-semibold">{{ isset($remoto['weight']) ? number_format((float) $remoto['weight'], 2).' lb' : '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Fecha recepción</dt>
                            <dd class="font-semibold">
                                {{ !empty($remoto['pickup_date']) ? \Carbon\Carbon::parse($remoto['pickup_date'])->format('d/m/Y H:i') : '—' }}
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-slate-400">Descripción</dt>
                            <dd class="font-semibold">{{ $remoto['description'] ?: '—' }}</dd>
                        </div>
                    </dl>

                    <div class="mt-8 border-t border-slate-100 pt-6">
                        @include('partials.tracking-timeline', [
                            'estadoActual' => $remoto['status'] ?? 'pendiente',
                            'eventos' => $remoto['events'] ?? [],
                            'ingresadoLocal' => false,
                        ])
                    </div>
                @else
                    <p class="text-center text-slate-500">No encontramos un paquete con el código <strong>{{ $code }}</strong>.</p>
                    @if(!empty($error))
                        <p class="mt-2 text-center text-sm text-rose-600">{{ $error }}</p>
                    @else
                        <p class="mt-2 text-center text-sm text-slate-400">CargoTrack no tiene ese código exacto. Copia el tracking o la guía tal como aparece en gc.cargotrack.net.</p>
                    @endif
                @endif
            </div>
        @endif
    </main>
</body>
</html>
