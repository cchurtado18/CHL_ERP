{{-- resources/views/layouts/navbar-menu.blade.php --}}
<style>
    .logout-link {
        color: inherit;
        text-decoration: none;
        font-size: 1rem;
        transition: color 0.2s;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .logout-link:hover {
        color: #D7263D !important;
    }
    .navbar-user {
        font-weight: 500;
        margin-right: 1.2rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 1rem;
    }
    .navbar-user i, .logout-link i {
        font-size: 1em;
        vertical-align: middle;
    }
    .navbar-flex {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        width: 100%;
        font-size: 1rem;
    }
    .navbar-flex form {
        margin: 0;
        padding: 0;
        display: flex;
        align-items: center;
    }
    .tracking-notification {
        position: relative;
        margin-right: 1rem;
    }
    .tracking-notification-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #dc3545;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: bold;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
</style>
<div class="navbar-flex">
    <!-- Notificación de tracking vencido -->
    <div class="tracking-notification">
        <a href="{{ route('tracking.dashboard') }}" class="btn btn-outline-danger btn-sm position-relative">
            <i class="fas fa-exclamation-triangle"></i>
            <span class="tracking-notification-badge" id="trackingVencidoCount" style="display: none;">0</span>
        </a>
    </div>
    
    <span class="navbar-user">
        <i class="fas fa-user"></i> {{ $user->nombre ?? 'Usuario' }}
    </span>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="logout-link">
            <i class="fas fa-sign-out-alt"></i> Cerrar sesión
        </button>
    </form>
</div> 