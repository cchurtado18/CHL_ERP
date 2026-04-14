{{-- resources/views/layouts/sidebar-menu.blade.php --}}
<style>
    .nav-link.d-flex {
        align-items: center;
        gap: 0.7em;
        display: flex;
    }
    .nav-link.active, .nav-link:focus, .nav-link:hover {
        color: #2d6a9a !important;
        background: rgba(21,83,124,0.12);
        border-left: 4px solid #2d6a9a;
    }
</style>
<ul class="nav flex-column">
    @if(auth()->check() && auth()->user()->rol === 'basico')
        <li class="nav-item">
            <a class="nav-link d-flex @if(request()->is('inventario*')) active @endif" href="{{ url('/inventario') }}">
                <i class="fas fa-box"></i>
                <span>Inventario</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link d-flex @if(request()->is('encomiendas*')) active @endif" href="{{ url('/encomiendas') }}">
                <i class="fas fa-people-carry-box"></i>
                <span>Encomiendas</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link d-flex @if(request()->is('notificaciones*')) active @endif" href="{{ url('/notificaciones') }}">
                <i class="fas fa-bell"></i>
                <span>Notificaciones</span>
            </a>
        </li>
    @else
        @if(auth()->check() && auth()->user()->rol === 'admin')
            <li class="nav-item mb-2">
                <a href="/" class="nav-link d-flex align-items-center @if(request()->is('/')) active @endif">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
        @endif
        <li class="nav-item">
            <a class="nav-link d-flex @if(request()->is('clientes*')) active @endif" href="{{ url('/clientes') }}">
                <i class="fas fa-users"></i>
                <span>Clientes</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link d-flex @if(request()->is('inventario*')) active @endif" href="{{ url('/inventario') }}">
                <i class="fas fa-box"></i>
                <span>Inventario</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link d-flex @if(request()->is('encomiendas*')) active @endif" href="{{ url('/encomiendas') }}">
                <i class="fas fa-people-carry-box"></i>
                <span>Encomiendas</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link d-flex @if(request()->is('facturacion*')) active @endif" href="{{ url('/facturacion') }}">
                <i class="fas fa-file-invoice"></i>
                <span>Facturación</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link d-flex @if(request()->is('tracking*')) active @endif" href="{{ url('/tracking') }}">
                <i class="fas fa-search"></i>
                <span>Tracking</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link d-flex @if(request()->is('notificaciones*')) active @endif" href="{{ url('/notificaciones') }}">
                <i class="fas fa-bell"></i>
                <span>Notificaciones</span>
            </a>
        </li>
        @if($user && $user->rol === 'admin')
            <li class="nav-item">
                <a class="nav-link d-flex @if(request()->is('usuarios*')) active @endif" href="{{ url('/usuarios') }}">
                    <i class="fas fa-users"></i>
                    <span>Usuarios</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex @if(request()->is('logs-inventario')) active @endif" href="{{ url('/logs-inventario') }}">
                    <i class="fas fa-history"></i>
                    <span>Historial de Inventario</span>
                </a>
            </li>
        @endif
    @endif
    <!-- Agrega más enlaces según tu app -->
</ul> 