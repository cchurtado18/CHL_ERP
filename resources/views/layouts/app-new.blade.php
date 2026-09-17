<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CH Logistics')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; font-size: 16px; }
    </style>
    @yield('head')
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
    @php $user = Auth::user(); @endphp
    @auth
    <div class="flex min-h-screen">
        {{-- Overlay móvil --}}
        <div id="erpOverlay" class="fixed inset-0 z-40 hidden bg-slate-900/50 lg:hidden" aria-hidden="true"></div>

        {{-- Sidebar: logo grande en la esquina + menú --}}
        <aside id="erpSidebar" class="fixed left-0 top-0 z-50 flex h-screen w-64 -translate-x-full flex-col border-r border-slate-200/80 bg-white shadow-sm transition-transform duration-200 lg:translate-x-0">
            <div class="flex h-44 flex-shrink-0 items-center justify-center border-b border-slate-100 bg-white px-4 py-4">
                <a href="{{ url('/inventario') }}" class="block w-full" title="CH Logistics">
                    <img src="/CH_Logistics_Logo.png" alt="CH Logistics" class="mx-auto h-36 w-auto max-w-full object-contain object-center">
                </a>
            </div>
            <nav class="flex-1 overflow-y-auto py-4 px-3">
                    @if($user->tienePermiso('dashboard'))
                    <a href="{{ url('/') }}" class="mb-1 flex h-12 items-center gap-3 rounded-r-lg px-3 text-[#15537c] {{ request()->is('/') && !request()->is('contabilidad*') ? 'bg-[#15537c]/10 border-l-4 border-[#15537c] font-semibold' : 'hover:bg-slate-100' }}" title="Dashboard"><i class="fas fa-tachometer-alt w-6 text-center text-lg"></i><span>Dashboard</span></a>
                    @endif
                    @if($user->tienePermiso('clientes'))
                    <a href="{{ url('/clientes') }}" class="mb-1 flex h-12 items-center gap-3 rounded-r-lg px-3 text-[#15537c] {{ request()->is('clientes*') ? 'bg-[#15537c]/10 border-l-4 border-[#15537c] font-semibold' : 'hover:bg-slate-100' }}" title="Clientes"><i class="fas fa-users w-6 text-center text-lg"></i><span>Clientes</span></a>
                    @endif
                    @if($user->tienePermiso('inventario'))
                    <a href="{{ url('/inventario') }}" class="mb-1 flex h-12 items-center gap-3 rounded-r-lg px-3 text-[#15537c] {{ request()->is('inventario*') ? 'bg-[#15537c]/10 border-l-4 border-[#15537c] font-semibold' : 'hover:bg-slate-100' }}" title="Inventario"><i class="fas fa-box w-6 text-center text-lg"></i><span>Inventario</span></a>
                    @endif
                    @if($user->tienePermiso('encomiendas'))
                    <a href="{{ url('/encomiendas') }}" class="mb-1 flex h-12 items-center gap-3 rounded-r-lg px-3 text-[#15537c] {{ request()->is('encomiendas*') ? 'bg-[#15537c]/10 border-l-4 border-[#15537c] font-semibold' : 'hover:bg-slate-100' }}" title="Encomiendas"><i class="fas fa-people-carry-box w-6 text-center text-lg"></i><span>Encomiendas</span></a>
                    @endif
                    @if($user->tienePermiso('leads'))
                    <a href="{{ url('/leads') }}" class="mb-1 flex h-12 items-center gap-3 rounded-r-lg px-3 text-[#15537c] {{ request()->is('leads*') ? 'bg-[#15537c]/10 border-l-4 border-[#15537c] font-semibold' : 'hover:bg-slate-100' }}" title="Leads"><i class="fas fa-bullseye w-6 text-center text-lg"></i><span>Leads</span></a>
                    @endif
                    @if($user->tienePermiso('facturacion'))
                    <a href="{{ url('/facturacion') }}" class="mb-1 flex h-12 items-center gap-3 rounded-r-lg px-3 text-[#15537c] {{ request()->is('facturacion*') ? 'bg-[#15537c]/10 border-l-4 border-[#15537c] font-semibold' : 'hover:bg-slate-100' }}" title="Facturación"><i class="fas fa-file-invoice w-6 text-center text-lg"></i><span>Facturación</span></a>
                    @endif
                    @if($user->tienePermiso('contabilidad'))
                    <a href="{{ url('/contabilidad') }}" class="mb-1 flex h-12 items-center gap-3 rounded-r-lg px-3 text-[#15537c] {{ request()->is('contabilidad*') && !request()->is('contabilidad/cobros*') ? 'bg-[#15537c]/10 border-l-4 border-[#15537c] font-semibold' : 'hover:bg-slate-100' }}" title="Contabilidad"><i class="fas fa-calculator w-6 text-center text-lg"></i><span>Contabilidad</span></a>
                    @elseif($user->tienePermiso('contabilidad.cobros'))
                    <a href="{{ route('contabilidad.cobros.create') }}" class="mb-1 flex h-12 items-center gap-3 rounded-r-lg px-3 text-[#15537c] {{ request()->is('contabilidad/cobros*') ? 'bg-[#15537c]/10 border-l-4 border-[#15537c] font-semibold' : 'hover:bg-slate-100' }}" title="Registrar cobro"><i class="fas fa-hand-holding-usd w-6 text-center text-lg"></i><span>Registrar cobro</span></a>
                    @endif
                    @if($user->tienePermiso('tracking'))
                    <a href="{{ url('/tracking') }}" class="mb-1 flex h-12 items-center gap-3 rounded-r-lg px-3 text-[#15537c] {{ request()->is('tracking*') ? 'bg-[#15537c]/10 border-l-4 border-[#15537c] font-semibold' : 'hover:bg-slate-100' }}" title="Tracking"><i class="fas fa-search w-6 text-center text-lg"></i><span>Tracking</span></a>
                    @endif
                    @if($user->tienePermiso('notificaciones'))
                    <a href="{{ url('/notificaciones') }}" class="relative mb-1 flex h-12 items-center gap-3 rounded-r-lg px-3 text-[#15537c] {{ request()->is('notificaciones*') ? 'bg-[#15537c]/10 border-l-4 border-[#15537c] font-semibold' : 'hover:bg-slate-100' }}" title="Notificaciones">
                        <span class="relative inline-flex w-6 justify-center">
                            <i class="fas fa-bell text-center text-lg"></i>
                            <span id="notif-unread-dot" class="pointer-events-none absolute -right-0.5 -top-0.5 hidden h-2.5 min-w-2.5 rounded-full bg-red-500 ring-2 ring-white" title="Hay notificaciones sin leer" aria-hidden="true"></span>
                        </span>
                        <span>Notificaciones</span>
                    </a>
                    @endif
                    @if($user->tienePermiso('usuarios'))
                    <a href="{{ url('/usuarios') }}" class="mb-1 flex h-12 items-center gap-3 rounded-r-lg px-3 text-[#15537c] {{ request()->is('usuarios*') ? 'bg-[#15537c]/10 border-l-4 border-[#15537c] font-semibold' : 'hover:bg-slate-100' }}" title="Usuarios"><i class="fas fa-user-cog w-6 text-center text-lg"></i><span>Usuarios</span></a>
                    @endif
                    @if($user->tienePermiso('logs_inventario'))
                    <a href="{{ url('/logs-inventario') }}" class="mb-1 flex h-12 items-center gap-3 rounded-r-lg px-3 text-[#15537c] {{ request()->is('logs-inventario') ? 'bg-[#15537c]/10 border-l-4 border-[#15537c] font-semibold' : 'hover:bg-slate-100' }}" title="Historial"><i class="fas fa-history w-6 text-center text-lg"></i><span>Historial</span></a>
                    @endif
            </nav>
            {{-- Usuario, tracking y cerrar sesión en el sidebar --}}
            <div class="flex-shrink-0 border-t border-slate-200 px-3 py-4">
                @if($user->tienePermiso('tracking'))
                <a href="{{ route('tracking.dashboard') }}" class="mb-3 flex h-11 items-center gap-3 rounded-r-lg px-3 text-[#15537c] hover:bg-slate-100" title="Tracking vencidos">
                    <i class="fas fa-exclamation-triangle w-6 text-center text-lg"></i>
                    <span class="flex-1 text-sm font-medium">Tracking</span>
                    <span id="trackingVencidoCount" class="hidden h-6 min-w-[22px] items-center justify-center rounded-full bg-[#15537c] px-1.5 text-xs font-bold text-white">0</span>
                </a>
                @endif
                <div class="mb-2 flex h-11 items-center gap-3 px-3">
                    <i class="fas fa-user w-6 text-center text-slate-500"></i>
                    <span class="flex-1 truncate text-sm font-medium text-slate-700">{{ $user->nombre ?? 'Usuario' }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex h-11 w-full items-center gap-3 rounded-r-lg px-3 text-left text-slate-600 hover:bg-slate-100 hover:text-[#15537c]" title="Cerrar sesión">
                        <i class="fas fa-sign-out-alt w-6 text-center"></i>
                        <span class="text-sm font-medium">Cerrar sesión</span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex min-h-screen flex-1 flex-col lg:ml-64">
            {{-- Banner de marca - toda la parte de arriba --}}
            <div class="sticky top-0 z-30 flex-shrink-0 border-b border-[#15537c]/20 bg-gradient-to-r from-[#15537c] via-[#15537c] to-[#2d6a9a] px-4 py-3 shadow-sm sm:px-6 lg:px-8">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <button type="button" id="erpMenuBtn"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-white hover:bg-white/10 lg:hidden"
                            aria-label="Abrir menú" aria-controls="erpSidebar" aria-expanded="false">
                            <i class="fas fa-bars text-xl" id="erpMenuIcon"></i>
                        </button>
                        <span class="text-lg font-bold tracking-tight text-white drop-shadow-sm sm:text-xl">CH Logistics</span>
                    </div>
                    @if($user->tienePermiso('notificaciones'))
                    <a href="{{ url('/notificaciones') }}" class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg text-white/90 hover:bg-white/10" title="Notificaciones">
                        <i class="fas fa-bell text-lg"></i>
                        <span id="notif-unread-dot-header" class="pointer-events-none absolute right-1 top-1 hidden h-2.5 min-w-2.5 rounded-full bg-red-500 ring-2 ring-[#15537c]" aria-hidden="true"></span>
                    </a>
                    @endif
                </div>
            </div>

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @yield('content')
            </main>
        </div>
    </div>
    @else
    <div class="flex min-h-screen items-center justify-center">
        @yield('content')
    </div>
    @endauth

    @auth
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            (function () {
                const sidebar = document.getElementById('erpSidebar');
                const overlay = document.getElementById('erpOverlay');
                const btn = document.getElementById('erpMenuBtn');
                const icon = document.getElementById('erpMenuIcon');
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
                btn.addEventListener('click', function () {
                    if (sidebar.classList.contains('-translate-x-full')) openMenu();
                    else closeMenu();
                });
                overlay.addEventListener('click', closeMenu);
                window.addEventListener('resize', function () {
                    if (window.innerWidth >= 1024) closeMenu();
                });
            })();
            @if($user->tienePermiso('tracking'))
            function loadTrackingVencidos() {
                fetch('{{ route("tracking.vencidos.count") }}').then(r => r.json()).then(data => {
                    const badge = document.getElementById('trackingVencidoCount');
                    if (badge && data.has_vencidos) { badge.textContent = data.count; badge.classList.remove('hidden'); badge.classList.add('flex'); }
                    else if (badge) badge.classList.add('hidden');
                }).catch(() => {});
            }
            loadTrackingVencidos();
            setInterval(loadTrackingVencidos, 60000);
            @endif
            @if($user->tienePermiso('notificaciones'))
            function loadNotificacionesNoLeidas() {
                fetch('{{ route("notificaciones.no-leidas") }}', { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(data => {
                        const n = Array.isArray(data) ? data.length : 0;
                        document.querySelectorAll('#notif-unread-dot').forEach(el => {
                            if (n > 0) el.classList.remove('hidden');
                            else el.classList.add('hidden');
                        });
                        const h = document.getElementById('notif-unread-dot-header');
                        if (h) {
                            if (n > 0) h.classList.remove('hidden');
                            else h.classList.add('hidden');
                        }
                    })
                    .catch(() => {});
            }
            loadNotificacionesNoLeidas();
            setInterval(loadNotificacionesNoLeidas, 30000);
            document.addEventListener('visibilitychange', function () {
                if (! document.hidden) {
                    loadNotificacionesNoLeidas();
                }
            });
            @endif
        });
    </script>
    @endauth
    @yield('scripts')
    @stack('scripts')
</body>
</html>
