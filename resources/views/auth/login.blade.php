<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar Sesión - CH Logistics</title>
    <link rel="icon" type="image/png" href="/CH_Logistics_Logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }

        /* Lado claro: fondo suave con brillos de los colores de la marca */
        .bg-light {
            background:
                radial-gradient(700px 480px at 20% 15%, rgba(240,166,58,0.10) 0%, transparent 60%),
                radial-gradient(800px 560px at 85% 90%, rgba(21,83,124,0.10) 0%, transparent 60%),
                linear-gradient(165deg, #f8fafc 0%, #eef3f8 100%);
        }

        /* Panel oscuro del formulario */
        .bg-dark-panel {
            background:
                radial-gradient(600px 400px at 90% -10%, rgba(45,106,154,0.5) 0%, transparent 60%),
                radial-gradient(520px 380px at -10% 110%, rgba(240,166,58,0.14) 0%, transparent 55%),
                linear-gradient(170deg, #103e5f 0%, #0d3452 60%, #0a2b45 100%);
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes floaty {
            0%, 100% { transform: translateY(0); }
            50%      { transform: translateY(-10px); }
        }
        .anim-1 { animation: fadeUp .55s ease-out both; }
        .anim-2 { animation: fadeUp .55s .15s ease-out both; }
        .logo-float { animation: floaty 6s ease-in-out infinite; }

        .input-dark {
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.18);
            color: #fff;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }
        .input-dark::placeholder { color: rgba(255,255,255,0.35); }
        .input-dark:focus {
            border-color: #F0A63A;
            background: rgba(255,255,255,0.10);
            box-shadow: 0 0 0 3px rgba(240,166,58,0.25);
            outline: none;
        }
    </style>
</head>
<body class="bg-light min-h-screen antialiased">

    <div class="flex min-h-screen flex-col lg:flex-row">

        {{-- Lado izquierdo: logo protagonista sobre el fondo claro --}}
        <div class="anim-1 relative flex flex-1 flex-col items-center justify-center px-6 py-12 lg:py-0">
            {{-- Líneas decorativas sutiles --}}
            <div class="pointer-events-none absolute left-10 top-10 hidden h-20 w-20 rounded-full border-2 border-[#F0A63A]/25 lg:block"></div>
            <div class="pointer-events-none absolute bottom-14 left-24 hidden h-3 w-3 rounded-full bg-[#F0A63A]/40 lg:block"></div>
            <div class="pointer-events-none absolute right-16 top-24 hidden h-2.5 w-2.5 rounded-full bg-[#15537c]/30 lg:block"></div>

            <img src="/CH_Logistics_Logo_hd.png" alt="CH Logistics"
                class="logo-float h-auto w-80 object-contain drop-shadow-xl sm:w-[26rem] lg:w-[32rem]">

            <p class="mt-8 max-w-md text-center text-base font-medium text-slate-500 sm:text-lg">
                Tu operación logística, clara y bajo control.
            </p>

            <div class="mt-8 hidden flex-wrap items-center justify-center gap-3 lg:flex">
                <span class="inline-flex items-center gap-2 rounded-full border border-[#15537c]/15 bg-white px-4 py-2 text-sm font-semibold text-[#15537c] shadow-sm">
                    <i class="fas fa-plane-departure text-[#F0A63A]"></i> Aéreo
                </span>
                <span class="inline-flex items-center gap-2 rounded-full border border-[#15537c]/15 bg-white px-4 py-2 text-sm font-semibold text-[#15537c] shadow-sm">
                    <i class="fas fa-ship text-[#F0A63A]"></i> Marítimo
                </span>
                <span class="inline-flex items-center gap-2 rounded-full border border-[#15537c]/15 bg-white px-4 py-2 text-sm font-semibold text-[#15537c] shadow-sm">
                    <i class="fas fa-box-open text-[#F0A63A]"></i> Encomiendas
                </span>
            </div>
        </div>

        {{-- Lado derecho: panel oscuro flotante con el formulario --}}
        <div class="flex items-stretch justify-center px-4 pb-10 lg:w-[44%] lg:min-w-[460px] lg:p-6">
            <div class="anim-2 bg-dark-panel flex w-full max-w-md flex-col justify-center rounded-3xl px-8 py-12 shadow-2xl shadow-[#0a2b45]/40 sm:px-12 lg:max-w-none">

                <div class="mb-9">
                    <span class="inline-flex items-center gap-2 rounded-full bg-[#F0A63A]/15 px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-[#F0A63A]">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#F0A63A]"></span> Acceso al sistema
                    </span>
                    <h1 class="mt-5 text-3xl font-extrabold tracking-tight text-white">Hola de nuevo</h1>
                    <p class="mt-2 text-sm text-white/60">Ingresa tus credenciales para entrar al panel.</p>
                </div>

                @if($errors->any())
                    <div class="mb-6 flex items-start gap-3 rounded-xl border border-rose-400/40 bg-rose-500/15 px-4 py-3 text-sm text-rose-200" role="alert">
                        <i class="fas fa-circle-exclamation mt-0.5"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="email" class="mb-2 block text-sm font-semibold text-white/80">Correo electrónico</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex w-11 items-center justify-center text-white/40">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                                autocomplete="username"
                                placeholder="tucorreo@empresa.com"
                                class="input-dark w-full rounded-xl py-3.5 pl-11 pr-4 text-base {{ $errors->has('email') ? '!border-rose-400' : '' }}">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-semibold text-white/80">Contraseña</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex w-11 items-center justify-center text-white/40">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" name="password" id="password" required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="input-dark w-full rounded-xl py-3.5 pl-11 pr-12 text-base {{ $errors->has('password') ? '!border-rose-400' : '' }}">
                            <button type="button" id="togglePass" tabindex="-1"
                                class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-white/40 transition hover:text-[#F0A63A]"
                                title="Mostrar / ocultar contraseña">
                                <i class="fas fa-eye" id="togglePassIcon"></i>
                            </button>
                        </div>
                    </div>

                    <label class="inline-flex cursor-pointer items-center gap-2.5 text-sm text-white/70">
                        <input type="checkbox" name="remember" id="remember"
                            class="h-4 w-4 rounded border-white/30 bg-white/10 text-[#F0A63A] focus:ring-[#F0A63A]">
                        Recordarme en este equipo
                    </label>

                    <button type="submit"
                        class="group flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#F0A63A] to-[#e8940f] py-4 text-base font-bold text-[#0d3452] shadow-lg shadow-[#F0A63A]/25 transition hover:from-[#f5b45c] hover:to-[#F0A63A] hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-[#F0A63A] focus:ring-offset-2 focus:ring-offset-[#0d3452]">
                        Entrar al sistema
                        <i class="fas fa-arrow-right text-sm transition-transform group-hover:translate-x-1"></i>
                    </button>
                </form>

                <p class="mt-10 text-center text-xs font-medium text-white/35">
                    &copy; {{ date('Y') }} CH Logistics &middot; Todos los derechos reservados
                </p>
                <p class="mt-3 text-center text-sm">
                    <a href="{{ route('public.tracking') }}" class="font-semibold text-[#F0A63A] hover:underline">Rastrear paquete sin cuenta</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('togglePass').addEventListener('click', function () {
            const input = document.getElementById('password');
            const icon = document.getElementById('togglePassIcon');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.classList.toggle('fa-eye', !show);
            icon.classList.toggle('fa-eye-slash', show);
        });
    </script>
</body>
</html>
