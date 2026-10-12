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
            --navy: #0d3554;
            --navy-deep: #081e31;
            --brand: #15537c;
            --amber: #F0A63A;
        }
        html, body { height: 100%; }
        body {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
        }

        .brand-pane {
            background:
                radial-gradient(900px 520px at 12% 18%, rgba(240,166,58,0.16) 0%, transparent 55%),
                radial-gradient(720px 480px at 92% 88%, rgba(45,140,196,0.22) 0%, transparent 50%),
                linear-gradient(165deg, #0b2a43 0%, var(--navy-deep) 58%, #061522 100%);
        }
        .brand-pane::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.035) 1px, transparent 1px);
            background-size: 56px 56px;
            mask-image: radial-gradient(ellipse at 40% 30%, #000 20%, transparent 72%);
            pointer-events: none;
        }

        @keyframes rise {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .rise-1 { animation: rise .55s cubic-bezier(.22,1,.36,1) both; }
        .rise-2 { animation: rise .55s .08s cubic-bezier(.22,1,.36,1) both; }

        .field {
            width: 100%;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 0.9rem 1rem;
            font-size: 0.95rem;
            color: #0f172a;
            transition: border-color .18s, box-shadow .18s, background .18s;
        }
        .field::placeholder { color: #94a3b8; }
        .field:hover { border-color: #cbd5e1; }
        .field:focus {
            outline: none;
            background: #fff;
            border-color: var(--brand);
            box-shadow: 0 0 0 4px rgba(21,83,124,0.12);
        }
        .field-error { border-color: #fb7185; background: #fff5f5; }
        .field-error:focus { box-shadow: 0 0 0 4px rgba(251,113,133,0.16); }

        .field-track {
            border-color: #f3d19a;
            background: #fff;
        }
        .field-track:focus {
            border-color: var(--amber);
            box-shadow: 0 0 0 4px rgba(240,166,58,0.22);
        }

        .btn-primary {
            background: linear-gradient(180deg, #1a6494 0%, var(--brand) 100%);
            box-shadow: 0 1px 0 rgba(255,255,255,0.18) inset, 0 10px 24px -12px rgba(21,83,124,0.7);
        }
        .btn-primary:hover { filter: brightness(1.06); }
        .btn-primary:active { transform: translateY(1px); }

        .btn-track {
            background: linear-gradient(180deg, #f5b85a 0%, var(--amber) 100%);
            color: #0d3452;
            box-shadow: 0 1px 0 rgba(255,255,255,0.28) inset, 0 10px 22px -12px rgba(240,166,58,0.8);
        }
        .btn-track:hover { filter: brightness(1.05); }

        .tip-slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            transform: translateX(12px);
            transition: opacity .45s ease, transform .45s ease;
            pointer-events: none;
        }
        .tip-slide.is-active {
            opacity: 1;
            transform: translateX(0);
            pointer-events: auto;
        }
        .tip-dot {
            height: 6px;
            width: 6px;
            border-radius: 999px;
            background: #cbd5e1;
            transition: width .25s, background .25s;
        }
        .tip-dot.is-active {
            width: 16px;
            background: #F0A63A;
        }
    </style>
</head>
<body class="min-h-screen antialiased">
    <div class="grid min-h-screen lg:grid-cols-[1.05fr_minmax(420px,0.95fr)]">

        <aside class="brand-pane relative hidden overflow-hidden text-white lg:flex lg:flex-col">
            <div class="relative z-10 flex flex-1 flex-col px-12 py-10 xl:px-16">
                <a href="{{ url('/') }}" class="inline-flex w-fit items-center rounded-2xl bg-white px-3.5 py-2.5 shadow-lg shadow-black/10">
                    <img src="/CH_Logistics_Logo_hd.png" alt="CH Logistics" class="h-11 w-auto object-contain">
                </a>

                <div class="rise-1 my-auto max-w-lg py-10">
                    <h1 class="text-4xl font-extrabold leading-[1.1] tracking-tight xl:text-5xl">
                        Tu operación,<br>
                        <span class="text-[#F0A63A]">clara y bajo control.</span>
                    </h1>

                    <div class="mt-16 rounded-2xl border border-white/10 bg-white/[0.07] px-5 py-5">
                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#F0A63A]">Consejo de rastreo</p>
                        <p class="mt-3 text-[15px] leading-relaxed text-white/80">
                            Si rastreas tu paquete y aún no aparece, no te preocupes: solemos tomarnos un día para ingresarlo al sistema.
                        </p>
                        <p class="mt-3 text-[15px] leading-relaxed text-white/80">
                            Si en los próximos días sigue sin verse, contáctanos para verificar en bodega. Si no fue recibido, reclama con tu proveedor en la plataforma de compra.
                        </p>
                    </div>
                </div>

                <p class="text-xs font-medium text-white/30">&copy; {{ date('Y') }} CH Logistics</p>
            </div>
        </aside>

        <main class="relative flex flex-col bg-[#f4f6f8]">
            <div class="relative z-10 mx-auto flex w-full max-w-[460px] flex-1 flex-col justify-center gap-5 px-5 py-8 sm:px-8">
                <div class="flex items-center justify-between lg:hidden">
                    <img src="/CH_Logistics_Logo_hd.png" alt="CH Logistics" class="h-10 w-auto object-contain">
                </div>

                <section class="rise-2 rounded-3xl border border-[#F0A63A]/25 bg-white p-5 shadow-[0_18px_40px_-24px_rgba(240,166,58,0.45)] sm:p-6">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#F0A63A]">Rastreo</p>
                            <h2 class="mt-1 text-xl font-extrabold tracking-tight text-slate-900">¿Dónde está tu paquete?</h2>
                        </div>
                    </div>
                    <form method="GET" action="{{ route('public.tracking') }}" class="flex flex-col gap-3 sm:flex-row">
                        <label for="tracking" class="sr-only">Tracking o guía</label>
                        <input type="text" name="tracking" id="tracking" required autofocus
                            placeholder="Tracking o número de guía"
                            class="field field-track min-w-0 flex-1 py-3.5">
                        <button type="submit"
                            class="btn-track shrink-0 rounded-xl px-5 py-3.5 text-sm font-extrabold">
                            Rastrear
                        </button>
                    </form>
                </section>

                <section id="tipCarousel" class="lg:hidden rounded-2xl border border-slate-200 bg-white px-4 py-3.5" aria-label="Consejo de rastreo">
                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-[#F0A63A]">Consejo de rastreo</p>
                    <div class="relative mt-2 min-h-[6.5rem]">
                        <p class="tip-slide is-active text-[13px] leading-relaxed text-slate-600">
                            Si rastreas tu paquete y aún no aparece, no te preocupes: solemos tomarnos un día para ingresarlo al sistema.
                        </p>
                        <p class="tip-slide text-[13px] leading-relaxed text-slate-600">
                            Si en los próximos días sigue sin verse, contáctanos para verificar en bodega.
                        </p>
                        <p class="tip-slide text-[13px] leading-relaxed text-slate-600">
                            Si no fue recibido, reclama con tu proveedor en la plataforma donde hiciste la compra.
                        </p>
                    </div>
                    <div class="mt-3 flex items-center gap-1.5" aria-hidden="true">
                        <span class="tip-dot is-active"></span>
                        <span class="tip-dot"></span>
                        <span class="tip-dot"></span>
                    </div>
                </section>

                <section class="rise-2 rounded-3xl border border-slate-200/80 bg-white px-5 py-6 shadow-[0_16px_40px_-28px_rgba(15,23,42,0.25)] sm:px-6">
                    <h2 class="text-lg font-extrabold tracking-tight text-slate-900">Iniciar sesión</h2>
                    <p class="mt-1 text-sm text-slate-500">Acceso para el equipo.</p>

                    @if($errors->any())
                        <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="mt-5 space-y-4">
                        @csrf

                        <div>
                            <label for="email" class="mb-1.5 block text-[13px] font-semibold text-slate-700">Correo electrónico</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                autocomplete="username"
                                placeholder="tucorreo@empresa.com"
                                class="field {{ $errors->has('email') ? 'field-error' : '' }}">
                        </div>

                        <div>
                            <label for="password" class="mb-1.5 block text-[13px] font-semibold text-slate-700">Contraseña</label>
                            <div class="relative">
                                <input type="password" name="password" id="password" required
                                    autocomplete="current-password"
                                    placeholder="••••••••"
                                    class="field pr-12 {{ $errors->has('password') ? 'field-error' : '' }}">
                                <button type="button" id="togglePass" tabindex="-1"
                                    class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-400 transition hover:text-[#15537c]"
                                    title="Mostrar u ocultar contraseña"
                                    aria-label="Mostrar u ocultar contraseña">
                                    <svg id="iconEye" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    <svg id="iconEyeOff" class="hidden" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path d="M3 3l18 18"/>
                                        <path d="M10.6 10.6A3 3 0 0 0 12 15a3 3 0 0 0 2.4-1.2"/>
                                        <path d="M9.9 5.1A10.8 10.8 0 0 1 12 5c6.5 0 10 7 10 7a18.3 18.3 0 0 1-3.2 4.1"/>
                                        <path d="M6.1 6.1A18.5 18.5 0 0 0 2 12s3.5 7 10 7a10.6 10.6 0 0 0 3.3-.5"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <label class="flex cursor-pointer items-center gap-2.5 text-sm text-slate-600">
                            <input type="checkbox" name="remember" id="remember" value="1"
                                class="h-4 w-4 rounded border-slate-300 text-[#15537c] focus:ring-[#15537c]">
                            Recordarme en este equipo
                        </label>

                        <button type="submit"
                            class="btn-primary flex w-full items-center justify-center rounded-xl py-3.5 text-sm font-bold text-white transition">
                            Continuar
                        </button>
                    </form>
                </section>

                <p class="text-center text-xs text-slate-400 lg:hidden">&copy; {{ date('Y') }} CH Logistics</p>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('togglePass').addEventListener('click', function () {
            const input = document.getElementById('password');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            document.getElementById('iconEye').classList.toggle('hidden', show);
            document.getElementById('iconEyeOff').classList.toggle('hidden', !show);
        });

        (function () {
            const root = document.getElementById('tipCarousel');
            if (!root) return;
            const slides = Array.from(root.querySelectorAll('.tip-slide'));
            const dots = Array.from(root.querySelectorAll('.tip-dot'));
            if (slides.length < 2) return;
            let i = 0;
            const show = (next) => {
                slides[i].classList.remove('is-active');
                dots[i]?.classList.remove('is-active');
                i = (next + slides.length) % slides.length;
                slides[i].classList.add('is-active');
                dots[i]?.classList.add('is-active');
            };
            let timer = setInterval(() => show(i + 1), 4200);
            root.addEventListener('click', () => {
                show(i + 1);
                clearInterval(timer);
                timer = setInterval(() => show(i + 1), 4200);
            });
        })();
    </script>
</body>
</html>
