<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rastrear paquete - CH Logistics</title>
    @include('partials.social-meta', [
        'title' => 'Rastrea tu paquete · CH Logistics',
        'description' => 'Escribe tu número de tracking o guía y mira el estado de tu envío. No necesitas cuenta.',
    ])
    <link rel="icon" type="image/png" href="/CH_Logistics_Logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --ink: #0b1f33;
            --brand: #15537c;
            --brand-600: #1a6494;
            --amber: #f0a63a;
            --amber-soft: #fdf3e2;
            --line: #e3e8ee;
            --surface: #f3f5f8;
        }
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; color: var(--ink); background: var(--surface); -webkit-font-smoothing: antialiased; }

        .hero {
            background:
                radial-gradient(620px 380px at 90% 0%, rgba(240,166,58,0.18), transparent 60%),
                radial-gradient(700px 480px at 0% 100%, rgba(26,100,148,0.45), transparent 60%),
                linear-gradient(160deg, #0e3a5c 0%, #0a2740 60%, #071a2b 100%);
        }
        .hero::after {
            content: ""; position: absolute; inset: 0; pointer-events: none;
            background-image: radial-gradient(rgba(255,255,255,0.08) 1px, transparent 1px);
            background-size: 22px 22px;
            mask-image: linear-gradient(180deg, #000 0%, #000 60%, transparent 100%);
        }
        .hero-bg { position: absolute; inset: 0; width: 100%; height: 100%; pointer-events: none; }

        .search {
            background: #fff;
            box-shadow: 0 24px 50px -24px rgba(0,0,0,0.55);
        }
        .search input:focus { outline: none; }
        .search:focus-within { box-shadow: 0 0 0 4px rgba(240,166,58,0.35), 0 24px 50px -24px rgba(0,0,0,0.55); }
        .btn-amber {
            color: #3d2504;
            background: linear-gradient(180deg, #f6bb5f 0%, var(--amber) 100%);
            box-shadow: 0 1px 0 rgba(255,255,255,0.3) inset;
            transition: filter .15s, transform .1s;
        }
        .btn-amber:hover { filter: brightness(1.04); }
        .btn-amber:active { transform: translateY(1px); }
        .btn-amber[disabled] { cursor: progress; filter: saturate(.85); }

        .card { background: #fff; border: 1px solid var(--line); box-shadow: 0 24px 60px -40px rgba(11,31,51,0.35); }

        /* ---------- Buscando: radar con paquete ---------- */
        .radar { position: relative; width: 132px; height: 132px; margin: 0 auto; }
        .radar .ring {
            position: absolute; inset: 0; border-radius: 999px;
            border: 2px solid rgba(21,83,124,0.45);
            animation: ping 2.4s cubic-bezier(0,0,.2,1) infinite;
        }
        .radar .ring:nth-child(2) { animation-delay: .8s; }
        .radar .ring:nth-child(3) { animation-delay: 1.6s; }
        @keyframes ping { 0% { transform: scale(.35); opacity: .9; } 100% { transform: scale(1); opacity: 0; } }
        .radar .core {
            position: absolute; inset: 38px; border-radius: 999px;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(180deg, var(--brand-600), var(--brand));
            color: #fff; font-size: 22px;
            box-shadow: 0 12px 30px -10px rgba(21,83,124,0.8);
            animation: bob 1.6s ease-in-out infinite;
        }
        @keyframes bob { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-4px); } }
        .radar .orbit { position: absolute; inset: 14px; animation: spin 2.8s linear infinite; }
        .radar .orbit::before {
            content: ""; position: absolute; top: -5px; left: 50%; width: 11px; height: 11px; margin-left: -5.5px;
            border-radius: 999px; background: var(--amber); box-shadow: 0 0 0 4px rgba(240,166,58,0.25);
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .dots span { display: inline-block; width: 6px; height: 6px; margin: 0 2px; border-radius: 999px; background: var(--amber); animation: dot 1.2s infinite ease-in-out; }
        .dots span:nth-child(2) { animation-delay: .15s; }
        .dots span:nth-child(3) { animation-delay: .3s; }
        @keyframes dot { 0%, 80%, 100% { transform: scale(.5); opacity: .4; } 40% { transform: scale(1); opacity: 1; } }
        .loader-msg { transition: opacity .3s; }

        .spinner { width: 16px; height: 16px; border-radius: 999px; border: 2px solid rgba(61,37,4,0.25); border-top-color: #3d2504; animation: spin .7s linear infinite; }

        .reveal { animation: reveal .5s cubic-bezier(.22,1,.36,1) both; }
        @keyframes reveal { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }

        .btn-wa { background: linear-gradient(180deg, #2fd568 0%, #1fb855 100%); box-shadow: 0 10px 24px -10px rgba(31,184,85,0.7); transition: filter .15s, transform .1s; }
        .btn-wa:hover { filter: brightness(1.05); }
        .wa-fab { position: fixed; right: 18px; bottom: 18px; z-index: 50; }
        .wa-fab .pulse { position: absolute; inset: 0; border-radius: 999px; background: #25d366; opacity: .5; animation: wapulse 2.2s ease-out infinite; }
        @keyframes wapulse { 0% { transform: scale(1); opacity: .45; } 100% { transform: scale(1.6); opacity: 0; } }
        @media (prefers-reduced-motion: reduce) { .wa-fab .pulse { animation: none; display: none; } }
        @media (prefers-reduced-motion: reduce) {
            .radar .ring, .radar .core, .radar .orbit, .dots span, .reveal, .spinner, .hero-bg .moving { animation: none !important; }
            .hero-bg .moving { display: none; }
        }
    </style>
</head>
<body class="min-h-screen">
@php
    // Traduce los textos en inglés que devuelve CargoTrack.
    $traducir = function (?string $texto): ?string {
        if ($texto === null || trim($texto) === '') {
            return $texto;
        }
        $mapa = [
            '/^\s*received\s*$/i' => 'Recibido en bodega',
            '/^\s*(in\s*transit|shipped|dispatched)\s*$/i' => 'En tránsito',
            '/^\s*delivered\s*$/i' => 'Entregado',
            '/^\s*(arrived|at destination)\s*$/i' => 'Llegó a destino',
            '/^\s*(customs|in customs)\s*$/i' => 'En aduana',
            '/^\s*pending\s*$/i' => 'Pendiente',
        ];
        foreach ($mapa as $patron => $es) {
            if (preg_match($patron, $texto)) {
                return $es;
            }
        }

        return preg_replace(['/\bpiece\(s\)/i', '/\bpieces\b/i', '/\bpiece\b/i'], ['pieza(s)', 'piezas', 'pieza'], $texto);
    };

    // Fechas tipo "11/20/2026 9:41:12 AM" -> "20/11/2026 09:41"
    $fecha = function ($valor): ?string {
        if (! $valor) {
            return null;
        }
        foreach (['n/j/Y g:i:s A', 'm/d/Y g:i:s A', 'n/j/Y g:i A', 'Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d'] as $f) {
            try {
                $d = \Carbon\Carbon::createFromFormat($f, trim((string) $valor));
                if ($d) {
                    return $d->format(str_contains($f, ':') ? 'd/m/Y H:i' : 'd/m/Y');
                }
            } catch (\Throwable $e) {
            }
        }
        try {
            return \Carbon\Carbon::parse($valor)->format('d/m/Y H:i');
        } catch (\Throwable $e) {
            return (string) $valor;
        }
    };

    $estadoInfo = [
        'pendiente' => ['Pendiente', 'Estamos esperando información de este envío.', 'fa-hourglass-half'],
        'recibido' => ['Recibido', 'Tu paquete ya llegó a nuestra bodega de origen.', 'fa-box'],
        'en_bodega' => ['Recibido', 'Tu paquete ya llegó a nuestra bodega de origen.', 'fa-box'],
        'en_transito' => ['En tránsito', 'Tu paquete va en camino hacia Nicaragua.', 'fa-plane'],
        'en_camino' => ['En tránsito', 'Tu paquete va en camino hacia Nicaragua.', 'fa-plane'],
        'en_aduana' => ['En destino', 'Tu paquete llegó al país y se está procesando.', 'fa-flag'],
        'listo_entrega' => ['Listo para entregar', 'Ya puedes coordinar la entrega o retiro.', 'fa-clipboard-check'],
        'listo_para_entrega' => ['Listo para entregar', 'Ya puedes coordinar la entrega o retiro.', 'fa-clipboard-check'],
        'en_oficina' => ['En oficina', 'Tu paquete ya está en nuestra oficina de CH Logistics.', 'fa-store'],
        'entregado' => ['Entregado', 'Tu paquete fue entregado.', 'fa-circle-check'],
    ];

    // WhatsApp de atención: +505 8928-8565
    $wa = fn (string $msg = '') => 'https://wa.me/50589288565'.($msg !== '' ? '?text='.rawurlencode($msg) : '');
    $waMsg = filled($code ?? null)
        ? "Hola CH Logistics, quiero consultar sobre mi paquete con tracking {$code}."
        : 'Hola CH Logistics, quiero consultar sobre mi paquete.';
@endphp

    {{-- ================= Encabezado + buscador ================= --}}
    <header class="hero relative overflow-hidden text-white">
        <svg class="hero-bg" viewBox="0 0 1200 420" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">
            <defs>
                <path id="r1" d="M-40,330 C260,120 640,90 1240,60"/>
                <path id="r2" d="M-40,120 C300,170 700,40 1240,220"/>
            </defs>
            <g fill="none" stroke-linecap="round">
                <use href="#r1" stroke="#fff" stroke-opacity=".14" stroke-width="1.5" stroke-dasharray="2 9"/>
                <use href="#r2" stroke="#f0a63a" stroke-opacity=".26" stroke-width="1.5" stroke-dasharray="2 9"/>
            </g>
            <circle cx="820" cy="118" r="3.5" fill="#f0a63a"/>
            <circle cx="360" cy="150" r="3" fill="#fff" opacity=".45"/>
            <g class="moving" fill="#fff" opacity=".5">
                <path d="M13 0 L-3 -2.2 L-8 -10 L-11.5 -10 L-8.5 -2.2 L-13 -2.2 L-15.5 -6 L-17.5 -6 L-16.5 0 L-17.5 6 L-15.5 6 L-13 2.2 L-8.5 2.2 L-11.5 10 L-8 10 L-3 2.2 Z">
                    <animateMotion dur="26s" repeatCount="indefinite" rotate="auto"><mpath href="#r1"/></animateMotion>
                </path>
            </g>
            <g class="moving" fill="#f0a63a" opacity=".65">
                <path d="M10 0 L-2.5 -1.8 L-6 -8 L-9 -8 L-6.5 -1.8 L-10 -1.8 L-12 -4.8 L-13.5 -4.8 L-12.8 0 L-13.5 4.8 L-12 4.8 L-10 1.8 L-6.5 1.8 L-9 8 L-6 8 L-2.5 1.8 Z">
                    <animateMotion dur="34s" begin="-14s" repeatCount="indefinite" rotate="auto"><mpath href="#r2"/></animateMotion>
                </path>
            </g>
        </svg>

        <div class="relative z-10 mx-auto max-w-3xl px-4 sm:px-6">
            <nav class="flex items-center justify-between py-5">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <img src="/CH_Logistics_Logo_3d.jpg" alt="" class="h-10 w-10 rounded-xl bg-white object-cover p-0.5 shadow-lg shadow-black/20">
                    <span class="leading-tight">
                        <span class="block text-base font-extrabold tracking-tight">CH Logistics</span>
                        <span class="block text-xs font-medium text-white/55">Envíos y paquetería</span>
                    </span>
                </a>
                <a href="{{ route('login') }}" class="rounded-full border border-white/20 px-4 py-2 text-sm font-semibold text-white/90 transition hover:bg-white/10">
                    Iniciar sesión
                </a>
            </nav>

            <div class="pb-16 pt-8 sm:pb-20 sm:pt-10">
                <h1 class="text-3xl font-extrabold tracking-tight sm:text-[2.6rem] sm:leading-[1.1]">
                    Rastrea tu <span class="text-[var(--amber)]">paquete</span>
                </h1>
                <p class="mt-3 max-w-xl text-[15px] leading-relaxed text-white/70">
                    Escribe tu número de tracking o tu guía (WRH). No necesitas cuenta.
                </p>

                <form method="GET" action="{{ route('public.tracking') }}" id="trackForm"
                    class="search mt-7 flex items-center gap-2 rounded-2xl p-2 transition-shadow" role="search">
                    <label for="tracking" class="sr-only">Número de tracking o guía</label>
                    <span class="pl-3 text-slate-400" aria-hidden="true"><i class="fas fa-box"></i></span>
                    <input type="text" name="tracking" id="tracking" value="{{ $code }}" required
                        @unless($buscado) autofocus @endunless
                        autocomplete="off" autocapitalize="characters" spellcheck="false"
                        placeholder="Ej. TBA123456789 o WRH-48213"
                        class="min-w-0 flex-1 bg-transparent px-2 py-3 text-base font-semibold tracking-wide text-[var(--ink)] placeholder:font-medium placeholder:tracking-normal placeholder:text-slate-400">
                    <button type="submit" id="trackBtn" class="btn-amber inline-flex shrink-0 items-center gap-2 rounded-xl px-4 py-3 text-sm font-extrabold sm:px-6">
                        <span class="spinner hidden" aria-hidden="true"></span>
                        <i class="fas fa-magnifying-glass icon" aria-hidden="true"></i>
                        <span class="label">Rastrear</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- ================= Resultado ================= --}}
    <main class="relative z-10 mx-auto -mt-8 max-w-3xl px-4 pb-16 sm:px-6">

        {{-- Animación de búsqueda (se muestra al enviar y mientras se consulta en el navegador) --}}
        <section id="searching" class="card hidden rounded-3xl px-6 py-12 text-center" aria-live="polite">
            <div class="radar" aria-hidden="true">
                <span class="ring"></span><span class="ring"></span><span class="ring"></span>
                <span class="orbit"></span>
                <span class="core"><i class="fas fa-box"></i></span>
            </div>
            <p class="mt-7 text-lg font-extrabold">Buscando tu paquete <span class="dots" aria-hidden="true"><span></span><span></span><span></span></span></p>
            <p class="loader-msg mt-1.5 text-sm text-slate-500" id="loaderMsg">Consultando la bodega…</p>
            <p class="mt-4 font-mono text-xs font-semibold text-slate-400" id="loaderCode"></p>
        </section>

        <div id="results">
        @if($buscado)
            @if($paquete || $remoto)
                @php
                    if ($paquete) {
                        $guia = $paquete->numero_guia;
                        $trackingCod = $paquete->tracking_codigo;
                        $estadoKey = $paquete->estadoCliente();
                        $peso = \App\Models\Inventario::formatPeso($paquete->peso_lb).' lb';
                        $fechaRecepcion = $paquete->fecha_ingreso ? \Carbon\Carbon::parse($paquete->fecha_ingreso)->format('d/m/Y') : null;
                        $descripcion = null;
                        $servicio = $paquete->servicio->tipo_servicio ?? null;
                        $estadoRaw = null;
                        $eventos = $paquete->estadoEventos->sortBy('evento_at')->values();
                        $ingresadoLocal = true;
                    } else {
                        $guia = $remoto['wrh'] ?? null;
                        $trackingCod = $remoto['tracking_number'] ?? $code;
                        $estadoKey = strtolower((string) ($remoto['status'] ?? 'pendiente'));
                        $peso = isset($remoto['weight']) ? number_format((float) $remoto['weight'], 2).' lb' : null;
                        $fechaRecepcion = $fecha($remoto['pickup_date'] ?? null);
                        $descripcion = $traducir($remoto['description'] ?? null);
                        $servicio = null;
                        $estadoRaw = $traducir($remoto['status_raw'] ?? null);
                        $eventos = collect($remoto['events'] ?? [])->map(function ($e) use ($traducir, $fecha) {
                            $e = (array) $e;
                            if (isset($e['status'])) $e['status'] = $traducir($e['status']);
                            if (isset($e['description'])) $e['description'] = $traducir($e['description']);
                            if (isset($e['date'])) $e['date'] = $fecha($e['date']);
                            return $e;
                        })->all();
                        $ingresadoLocal = false;
                    }
                    [$estadoLabel, $estadoTexto, $estadoIcono] = $estadoInfo[$estadoKey] ?? [ucfirst(str_replace('_', ' ', $estadoKey)), null, 'fa-box'];
                    $detalles = array_filter([
                        ['fa-barcode', 'Tracking', $trackingCod, true],
                        ['fa-warehouse', 'Guía (WRH)', $guia, true],
                        ['fa-weight-hanging', 'Peso', $peso, false],
                        ['fa-calendar-check', 'Fecha de recepción', $fechaRecepcion, false],
                        ['fa-boxes-stacked', 'Contenido', $descripcion, false],
                        ['fa-truck-fast', 'Servicio', $servicio ? ucfirst($servicio) : null, false],
                    ], fn ($d) => filled($d[2]));
                @endphp

                <article class="card reveal overflow-hidden rounded-3xl">
                    {{-- Estado principal --}}
                    <div class="flex flex-col gap-4 border-b border-slate-100 p-6 sm:flex-row sm:items-center sm:justify-between sm:p-7">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-[var(--amber-soft)] text-xl text-[#b26a06]">
                                <i class="fas {{ $estadoIcono }}"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Estado actual</p>
                                <h2 class="text-2xl font-extrabold tracking-tight">{{ $estadoLabel }}</h2>
                                @if($estadoTexto)
                                    <p class="mt-0.5 text-sm text-slate-500">{{ $estadoTexto }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="sm:text-right">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">{{ $guia ? 'Guía' : 'Tracking' }}</p>
                            <button type="button" class="copy group mt-0.5 inline-flex items-center gap-2 font-mono text-lg font-bold text-[var(--brand)]"
                                data-copy="{{ $guia ?: $trackingCod }}" title="Copiar">
                                {{ $guia ?: $trackingCod }}
                                <i class="far fa-copy text-sm text-slate-300 transition group-hover:text-[var(--brand)]" aria-hidden="true"></i>
                                <span class="sr-only">Copiar</span>
                            </button>
                        </div>
                    </div>

                    {{-- Avance --}}
                    <div class="border-b border-slate-100 px-4 py-6 sm:px-7">
                        @include('partials.tracking-timeline', [
                            'estadoActual' => $estadoKey,
                            'eventos' => $eventos,
                            'ingresadoLocal' => $ingresadoLocal,
                        ])
                    </div>

                    {{-- Detalles --}}
                    <dl class="grid gap-px bg-slate-100 sm:grid-cols-2">
                        @foreach($detalles as [$icono, $etiqueta, $valor, $mono])
                            <div class="flex items-start gap-3 bg-white px-6 py-4 sm:px-7">
                                <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-sm text-slate-500"><i class="fas {{ $icono }}"></i></span>
                                <div class="min-w-0">
                                    <dt class="text-xs font-semibold text-slate-400">{{ $etiqueta }}</dt>
                                    <dd class="mt-0.5 break-words font-bold {{ $mono ? 'font-mono text-[15px]' : '' }}">{{ $valor }}</dd>
                                </div>
                            </div>
                        @endforeach
                        @if(count($detalles) % 2 === 1)
                            <div class="hidden bg-white sm:block"></div>
                        @endif
                    </dl>

                </article>

                <p class="mt-5 text-center text-sm text-slate-500">
                    ¿Tienes dudas sobre tu envío? <a href="{{ route('login') }}" class="font-semibold text-[var(--brand)] hover:underline">Entra a tu cuenta</a> o <a href="{{ $wa($waMsg) }}" target="_blank" rel="noopener" class="font-semibold text-[#128C7E] hover:underline">escríbenos por WhatsApp</a>.
                </p>

            @elseif(!empty($usarNavegador))
                {{-- CargoTrack no respondió al servidor: se consulta desde el navegador --}}
                <div id="trackingExtract"></div>
    <template id="tplNotFound">
        <div class="card reveal rounded-3xl px-6 py-10 text-center sm:px-10">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-2xl text-slate-400"><i class="fas fa-box-open"></i></div>
            <h2 class="mt-5 text-xl font-extrabold">No encontramos ese código</h2>
            <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-slate-500">Buscamos <span class="font-mono font-bold text-slate-700">__CODE__</span> y aún no aparece. Revisa que esté escrito igual que en tu compra.</p>
            <div class="mx-auto mt-6 max-w-md rounded-2xl bg-[var(--amber-soft)] px-5 py-4 text-left text-[13px] leading-relaxed text-[#7a4a07]">
                <p class="font-bold">¿Tu paquete aún no aparece?</p>
                <p class="mt-1">Si después de un día sigue sin verse, <a href="{{ $wa($waMsg) }}" target="_blank" rel="noopener" class="font-bold underline decoration-2 underline-offset-2 hover:text-[#128C7E]">contáctanos por WhatsApp</a>.</p>
            </div>
            <a href="{{ $wa($waMsg) }}" target="_blank" rel="noopener" class="btn-wa mt-6 inline-flex items-center gap-2 rounded-xl px-5 py-3 text-sm font-extrabold text-white">
                <i class="fab fa-whatsapp text-lg" aria-hidden="true"></i> Escríbenos por WhatsApp
            </a>
        </div>
    </template>
                <script>
                (function () {
                    const code = @json($code);
                    const trackUrl = @json($cargotrackUrl) + '?track=' + encodeURIComponent(code) + '&action2=process';
                    const parseUrl = @json(route('public.tracking.parse'));
                    const csrf = document.querySelector('meta[name="csrf-token"]').content;
                    const box = document.getElementById('trackingExtract');
                    const proxies = [
                        'https://r.jina.ai/' + trackUrl,
                        'https://api.allorigins.win/raw?url=' + encodeURIComponent(trackUrl)
                    ];

                    window.CHL_TRACK.showSearching(code);

                    const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
                    const tr = t => {
                        if (!t) return t;
                        const s = String(t).trim();
                        const map = [[/^received$/i,'Recibido en bodega'],[/^(in\s*transit|shipped|dispatched)$/i,'En tránsito'],[/^delivered$/i,'Entregado'],[/^(arrived|at destination)$/i,'Llegó a destino'],[/^(customs|in customs)$/i,'En aduana']];
                        for (const [re, es] of map) if (re.test(s)) return es;
                        return s.replace(/\bpiece\(s\)/ig, 'pieza(s)').replace(/\bpieces\b/ig, 'piezas').replace(/\bpiece\b/ig, 'pieza');
                    };
                    const fmt = v => {
                        if (!v) return '';
                        const d = new Date(String(v).replace(' ', 'T'));
                        return isNaN(d) ? String(v) : d.toLocaleString('es-NI', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                    };
                    const estados = {
                        recibido: ['Recibido', 'Tu paquete ya llegó a nuestra bodega de origen.', 'fa-box'],
                        en_transito: ['En tránsito', 'Tu paquete va en camino hacia Nicaragua.', 'fa-plane'],
                        en_aduana: ['En destino', 'Tu paquete llegó al país y se está procesando.', 'fa-flag'],
                        entregado: ['Entregado', 'Tu paquete fue entregado.', 'fa-circle-check'],
                        pendiente: ['Pendiente', 'Estamos esperando información de este envío.', 'fa-hourglass-half'],
                    };

                    function render(d) {
                        const key = String(d.status || 'pendiente').toLowerCase();
                        const [label, texto, icono] = estados[key] || [key.replace(/_/g, ' '), '', 'fa-box'];
                        const guia = d.wrh || '';
                        const track = d.tracking_number || code;
                        const filas = [
                            ['fa-barcode', 'Tracking', track, true],
                            ['fa-warehouse', 'Guía (WRH)', guia, true],
                            ['fa-weight-hanging', 'Peso', d.weight != null ? Number(d.weight).toFixed(2) + ' lb' : '', false],
                            ['fa-calendar-check', 'Fecha de recepción', fmt(d.pickup_date), false],
                            ['fa-boxes-stacked', 'Contenido', tr(d.description), false],
                        ].filter(f => f[2]);
                        const events = Array.isArray(d.events) ? d.events : [];
                        const eventos = events.map((ev, i) =>
                            '<li class="relative flex gap-4 pb-6 last:pb-0">' +
                                '<span class="absolute -left-[5px] top-1.5 h-2.5 w-2.5 rounded-full ' + (i === events.length - 1 ? 'bg-[#F0A63A] ring-4 ring-[#F0A63A]/20' : 'bg-[#15537c]') + '"></span>' +
                                '<div class="ml-5 flex flex-1 flex-wrap items-baseline justify-between gap-2"><p class="font-semibold text-slate-800">' + esc(tr(ev.status || ev.description || 'Evento')) + '</p>' +
                                (ev.date ? '<time class="text-xs font-medium text-slate-400">' + esc(fmt(ev.date)) + '</time>' : '') + '</div></li>'
                        ).join('');

                        box.innerHTML =
                            '<article class="card reveal overflow-hidden rounded-3xl">' +
                                '<div class="flex flex-col gap-4 border-b border-slate-100 p-6 sm:flex-row sm:items-center sm:justify-between sm:p-7">' +
                                    '<div class="flex items-center gap-4"><div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-[var(--amber-soft)] text-xl text-[#b26a06]"><i class="fas ' + icono + '"></i></div>' +
                                    '<div><p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Estado actual</p><h2 class="text-2xl font-extrabold tracking-tight">' + esc(label) + '</h2>' +
                                    (texto ? '<p class="mt-0.5 text-sm text-slate-500">' + esc(texto) + '</p>' : '') + '</div></div>' +
                                    '<div class="sm:text-right"><p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">' + (guia ? 'Guía' : 'Tracking') + '</p><p class="mt-0.5 font-mono text-lg font-bold text-[var(--brand)]">' + esc(guia || track) + '</p></div>' +
                                '</div>' +
                                '<dl class="grid gap-px bg-slate-100 sm:grid-cols-2">' +
                                    filas.map(f => '<div class="flex items-start gap-3 bg-white px-6 py-4 sm:px-7"><span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-sm text-slate-500"><i class="fas ' + f[0] + '"></i></span><div class="min-w-0"><dt class="text-xs font-semibold text-slate-400">' + f[1] + '</dt><dd class="mt-0.5 break-words font-bold ' + (f[3] ? 'font-mono text-[15px]' : '') + '">' + esc(f[2]) + '</dd></div></div>').join('') +
                                    (filas.length % 2 ? '<div class="hidden bg-white sm:block"></div>' : '') +
                                '</dl>' +
                                (eventos ? '<div class="border-t border-slate-100 px-6 py-6 sm:px-7"><p class="mb-4 text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Historial</p><ol class="relative border-l-2 border-[#15537c]/20">' + eventos + '</ol></div>' : '') +
                            '</article>';
                    }

                    function notFound() {
                        box.innerHTML = document.getElementById('tplNotFound').innerHTML.replace(/__CODE__/g, esc(code));
                    }

                    async function run() {
                        for (const url of proxies) {
                            try {
                                const page = await fetch(url);
                                if (!page.ok) continue;
                                const html = await page.text();
                                if (html.indexOf('Searching for') === -1) continue;
                                const parsed = await fetch(parseUrl, {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                                    body: JSON.stringify({ code: code, html: html })
                                });
                                const data = await parsed.json();
                                if (data.ok && data.remoto) {
                                    window.CHL_TRACK.hideSearching();
                                    render(data.remoto);
                                    return;
                                }
                            } catch (e) {}
                        }
                        window.CHL_TRACK.hideSearching();
                        notFound();
                    }
                    run();
                })();
                </script>
            @else
                <div class="card reveal rounded-3xl px-6 py-10 text-center sm:px-10">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-2xl text-slate-400">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <h2 class="mt-5 text-xl font-extrabold">No encontramos ese código</h2>
                    <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-slate-500">
                        Buscamos <span class="font-mono font-bold text-slate-700">{{ $code }}</span> y aún no aparece. Revisa que esté escrito igual que en tu compra.
                    </p>
                    @if($error && ! str_contains(strtolower($error), 'no tiene'))
                        <p class="mx-auto mt-3 max-w-md text-xs text-slate-400">{{ $error }}</p>
                    @endif
                    <div class="mx-auto mt-6 max-w-md rounded-2xl bg-[var(--amber-soft)] px-5 py-4 text-left text-[13px] leading-relaxed text-[#7a4a07]">
                        <p class="font-bold">¿Tu paquete aún no aparece?</p>
                        <p class="mt-1">Si después de un día sigue sin verse, <a href="{{ $wa($waMsg) }}" target="_blank" rel="noopener" class="font-bold underline decoration-2 underline-offset-2 hover:text-[#128C7E]">contáctanos por WhatsApp</a>.</p>
                    </div>
                    <a href="{{ $wa($waMsg) }}" target="_blank" rel="noopener" class="btn-wa mt-6 inline-flex items-center gap-2 rounded-xl px-5 py-3 text-sm font-extrabold text-white">
                        <i class="fab fa-whatsapp text-lg" aria-hidden="true"></i> Escríbenos por WhatsApp
                    </a>
                    <br>
                    <button type="button" onclick="document.getElementById('tracking').select()" class="mt-4 text-sm font-bold text-[var(--brand)] hover:underline">
                        Intentar con otro código
                    </button>
                </div>
            @endif
        @else
            {{-- Sin búsqueda: ayuda --}}
            <section class="grid gap-4 sm:grid-cols-3">
                <div class="card rounded-2xl p-5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#15537c]/10 text-[var(--brand)]"><i class="fas fa-barcode"></i></span>
                    <p class="mt-4 font-bold">Tracking de tu tienda</p>
                    <p class="mt-1 text-sm leading-relaxed text-slate-500">El número que te dio Amazon, Shein u otra tienda al enviar tu compra.</p>
                </div>
                <div class="card rounded-2xl p-5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#15537c]/10 text-[var(--brand)]"><i class="fas fa-warehouse"></i></span>
                    <p class="mt-4 font-bold">Guía de bodega (WRH)</p>
                    <p class="mt-1 text-sm leading-relaxed text-slate-500">El número que asignamos cuando tu paquete llega a nuestra bodega.</p>
                </div>
                <div class="card rounded-2xl p-5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--amber-soft)] text-[#b26a06]"><i class="fas fa-headset"></i></span>
                    <p class="mt-4 font-bold">¿Aún no aparece?</p>
                    <p class="mt-1 text-sm leading-relaxed text-slate-500">Si después de un día sigue sin verse, <a href="{{ $wa($waMsg) }}" target="_blank" rel="noopener" class="font-semibold text-[#128C7E] hover:underline">escríbenos por WhatsApp</a>.</p>
                </div>
            </section>
        @endif
        </div>
    </main>


    <script>
    (function () {
        const form = document.getElementById('trackForm');
        const input = document.getElementById('tracking');
        const btn = document.getElementById('trackBtn');
        const searching = document.getElementById('searching');
        const results = document.getElementById('results');
        const msg = document.getElementById('loaderMsg');
        const mensajes = ['Consultando la bodega…', 'Revisando el estado del envío…', 'Buscando en el sistema de carga…', 'Ya casi…'];
        let timer = null;

        function showSearching(code) {
            results.classList.add('hidden');
            searching.classList.remove('hidden');
            document.getElementById('loaderCode').textContent = code || '';
            let i = 0;
            msg.textContent = mensajes[0];
            clearInterval(timer);
            timer = setInterval(() => {
                i = Math.min(i + 1, mensajes.length - 1);
                msg.style.opacity = 0;
                setTimeout(() => { msg.textContent = mensajes[i]; msg.style.opacity = 1; }, 250);
            }, 2200);
            btn.disabled = true;
            btn.querySelector('.spinner').classList.remove('hidden');
            btn.querySelector('.icon').classList.add('hidden');
            btn.querySelector('.label').textContent = 'Buscando…';
        }

        function hideSearching() {
            clearInterval(timer);
            searching.classList.add('hidden');
            results.classList.remove('hidden');
            btn.disabled = false;
            btn.querySelector('.spinner').classList.add('hidden');
            btn.querySelector('.icon').classList.remove('hidden');
            btn.querySelector('.label').textContent = 'Rastrear';
        }

        window.CHL_TRACK = { showSearching, hideSearching };

        form.addEventListener('submit', function () {
            input.value = input.value.trim();
            if (!input.value) return;
            showSearching(input.value);
        });

        // Al volver con el botón "atrás", no dejar la animación pegada.
        window.addEventListener('pageshow', e => { if (e.persisted) hideSearching(); });

        // Copiar código
        document.querySelectorAll('.copy').forEach(b => b.addEventListener('click', () => {
            navigator.clipboard?.writeText(b.dataset.copy).then(() => {
                const i = b.querySelector('i');
                i.className = 'fas fa-check text-sm text-emerald-500';
                setTimeout(() => { i.className = 'far fa-copy text-sm text-slate-300 transition group-hover:text-[var(--brand)]'; }, 1500);
            });
        }));
    })();
    </script>
    {{-- Botón flotante de WhatsApp --}}
    <a href="{{ $wa($waMsg) }}" target="_blank" rel="noopener" class="wa-fab group flex items-center" aria-label="Escríbenos por WhatsApp al +505 8928-8565">
        <span class="mr-3 hidden rounded-full bg-white px-4 py-2 text-sm font-bold text-[var(--ink)] shadow-lg shadow-black/10 sm:block">¿Dudas? Escríbenos</span>
        <span class="relative flex h-14 w-14 items-center justify-center rounded-full bg-[#25d366] text-3xl text-white shadow-xl shadow-[#25d366]/40 transition group-hover:scale-105">
            <span class="pulse" aria-hidden="true"></span>
            <i class="fab fa-whatsapp relative" aria-hidden="true"></i>
        </span>
    </a>
</body>
</html>
