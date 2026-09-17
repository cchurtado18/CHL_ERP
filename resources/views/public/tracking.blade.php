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
                <img src="/CH_Logistics_Logo_hd.png" alt="CH Logistics" class="h-10 w-auto object-contain">
            </a>
            <a href="{{ route('login') }}" class="text-sm font-semibold text-[#15537c] hover:underline">Iniciar sesión</a>
        </div>
    </header>

    <main class="mx-auto max-w-3xl px-4 py-10">
        <h1 class="text-3xl font-extrabold text-slate-800">Rastrear paquete</h1>
        <p class="mt-2 text-sm text-slate-500">Ingresa el código de tracking o el número de guía. No necesitas cuenta.</p>

        <form method="GET" action="{{ route('public.tracking') }}" class="mt-6 flex flex-col gap-3 sm:flex-row">
            <input type="text" name="code" value="{{ $code }}" required
                placeholder="Ej: 000123 o tracking"
                class="flex-1 rounded-xl border border-slate-300 px-4 py-3 text-base shadow-sm focus:border-[#15537c] focus:outline-none focus:ring-2 focus:ring-[#15537c]/20">
            <button type="submit" class="rounded-xl bg-[#15537c] px-6 py-3 font-bold text-white hover:bg-[#0f3d5c]">
                Buscar
            </button>
        </form>

        @if($buscado)
            <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                @if($paquete)
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Resultado</p>
                            <h2 class="text-xl font-extrabold text-slate-800">{{ $paquete->tracking_codigo ?: $paquete->numero_guia }}</h2>
                        </div>
                        <span class="rounded-full bg-[#15537c]/10 px-3 py-1 text-sm font-bold capitalize text-[#15537c]">
                            {{ str_replace('_', ' ', $paquete->estado) }}
                        </span>
                    </div>
                    <dl class="mt-5 grid gap-3 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-slate-400">Servicio</dt>
                            <dd class="font-semibold capitalize">{{ $paquete->servicio->tipo_servicio ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Fecha ingreso</dt>
                            <dd class="font-semibold">{{ \Carbon\Carbon::parse($paquete->fecha_ingreso)->format('d/m/Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Peso</dt>
                            <dd class="font-semibold">{{ number_format($paquete->peso_lb ?? 0, 2) }} lb</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Nº guía</dt>
                            <dd class="font-semibold">{{ $paquete->numero_guia ?: '—' }}</dd>
                        </div>
                    </dl>

                    @if($paquete->estadoEventos->isNotEmpty())
                        <h3 class="mt-6 mb-3 font-bold text-slate-800">Historial</h3>
                        <ol class="space-y-3 border-l-2 border-slate-200 pl-4">
                            @foreach($paquete->estadoEventos->take(10) as $evt)
                                <li>
                                    <p class="font-semibold capitalize">{{ str_replace('_',' ', $evt->estado) }}</p>
                                    <p class="text-xs text-slate-500">{{ optional($evt->evento_at)->format('d/m/Y H:i') }}</p>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                @else
                    <p class="text-center text-slate-500">No encontramos un paquete con el código <strong>{{ $code }}</strong>.</p>
                @endif
            </div>
        @endif
    </main>
</body>
</html>
