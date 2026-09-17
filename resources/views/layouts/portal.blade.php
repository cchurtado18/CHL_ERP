<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal') - CH Logistics</title>
    <link rel="icon" type="image/png" href="/CH_Logistics_Logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; font-size: 16px; }
    </style>
    @yield('head')
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
    @php $user = auth()->user(); @endphp

    {{-- Overlay móvil --}}
    <div id="portalOverlay" class="fixed inset-0 z-40 hidden bg-slate-900/50 lg:hidden" aria-hidden="true"></div>

    {{-- Sidebar (drawer en móvil) --}}
    <aside id="portalSidebar"
        class="fixed left-0 top-0 z-50 flex h-screen w-64 -translate-x-full flex-col border-r border-slate-200/80 bg-white shadow-sm transition-transform duration-200 lg:translate-x-0">
        <div class="flex h-36 flex-shrink-0 items-center justify-center border-b border-slate-100 px-4 py-3">
            <a href="{{ route('portal.home') }}" class="block w-full" title="CH Logistics">
                <img src="/CH_Logistics_Logo_hd.png" alt="CH Logistics" class="mx-auto h-24 w-auto max-w-full object-contain">
            </a>
        </div>
        <nav class="flex-1 overflow-y-auto py-4 px-3">
            <a href="{{ route('portal.home') }}"
               class="mb-1 flex h-12 items-center gap-3 rounded-r-lg px-3 text-[#15537c] {{ request()->routeIs('portal.home') ? 'bg-[#15537c]/10 border-l-4 border-[#15537c] font-semibold' : 'hover:bg-slate-100' }}">
                <i class="fas fa-home w-6 text-center text-lg"></i><span>Inicio</span>
            </a>
            <a href="{{ route('portal.paquetes.index') }}"
               class="mb-1 flex h-12 items-center gap-3 rounded-r-lg px-3 text-[#15537c] {{ request()->routeIs('portal.paquetes.*') ? 'bg-[#15537c]/10 border-l-4 border-[#15537c] font-semibold' : 'hover:bg-slate-100' }}">
                <i class="fas fa-box w-6 text-center text-lg"></i><span>Mis paquetes</span>
            </a>
            <a href="{{ route('portal.facturas.index') }}"
               class="mb-1 flex h-12 items-center gap-3 rounded-r-lg px-3 text-[#15537c] {{ request()->routeIs('portal.facturas.*') ? 'bg-[#15537c]/10 border-l-4 border-[#15537c] font-semibold' : 'hover:bg-slate-100' }}">
                <i class="fas fa-file-invoice-dollar w-6 text-center text-lg"></i><span>Mis facturas</span>
            </a>
            <a href="{{ route('portal.cuenta') }}"
               class="mb-1 flex h-12 items-center gap-3 rounded-r-lg px-3 text-[#15537c] {{ request()->routeIs('portal.cuenta') ? 'bg-[#15537c]/10 border-l-4 border-[#15537c] font-semibold' : 'hover:bg-slate-100' }}">
                <i class="fas fa-user-cog w-6 text-center text-lg"></i><span>Mi cuenta</span>
            </a>
            <a href="{{ route('public.tracking') }}" target="_blank" rel="noopener"
               class="mb-1 flex h-12 items-center gap-3 rounded-r-lg px-3 text-[#15537c] hover:bg-slate-100">
                <i class="fas fa-search-location w-6 text-center text-lg"></i><span>Rastreo público</span>
            </a>
        </nav>
        <div class="flex-shrink-0 border-t border-slate-200 px-3 py-4">
            <div class="mb-2 flex h-11 items-center gap-3 px-3">
                <i class="fas fa-user w-6 text-center text-slate-500"></i>
                <span class="flex-1 truncate text-sm font-medium text-slate-700">{{ $user->nombre }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex h-11 w-full items-center gap-3 rounded-r-lg px-3 text-left text-slate-600 hover:bg-slate-100 hover:text-[#15537c]">
                    <i class="fas fa-sign-out-alt w-6 text-center"></i>
                    <span class="text-sm font-medium">Cerrar sesión</span>
                </button>
            </form>
        </div>
    </aside>

    <div class="flex min-h-screen flex-col lg:ml-64">
        {{-- Top bar --}}
        <div class="sticky top-0 z-30 flex-shrink-0 border-b border-[#15537c]/20 bg-gradient-to-r from-[#15537c] via-[#15537c] to-[#2d6a9a] px-4 py-3 shadow-sm sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <button type="button" id="portalMenuBtn"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-white hover:bg-white/10 lg:hidden"
                        aria-label="Abrir menú" aria-controls="portalSidebar" aria-expanded="false">
                        <i class="fas fa-bars text-xl" id="portalMenuIcon"></i>
                    </button>
                    <div>
                        <p class="text-lg font-bold tracking-tight text-white sm:text-xl">CH Logistics</p>
                        <p class="text-xs text-white/70 sm:text-sm">@yield('navbar-title', 'Portal del cliente')</p>
                    </div>
                </div>
                <span class="hidden truncate text-sm text-white/80 sm:inline max-w-[200px]">{{ $user->nombre }}</span>
            </div>
        </div>

        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            @if(session('success'))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-base text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-base text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif
            @yield('content')
        </main>

        <footer class="border-t border-slate-200 py-4 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} CH Logistics · Portal del cliente
        </footer>
    </div>

    <script>
        (function () {
            const sidebar = document.getElementById('portalSidebar');
            const overlay = document.getElementById('portalOverlay');
            const btn = document.getElementById('portalMenuBtn');
            const icon = document.getElementById('portalMenuIcon');
            if (!sidebar || !overlay || !btn) return;

            function openMenu() {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                btn.setAttribute('aria-expanded', 'true');
                if (icon) { icon.classList.remove('fa-bars'); icon.classList.add('fa-times'); }
                document.body.classList.add('overflow-hidden');
            }
            function closeMenu() {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                btn.setAttribute('aria-expanded', 'false');
                if (icon) { icon.classList.remove('fa-times'); icon.classList.add('fa-bars'); }
                document.body.classList.remove('overflow-hidden');
            }
            function toggleMenu() {
                if (sidebar.classList.contains('-translate-x-full')) openMenu();
                else closeMenu();
            }

            btn.addEventListener('click', toggleMenu);
            overlay.addEventListener('click', closeMenu);
            window.addEventListener('resize', function () {
                if (window.innerWidth >= 1024) closeMenu();
            });
        })();
    </script>
    @yield('scripts')
    @stack('scripts')
</body>
</html>
