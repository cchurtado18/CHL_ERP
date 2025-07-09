@extends('layouts.app')

@section('title', 'Notificaciones - SkylinkOne CRM')

@section('content')
<div class="container-fluid px-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="rounded-4 shadow-sm px-4 py-4 mb-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(90deg, #1A2E75 0%, #5C6AC4 100%); min-height:90px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width:60px; height:60px; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                        <i class="fas fa-bell text-primary" style="font-size:2.6rem;"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-end gap-2">
                            <h1 class="mb-0 fw-bold text-white" style="font-size:2.1rem; letter-spacing:1px;">Notificaciones</h1>
                        </div>
                        <p class="mb-0 text-white-50" style="font-size:1.13rem; font-weight:400;">Gestiona las notificaciones del sistema</p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button id="toggleViewBtn" class="btn btn-toggle-view">
                        <i class="fas fa-th-large"></i>
                    </button>
                    <a href="{{ route('notificaciones.create') }}" class="btn btn-lg fw-semibold shadow-sm px-4 btn-crear-notif">
                        <i class="fas fa-plus me-2"></i>Nueva Notificación
                    </a>
                </div>
            </div>
        </div>
    </div>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    <!-- Vista Tabla -->
    <div id="notificacionesTabla" style="display: block;">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h6 class="m-0 fw-bold text-primary">
                    <i class="fas fa-bell me-2"></i>Notificaciones ({{ $notificaciones->total() }})
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table notificaciones-table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 px-4 py-3 fw-semibold text-dark">Usuario</th>
                                <th class="border-0 px-4 py-3 fw-semibold text-dark">Estado</th>
                                <th class="border-0 px-4 py-3 fw-semibold text-dark">Título</th>
                                <th class="border-0 px-4 py-3 fw-semibold text-dark">Mensaje</th>
                                <th class="border-0 px-4 py-3 fw-semibold text-dark">Fecha</th>
                                <th class="border-0 px-4 py-3 fw-semibold text-dark text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($notificaciones as $notificacion)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $notificacion->usuario->nombre ?? 'Usuario no encontrado' }}</div>
                                    <small class="text-muted">{{ $notificacion->usuario->email ?? '' }}</small>
                                </td>
                                <td>
                                    <span class="badge rounded-pill bg-{{ $notificacion->leido ? 'success' : 'warning' }} {{ $notificacion->leido ? '' : 'text-dark' }}">
                                        <i class="fas {{ $notificacion->leido ? 'fa-check' : 'fa-bell' }} me-1"></i>
                                        {{ $notificacion->leido ? 'Leída' : 'No leída' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary">{{ $notificacion->titulo }}</div>
                                </td>
                                <td>
                                    <span class="text-muted small">{{ Str::limit($notificacion->mensaje, 60) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        {{ $notificacion->fecha ? \Carbon\Carbon::parse($notificacion->fecha)->format('d/m/Y H:i') : 'N/A' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('notificaciones.show', $notificacion) }}" class="btn btn-inv-action btn-inv-view me-1" title="Ver Detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('notificaciones.edit', $notificacion) }}" class="btn btn-inv-action btn-inv-edit me-1" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('notificaciones.destroy', $notificacion) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta notificación?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-inv-action btn-inv-delete" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 empty-table-msg">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>No hay notificaciones disponibles</p>
                                        <a href="{{ route('notificaciones.create') }}" class="btn btn-lg fw-semibold shadow-sm px-4 btn-crear-notif">
                                            <i class="fas fa-plus me-2"></i>Crear Notificación
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($notificaciones->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $notificaciones->links('vendor.pagination.custom') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Vista Tarjetas -->
    <div id="notificacionesTarjetas" style="display: none;">
        <div class="row g-4">
            @forelse($notificaciones as $notificacion)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card shadow-sm h-100 border-0 position-relative">
                    <div class="card-body pb-4">
                        <div class="d-flex align-items-center mb-2">
                            <div class="me-3">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 1.2rem;">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <div>
                                <div class="fw-semibold text-dark small mb-1">
                                    {{ $notificacion->usuario->nombre ?? 'Usuario no encontrado' }}
                                </div>
                                <div class="text-muted small">{{ $notificacion->usuario->email ?? '' }}</div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <span class="badge rounded-pill bg-{{ $notificacion->leido ? 'success' : 'warning' }} me-2 {{ $notificacion->leido ? '' : 'text-dark' }}">
                                <i class="fas {{ $notificacion->leido ? 'fa-check' : 'fa-bell' }} me-1"></i>
                                {{ $notificacion->leido ? 'Leída' : 'No leída' }}
                            </span>
                            <span class="badge bg-light text-secondary border">
                                <i class="fas fa-calendar-alt me-1"></i>
                                {{ $notificacion->fecha ? \Carbon\Carbon::parse($notificacion->fecha)->format('d/m/Y H:i') : 'N/A' }}
                            </span>
                        </div>
                        <h5 class="card-title text-primary mb-2" style="min-height: 2.2em;">{{ $notificacion->titulo }}</h5>
                        <p class="card-text text-muted small" style="min-height: 3.5em;">
                            {{ Str::limit($notificacion->mensaje, 100) }}
                        </p>
                    </div>
                    <div class="card-footer bg-transparent border-0 d-flex justify-content-end gap-2 position-absolute bottom-0 end-0 w-100 pb-3 pe-3">
                        <a href="{{ route('notificaciones.show', $notificacion) }}" class="btn btn-sm btn-outline-primary" title="Ver Detalles">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('notificaciones.edit', $notificacion) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('notificaciones.destroy', $notificacion) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta notificación?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                <p class="text-muted">No hay notificaciones disponibles</p>
            </div>
            @endforelse
        </div>
        @if($notificaciones->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $notificaciones->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
const tabla = document.getElementById('notificacionesTabla');
const tarjetas = document.getElementById('notificacionesTarjetas');
const btn = document.getElementById('toggleViewBtn');
let vistaTabla = true;
btn.addEventListener('click', function() {
    vistaTabla = !vistaTabla;
    if (vistaTabla) {
        tabla.style.display = 'block';
        tarjetas.style.display = 'none';
        btn.innerHTML = '<i class="fas fa-th-large"></i> Cambiar Vista';
    } else {
        tabla.style.display = 'none';
        tarjetas.style.display = 'block';
        btn.innerHTML = '<i class="fas fa-list"></i> Cambiar Vista';
    }
});
</script>
@endsection

<style>
.notificaciones-table {
    border-radius: 20px 20px 16px 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(26,46,117,0.04);
    border-collapse: separate;
    border-spacing: 0;
}
.notificaciones-table thead th {
    background: #1A2E75 !important;
    color: #fff !important;
    font-weight: 700;
    letter-spacing: 0.5px;
    border: none !important;
    padding: 18px 18px !important;
    font-size: 1.08rem;
    vertical-align: middle;
    white-space: nowrap;
}
.notificaciones-table thead th:first-child {
    border-top-left-radius: 20px;
}
.notificaciones-table thead th:last-child {
    border-top-right-radius: 20px;
}
.notificaciones-table tbody td {
    border: none !important;
    padding: 16px 18px !important;
    vertical-align: middle !important;
    font-size: 1.04rem;
}
.notificaciones-table tbody tr:hover {
    background: #F0F4FF !important;
}
.empty-table-msg {
    background: #fff;
    border-bottom-left-radius: 16px;
    border-bottom-right-radius: 16px;
}
.btn-inv-action {
    border-radius: 8px !important;
    min-width: 34px;
    min-height: 34px;
    padding: 0 10px;
    font-size: 1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    box-shadow: none;
    transition: background 0.15s;
    margin-right: 4px;
}
.btn-inv-action:last-child {
    margin-right: 0;
}
.btn-inv-view {
    background: #1A2E75;
    color: #fff;
}
.btn-inv-edit {
    background: #5C6AC4;
    color: #fff;
}
.btn-inv-delete {
    background: #BF1E2E;
    color: #fff;
}
.btn-inv-action:hover, .btn-inv-action:focus {
    opacity: 0.92;
    color: #fff;
}
.btn-toggle-view {
    background: #fff !important;
    color: #1A2E75 !important;
    border: none !important;
    font-weight: 600;
    border-radius: 10px;
    box-shadow: none;
    transition: background 0.15s, color 0.15s;
    padding: 10px 22px;
}
.btn-toggle-view:hover, .btn-toggle-view:focus {
    background: #1A2E75 !important;
    color: #fff !important;
}
.btn-crear-notif {
    background: #1A2E75 !important;
    color: #fff !important;
    border-radius: 10px;
    font-size: 1.08rem;
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(26,46,117,0.08);
    padding: 12px 32px !important;
    transition: background 0.15s, color 0.15s;
}
.btn-crear-notif:hover, .btn-crear-notif:focus {
    background: #223a7a !important;
    color: #fff !important;
}
.card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style> 