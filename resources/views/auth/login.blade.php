<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar sesión — CH Logistics</title>
    <link rel="icon" type="image/png" href="/CH_Logistics_Logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --ink: #0b1f33;
            --navy: #0d3554;
            --brand: #15537c;
            --brand-600: #1a6494;
            --amber: #f0a63a;
            --amber-soft: #fdf3e2;
            --line: #e3e8ee;
            --muted: #64748b;
            --surface: #f3f5f8;
        }
        html, body { height: 100%; }
        body {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
            color: var(--ink);
            background: var(--surface);
            -webkit-font-smoothing: antialiased;
        }

        /* ---------- Panel de marca ---------- */
        .brand-pane {
            background:
                radial-gradient(640px 420px at 85% 8%, rgba(240,166,58,0.18), transparent 60%),
                radial-gradient(760px 520px at 0% 100%, rgba(26,100,148,0.45), transparent 60%),
                linear-gradient(160deg, #0e3a5c 0%, #0a2740 55%, #071a2b 100%);
        }
        .brand-pane::after {
            content: "";
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255,255,255,0.09) 1px, transparent 1px);
            background-size: 22px 22px;
            mask-image: linear-gradient(180deg, transparent 0%, #000 30%, #000 70%, transparent 100%);
            pointer-events: none;
        }

        .logistics-bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }

        /* Vista previa del portal (decorativa) */
        .preview {
            transform: perspective(1400px) rotateY(-9deg) rotateX(4deg);
            transform-origin: left center;
            animation: float 7s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { translate: 0 0; }
            50% { translate: 0 -8px; }
        }
        .preview-card {
            background: rgba(255,255,255,0.97);
            box-shadow: 0 40px 80px -30px rgba(0,0,0,0.55), 0 0 0 1px rgba(255,255,255,0.6) inset;
        }
        .chip { font-size: 10.5px; font-weight: 700; border-radius: 999px; padding: 3px 8px; letter-spacing: .01em; }
        .chip-ok { background: #e7f6ee; color: #137a48; }
        .chip-in { background: #e6f0f8; color: var(--brand); }
        .chip-due { background: var(--amber-soft); color: #9a5b06; }

        .pill {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
        }

        /* ---------- Tarjeta de acceso ---------- */
        .card {
            background: #fff;
            border: 1px solid var(--line);
            box-shadow: 0 24px 60px -36px rgba(11,31,51,0.35);
        }

        .tabs { background: var(--surface); }
        .tab {
            position: relative;
            z-index: 1;
            color: var(--muted);
            transition: color .2s;
        }
        .tab[aria-selected="true"] { color: var(--ink); }
        .tab-indicator {
            position: absolute;
            top: 4px; bottom: 4px; left: 4px;
            width: calc(50% - 4px);
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 1px 2px rgba(11,31,51,0.08), 0 4px 12px -6px rgba(11,31,51,0.18);
            transition: transform .28s cubic-bezier(.22,1,.36,1);
        }
        .tabs[data-active="track"] .tab-indicator { transform: translateX(100%); }

        .field {
            width: 100%;
            border-radius: 12px;
            border: 1px solid var(--line);
            background: #fbfcfd;
            padding: 0.85rem 1rem 0.85rem 2.75rem;
            font-size: 0.95rem;
            color: var(--ink);
            transition: border-color .18s, box-shadow .18s, background .18s;
        }
        .field::placeholder { color: #9aa7b6; }
        .field:hover { border-color: #cfd8e2; }
        .field:focus {
            outline: none;
            background: #fff;
            border-color: var(--brand);
            box-shadow: 0 0 0 4px rgba(21,83,124,0.12);
        }
        .field-error, .field-error:hover { border-color: #f4a3b1; background: #fff7f8; }
        .field-error:focus { border-color: #e11d48; box-shadow: 0 0 0 4px rgba(225,29,72,0.12); }
        .field-icon {
            position: absolute; left: 0; top: 0; bottom: 0; width: 2.75rem;
            display: flex; align-items: center; justify-content: center;
            color: #9aa7b6; pointer-events: none;
        }
        .field-wrap:focus-within .field-icon { color: var(--brand); }

        .btn {
            display: flex; width: 100%; align-items: center; justify-content: center; gap: .5rem;
            border-radius: 12px; padding: .9rem 1rem; font-size: .95rem; font-weight: 700;
            transition: filter .15s, transform .1s, box-shadow .15s;
        }
        .btn:active { transform: translateY(1px); }
        .btn:focus-visible { outline: none; box-shadow: 0 0 0 4px rgba(21,83,124,0.25); }
        .btn-primary {
            color: #fff;
            background: linear-gradient(180deg, var(--brand-600) 0%, var(--brand) 100%);
            box-shadow: 0 1px 0 rgba(255,255,255,0.18) inset, 0 12px 24px -14px rgba(21,83,124,0.8);
        }
        .btn-primary:hover { filter: brightness(1.07); }
        .btn-amber {
            color: #3d2504;
            background: linear-gradient(180deg, #f6bb5f 0%, var(--amber) 100%);
            box-shadow: 0 1px 0 rgba(255,255,255,0.3) inset, 0 12px 24px -14px rgba(240,166,58,0.9);
        }
        .btn-amber:hover { filter: brightness(1.04); }
        .btn[disabled] { opacity: .75; cursor: progress; }

        .panel[hidden] { display: none; }
        .panel { animation: fade .3s ease both; }
        @keyframes fade { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }

        .rise { animation: rise .6s cubic-bezier(.22,1,.36,1) both; }
        .rise-2 { animation: rise .6s .08s cubic-bezier(.22,1,.36,1) both; }
        @keyframes rise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }

        .spinner {
            width: 16px; height: 16px; border-radius: 999px;
            border: 2px solid rgba(255,255,255,0.4); border-top-color: #fff;
            animation: spin .7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        @media (prefers-reduced-motion: reduce) {
            .preview, .rise, .rise-2, .panel, .spinner { animation: none !important; }
            .tab-indicator { transition: none; }
            .logistics-bg .moving, .logistics-bg .pulse { display: none; }
        }
    </style>
</head>
<body class="min-h-screen">
    <div class="grid min-h-screen lg:grid-cols-[minmax(0,1.1fr)_minmax(460px,0.9fr)]">

        {{-- ================= Panel de marca (escritorio) ================= --}}
        <aside class="brand-pane relative hidden overflow-hidden text-white lg:flex lg:flex-col">
            {{-- Ilustración de fondo: rutas aéreas y puerto con contenedores --}}
            <svg class="logistics-bg" viewBox="0 0 800 1000" preserveAspectRatio="xMidYMax slice" aria-hidden="true" focusable="false">
                <defs>
                    <pattern id="corrugado" width="6" height="26" patternUnits="userSpaceOnUse">
                        <rect width="6" height="26" fill="currentColor"/>
                        <rect x="4" width="1.2" height="26" fill="#0a2740" opacity=".55"/>
                    </pattern>
                    <linearGradient id="fadeUp" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0" stop-color="#fff" stop-opacity="0"/>
                        <stop offset=".35" stop-color="#fff" stop-opacity="1"/>
                    </linearGradient>
                    <mask id="portMask">
                        <rect x="0" y="760" width="800" height="240" fill="url(#fadeUp)"/>
                    </mask>
                    <path id="rutaAerea" d="M-20,520 C180,300 470,250 830,120"/>
                    <path id="rutaAerea2" d="M-20,180 C220,230 420,90 830,300"/>
                </defs>

                {{-- Rutas --}}
                <g fill="none" stroke-linecap="round">
                    <use href="#rutaAerea" stroke="#fff" stroke-opacity=".16" stroke-width="1.5" stroke-dasharray="2 9"/>
                    <use href="#rutaAerea2" stroke="#f0a63a" stroke-opacity=".28" stroke-width="1.5" stroke-dasharray="2 9"/>
                    <path d="M-20,700 C200,640 520,700 830,600" stroke="#fff" stroke-opacity=".08" stroke-width="1.2" stroke-dasharray="1 7"/>
                </g>

                {{-- Nodos (bodegas / destinos) --}}
                <g fill="#f0a63a">
                    <g transform="translate(470 238)">
                        <circle r="3.5"/>
                        <circle r="4" fill="none" stroke="#f0a63a" stroke-width="1.2" class="pulse">
                            <animate attributeName="r" values="4;16" dur="2.8s" repeatCount="indefinite"/>
                            <animate attributeName="opacity" values=".7;0" dur="2.8s" repeatCount="indefinite"/>
                        </circle>
                    </g>
                    <g transform="translate(655 210)" fill="#fff" opacity=".55">
                        <circle r="3"/>
                        <circle r="3" fill="none" stroke="#fff" stroke-width="1" class="pulse">
                            <animate attributeName="r" values="3;13" dur="3.4s" begin="1s" repeatCount="indefinite"/>
                            <animate attributeName="opacity" values=".7;0" dur="3.4s" begin="1s" repeatCount="indefinite"/>
                        </circle>
                    </g>
                    <circle cx="160" cy="396" r="2.5" fill="#fff" opacity=".4"/>
                </g>

                {{-- Aviones en ruta --}}
                <g class="moving" fill="#fff" opacity=".55">
                    <path d="M13 0 L-3 -2.2 L-8 -10 L-11.5 -10 L-8.5 -2.2 L-13 -2.2 L-15.5 -6 L-17.5 -6 L-16.5 0 L-17.5 6 L-15.5 6 L-13 2.2 L-8.5 2.2 L-11.5 10 L-8 10 L-3 2.2 Z">
                        <animateMotion dur="22s" repeatCount="indefinite" rotate="auto"><mpath href="#rutaAerea"/></animateMotion>
                    </path>
                </g>
                <g class="moving" fill="#f0a63a" opacity=".7">
                    <path d="M10 0 L-2.5 -1.8 L-6 -8 L-9 -8 L-6.5 -1.8 L-10 -1.8 L-12 -4.8 L-13.5 -4.8 L-12.8 0 L-13.5 4.8 L-12 4.8 L-10 1.8 L-6.5 1.8 L-9 8 L-6 8 L-2.5 1.8 Z">
                        <animateMotion dur="30s" begin="-12s" repeatCount="indefinite" rotate="auto"><mpath href="#rutaAerea2"/></animateMotion>
                    </path>
                </g>

                {{-- Puerto: barco, grúa y contenedores --}}
                <g mask="url(#portMask)" color="#ffffff" opacity=".11">
                    {{-- Agua --}}
                    <rect x="0" y="952" width="800" height="48" fill="currentColor" opacity=".35"/>
                    <path d="M0 960 q20 -6 40 0 t40 0 t40 0 t40 0 t40 0 t40 0 t40 0 t40 0 t40 0 t40 0" fill="none" stroke="currentColor" stroke-width="2" opacity=".6"/>

                    {{-- Barco portacontenedores --}}
                    <path d="M20 905 L330 905 L312 952 L42 952 Z" fill="currentColor"/>
                    <rect x="270" y="868" width="40" height="37" fill="currentColor"/>
                    <rect x="282" y="850" width="18" height="18" fill="currentColor"/>
                    <g fill="url(#corrugado)">
                        <rect x="46" y="879" width="52" height="26"/><rect x="100" y="879" width="52" height="26"/>
                        <rect x="154" y="879" width="52" height="26"/><rect x="208" y="879" width="52" height="26"/>
                        <rect x="72" y="851" width="52" height="26"/><rect x="126" y="851" width="52" height="26"/>
                        <rect x="180" y="851" width="52" height="26"/>
                        <rect x="126" y="823" width="52" height="26"/>
                    </g>

                    {{-- Grúa pórtico --}}
                    <g fill="none" stroke="currentColor" stroke-width="7" stroke-linejoin="round">
                        <path d="M372 952 L372 760 M432 952 L432 760 M372 820 L432 820 M372 880 L432 880"/>
                        <path d="M372 760 L432 700" stroke-width="5"/>
                    </g>
                    <path d="M240 752 L560 752 L560 766 L240 766 Z" fill="currentColor"/>
                    <path d="M402 700 L402 752" stroke="currentColor" stroke-width="5"/>
                    <path d="M402 700 L250 752 M402 700 L555 752" stroke="currentColor" stroke-width="2"/>
                    <rect x="292" y="766" width="22" height="12" fill="currentColor"/>
                    <path d="M303 778 L303 812" stroke="currentColor" stroke-width="1.5"/>
                    <rect x="282" y="812" width="44" height="10" fill="currentColor"/>

                    {{-- Muelle y patio de contenedores --}}
                    <rect x="350" y="940" width="450" height="12" fill="currentColor"/>
                    <g fill="url(#corrugado)">
                        <rect x="470" y="912" width="60" height="26"/><rect x="532" y="912" width="60" height="26"/>
                        <rect x="594" y="912" width="60" height="26"/><rect x="656" y="912" width="60" height="26"/>
                        <rect x="718" y="912" width="60" height="26"/>
                        <rect x="500" y="884" width="60" height="26"/><rect x="562" y="884" width="60" height="26"/>
                        <rect x="624" y="884" width="60" height="26"/><rect x="686" y="884" width="60" height="26"/>
                        <rect x="594" y="856" width="60" height="26"/><rect x="656" y="856" width="60" height="26"/>
                    </g>
                </g>
                {{-- Un contenedor en color de marca --}}
                <rect x="532" y="856" width="60" height="26" rx="1" fill="#f0a63a" opacity=".32"/>
            </svg>

            <div class="relative z-10 flex flex-1 flex-col px-12 py-10 xl:px-16">
                <div class="flex items-center gap-3">
                    <img src="/CH_Logistics_Logo_3d.jpg" alt="" class="h-12 w-12 rounded-xl bg-white object-cover p-0.5 shadow-lg shadow-black/20">
                    <div class="leading-tight">
                        <p class="text-lg font-extrabold tracking-tight">CH Logistics</p>
                        <p class="text-xs font-medium text-white/55">Envíos y paquetería</p>
                    </div>
                </div>

                <div class="rise my-auto grid items-center gap-10 py-10 2xl:grid-cols-[minmax(0,1.15fr)_minmax(0,1fr)] 2xl:gap-12">
                    <div class="max-w-md">
                        <h1 class="text-[2.6rem] font-extrabold leading-[1.08] tracking-tight 2xl:text-5xl">
                            Tus envíos,<br><span class="text-[var(--amber)]">siempre a la vista.</span>
                        </h1>
                        <p class="mt-5 text-[15px] leading-relaxed text-white/70">
                            Un solo acceso para el equipo y para nuestros clientes. Consulta paquetes, facturas y el estado de cada envío desde cualquier lugar.
                        </p>
                        <ul class="mt-8 flex flex-wrap gap-2 text-[13px] font-medium text-white/85">
                            <li class="pill inline-flex items-center gap-2 rounded-full px-3.5 py-1.5">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 8 12 3 3 8v8l9 5 9-5V8Z"/><path d="m3 8 9 5 9-5M12 13v8"/></svg>
                                Paquetes
                            </li>
                            <li class="pill inline-flex items-center gap-2 rounded-full px-3.5 py-1.5">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                                Rastreo en línea
                            </li>
                        </ul>
                    </div>

                    {{-- Vista previa ilustrativa del portal --}}
                    <div class="preview hidden xl:block" aria-hidden="true">
                        <div class="preview-card w-full max-w-[340px] rounded-2xl p-4 text-[var(--ink)]">
                            <div class="flex items-center justify-between">
                                <p class="text-[13px] font-extrabold">Mis paquetes</p>
                                <span class="text-[11px] font-semibold text-slate-400">Este mes</span>
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-2">
                                <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                                    <p class="text-[10.5px] font-semibold uppercase tracking-wider text-slate-400">Recibidos</p>
                                    <p class="mt-0.5 text-xl font-extrabold text-[var(--brand)]">12</p>
                                </div>
                                <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                                    <p class="text-[10.5px] font-semibold uppercase tracking-wider text-slate-400">Entregados</p>
                                    <p class="mt-0.5 text-xl font-extrabold text-emerald-600">9</p>
                                </div>
                            </div>
                            <ul class="mt-3 divide-y divide-slate-100">
                                <li class="flex items-center justify-between py-2.5">
                                    <div>
                                        <p class="font-mono text-[12px] font-semibold">WRH-48213</p>
                                        <p class="text-[11px] text-slate-400">3.2 lb · Aéreo</p>
                                    </div>
                                    <span class="chip chip-in">Recibido</span>
                                </li>
                                <li class="flex items-center justify-between py-2.5">
                                    <div>
                                        <p class="font-mono text-[12px] font-semibold">WRH-48177</p>
                                        <p class="text-[11px] text-slate-400">11.0 lb · Marítimo</p>
                                    </div>
                                    <span class="chip chip-ok">Entregado</span>
                                </li>
                                <li class="flex items-center justify-between py-2.5">
                                    <div>
                                        <p class="font-mono text-[12px] font-semibold">WRH-48102</p>
                                        <p class="text-[11px] text-slate-400">1.4 lb · Aéreo</p>
                                    </div>
                                    <span class="chip chip-ok">Entregado</span>
                                </li>
                            </ul>
                        </div>
                        <div class="preview-card -mt-6 ml-12 w-full max-w-[300px] rounded-2xl p-4 text-[var(--ink)]">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[var(--amber-soft)] text-[#9a5b06]">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2h9l5 5v15H6z"/><path d="M14 2v6h6M9 13h7M9 17h5"/></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[12.5px] font-bold">Factura F-0231</p>
                                    <p class="text-[11px] text-slate-400">$48.50 · PDF disponible</p>
                                </div>
                                <span class="chip chip-due">Pendiente</span>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-xs font-medium text-white/35">&copy; {{ date('Y') }} CH Logistics</p>
            </div>
        </aside>

        {{-- ================= Acceso ================= --}}
        <main class="flex flex-col">
            <div class="mx-auto flex w-full max-w-[440px] flex-1 flex-col justify-center px-4 py-8 sm:px-6">

                {{-- Marca en móvil --}}
                <div class="mb-6 flex items-center gap-3 lg:hidden">
                    <img src="/CH_Logistics_Logo_3d.jpg" alt="" class="h-11 w-11 rounded-xl object-cover shadow-sm">
                    <div class="leading-tight">
                        <p class="text-base font-extrabold tracking-tight text-[var(--ink)]">CH Logistics</p>
                        <p class="text-xs font-medium text-slate-500">Envíos y paquetería</p>
                    </div>
                </div>

                <div class="rise-2">
                    <h2 id="panelTitle" class="text-2xl font-extrabold tracking-tight sm:text-[1.7rem]">Bienvenido de nuevo</h2>
                    <p id="panelSubtitle" class="mt-1.5 text-[15px] text-slate-500">Entra con tu correo para ver tus paquetes y facturas.</p>

                    <section class="card mt-6 rounded-2xl p-5 sm:p-6">
                        {{-- Pestañas --}}
                        <div class="tabs relative grid grid-cols-2 rounded-xl p-1" role="tablist" aria-label="Opciones de acceso" data-active="login">
                            <span class="tab-indicator" aria-hidden="true"></span>
                            <button type="button" role="tab" id="tab-login" aria-controls="panel-login" aria-selected="true"
                                class="tab rounded-[10px] py-2.5 text-sm font-bold" data-tab="login">Iniciar sesión</button>
                            <button type="button" role="tab" id="tab-track" aria-controls="panel-track" aria-selected="false" tabindex="-1"
                                class="tab rounded-[10px] py-2.5 text-sm font-bold" data-tab="track">Rastrear paquete</button>
                        </div>

                        {{-- Panel: iniciar sesión --}}
                        <div id="panel-login" role="tabpanel" aria-labelledby="tab-login" class="panel mt-6">
                            @if($errors->any())
                                <div class="mb-5 flex gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">
                                    <svg class="mt-0.5 shrink-0" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
                                    <span>{{ $errors->first() }}</span>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('login') }}" class="space-y-4" id="loginForm" novalidate>
                                @csrf

                                <div>
                                    <label for="email" class="mb-1.5 block text-[13px] font-semibold text-slate-700">Correo electrónico</label>
                                    <div class="field-wrap relative">
                                        <span class="field-icon" aria-hidden="true">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                                        </span>
                                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                                            autocomplete="username" inputmode="email"
                                            placeholder="tucorreo@ejemplo.com"
                                            @if($errors->has('email')) aria-invalid="true" @endif
                                            class="field {{ $errors->has('email') ? 'field-error' : '' }}">
                                    </div>
                                </div>

                                <div>
                                    <label for="password" class="mb-1.5 block text-[13px] font-semibold text-slate-700">Contraseña</label>
                                    <div class="field-wrap relative">
                                        <span class="field-icon" aria-hidden="true">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 1 1 8 0v3"/></svg>
                                        </span>
                                        <input type="password" name="password" id="password" required
                                            autocomplete="current-password"
                                            placeholder="Tu contraseña"
                                            class="field pr-12 {{ $errors->has('password') ? 'field-error' : '' }}">
                                        <button type="button" id="togglePass"
                                            class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-xl text-slate-400 transition hover:text-[var(--brand)] focus-visible:text-[var(--brand)] focus-visible:outline-none"
                                            aria-label="Mostrar contraseña" aria-pressed="false">
                                            <svg id="iconEye" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>
                                            </svg>
                                            <svg id="iconEyeOff" class="hidden" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                <path d="M3 3l18 18"/><path d="M10.6 10.6A3 3 0 0 0 12 15a3 3 0 0 0 2.4-1.2"/>
                                                <path d="M9.9 5.1A10.8 10.8 0 0 1 12 5c6.5 0 10 7 10 7a18.3 18.3 0 0 1-3.2 4.1"/>
                                                <path d="M6.1 6.1A18.5 18.5 0 0 0 2 12s3.5 7 10 7a10.6 10.6 0 0 0 3.3-.5"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <p id="capsHint" class="mt-1.5 hidden text-xs font-medium text-amber-700">Bloq Mayús está activado.</p>
                                </div>

                                <label class="flex cursor-pointer items-center gap-2.5 pt-0.5 text-sm text-slate-600">
                                    <input type="checkbox" name="remember" id="remember" value="1"
                                        class="h-4 w-4 rounded border-slate-300 text-[var(--brand)] focus:ring-[var(--brand)]">
                                    Mantener la sesión en este equipo
                                </label>

                                <button type="submit" class="btn btn-primary" id="loginBtn">
                                    <span class="spinner hidden" aria-hidden="true"></span>
                                    <span class="label">Entrar</span>
                                </button>
                            </form>

                            <p class="mt-5 text-center text-[13px] leading-relaxed text-slate-500">
                                ¿Eres cliente y aún no tienes acceso?<br class="sm:hidden">
                                Pídelo a nuestro equipo y te lo activamos.
                            </p>
                        </div>

                        {{-- Panel: rastrear --}}
                        <div id="panel-track" role="tabpanel" aria-labelledby="tab-track" class="panel mt-6" hidden>
                            <form method="GET" action="{{ route('public.tracking') }}" class="space-y-4">
                                <div>
                                    <label for="tracking" class="mb-1.5 block text-[13px] font-semibold text-slate-700">Número de tracking o guía</label>
                                    <div class="field-wrap relative">
                                        <span class="field-icon" aria-hidden="true">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 8 12 3 3 8v8l9 5 9-5V8Z"/><path d="m3 8 9 5 9-5M12 13v8"/></svg>
                                        </span>
                                        <input type="text" name="tracking" id="tracking" required
                                            autocomplete="off" autocapitalize="characters" spellcheck="false"
                                            placeholder="Ej. TBA123456789 o WRH-48213"
                                            class="field font-medium tracking-wide">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-amber">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                                    Rastrear
                                </button>
                            </form>

                            <div class="mt-5 rounded-xl bg-[var(--amber-soft)] px-4 py-3.5 text-[13px] leading-relaxed text-[#7a4a07]">
                                <p class="font-bold">¿Tu paquete aún no aparece?</p>
                                <p class="mt-1">Si después de un día sigue sin verse, contáctanos.</p>
                            </div>
                        </div>
                    </section>
                </div>

                <p class="mt-8 text-center text-xs text-slate-400 lg:hidden">&copy; {{ date('Y') }} CH Logistics</p>
            </div>
        </main>
    </div>

    <script>
        (function () {
            // ---- Pestañas ----
            const tabs = Array.from(document.querySelectorAll('[role="tab"]'));
            const bar = document.querySelector('.tabs');
            const title = document.getElementById('panelTitle');
            const subtitle = document.getElementById('panelSubtitle');
            const copy = {
                login: ['Bienvenido de nuevo', 'Entra con tu correo para ver tus paquetes y facturas.'],
                track: ['Rastrea tu paquete', 'No necesitas cuenta: escribe tu número de tracking o guía.'],
            };

            function activate(name, focus) {
                tabs.forEach(t => {
                    const on = t.dataset.tab === name;
                    t.setAttribute('aria-selected', on ? 'true' : 'false');
                    t.tabIndex = on ? 0 : -1;
                    document.getElementById(t.getAttribute('aria-controls')).hidden = !on;
                    if (on && focus) t.focus();
                });
                bar.dataset.active = name;
                [title.textContent, subtitle.textContent] = copy[name];
                const field = document.getElementById(name === 'login' ? 'email' : 'tracking');
                if (!focus) field.focus({ preventScroll: true });
                try { history.replaceState(null, '', name === 'track' ? '#rastrear' : location.pathname); } catch (e) {}
            }

            tabs.forEach((t, i) => {
                t.addEventListener('click', () => activate(t.dataset.tab, false));
                t.addEventListener('keydown', e => {
                    if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
                    e.preventDefault();
                    const next = tabs[(i + (e.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length];
                    activate(next.dataset.tab, true);
                });
            });

            const hasErrors = {{ $errors->any() ? 'true' : 'false' }};
            if (location.hash === '#rastrear' && !hasErrors) activate('track', false);

            // ---- Mostrar / ocultar contraseña ----
            const pass = document.getElementById('password');
            const toggle = document.getElementById('togglePass');
            toggle.addEventListener('click', () => {
                const show = pass.type === 'password';
                pass.type = show ? 'text' : 'password';
                toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
                toggle.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
                document.getElementById('iconEye').classList.toggle('hidden', show);
                document.getElementById('iconEyeOff').classList.toggle('hidden', !show);
            });

            // ---- Aviso de Bloq Mayús ----
            const caps = document.getElementById('capsHint');
            pass.addEventListener('keyup', e => {
                if (e.getModifierState) caps.classList.toggle('hidden', !e.getModifierState('CapsLock'));
            });
            pass.addEventListener('blur', () => caps.classList.add('hidden'));

            // ---- Estado de carga al enviar ----
            const form = document.getElementById('loginForm');
            form.addEventListener('submit', e => {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    form.reportValidity();
                    return;
                }
                const btn = document.getElementById('loginBtn');
                btn.disabled = true;
                btn.querySelector('.spinner').classList.remove('hidden');
                btn.querySelector('.label').textContent = 'Entrando…';
            });
        })();
    </script>
</body>
</html>
