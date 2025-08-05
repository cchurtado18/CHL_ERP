@extends('layouts.app')

@section('title', 'Dashboard de Tracking - SkylinkOne CRM')

@section('content')
<div class="container-fluid px-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="rounded-4 shadow-sm px-4 py-4 mb-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(90deg, #1A2E75 0%, #5C6AC4 100%); min-height:90px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width:60px; height:60px; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                        <i class="fas fa-chart-line text-primary" style="font-size:2.2rem;"></i>
                    </div>
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="letter-spacing:1px;">Dashboard de Tracking</h1>
                        <p class="mb-0 text-white-50" style="font-size:1.1rem;">Monitoreo y control de seguimientos con temporizadores</p>
                    </div>
                </div>
                <a href="{{ route('tracking.create') }}" class="btn btn-lg fw-semibold shadow-sm px-4" style="background:#1A2E75; color:#fff;">
                    <i class="fas fa-plus me-2"></i> Nuevo Tracking
                </a>
            </div>
            <!-- Botones secundarios arriba de las tarjetas de estadísticas -->
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-start gap-2">
                    <a href="{{ route('tracking.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2 px-4 py-2 fw-semibold shadow-sm" style="font-size:1.01em; border-radius:0.75rem;">
                        <i class="fas fa-list me-1"></i> Ver Todos
                    </a>
                    <a href="{{ route('tracking.index', ['estado' => 'completado']) }}" class="btn btn-success d-flex align-items-center gap-2 px-4 py-2 fw-semibold shadow-sm" style="font-size:1.01em; border-radius:0.75rem;">
                        <i class="fas fa-check-circle me-1"></i> Ver Completados
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-calendar text-primary fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-1">TOTAL TRACKINGS</h6>
                            <h4 class="mb-0 fw-bold text-dark">{{ $totalTrackings }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-clock text-warning fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-1">PENDIENTES</h6>
                            <h4 class="mb-0 fw-bold text-dark">{{ $trackingsPendientes }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-exclamation-triangle text-danger fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-1">VENCIDOS</h6>
                            <h4 class="mb-0 fw-bold text-danger">{{ $trackingsVencidos }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-check-circle text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-1">COMPLETADOS</h6>
                            <h4 class="mb-0 fw-bold text-dark">{{ $trackingsCompletados }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Búsqueda Rápida -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8 mb-2 mb-md-0">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-primary"></i></span>
                                <input type="text" id="codigoTracking" class="form-control border-start-0" placeholder="Ingresa el código de tracking...">
                                <button class="btn btn-primary" type="button" onclick="buscarTracking()">
                                    <i class="fas fa-search me-2"></i>Buscar
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-outline-info w-100" onclick="cargarProximosVencer()">
                                <i class="fas fa-clock me-2"></i>Próximos a Vencer
                            </button>
                        </div>
                    </div>
                    <div id="resultadoBusqueda" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Trackings Vencidos -->
    @if($trackingsVencidos > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-danger shadow-sm" style="border-left: 4px solid #dc3545 !important;">
                <div class="card-header bg-danger text-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-exclamation-triangle me-2"></i>Trackings Vencidos
                    </h6>
                    <span class="badge bg-white text-danger fw-bold">{{ $trackingsVencidos }}</span>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger border-0" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>¡Atención!</strong> Hay {{ $trackingsVencidos }} tracking(s) que han vencido su tiempo límite. 
                        <a href="{{ route('tracking.index', ['estado' => 'vencido']) }}" class="alert-link">Ver todos los vencidos</a>
                    </div>
                    
                    @if($trackingsVencidosList->count() > 0)
                    <div class="row">
                        @foreach($trackingsVencidosList->take(5) as $tracking)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card border-danger h-100" style="border-left: 4px solid #dc3545 !important;">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title text-danger mb-0">
                                            {{ $tracking->tracking_codigo }}
                                        </h6>
                                        <span class="badge bg-danger text-white">
                                            VENCIDO
                                        </span>
                                    </div>
                                    <p class="card-text small text-muted mb-2">
                                        <strong>Cliente:</strong> {{ $tracking->cliente->nombre_completo }}
                                    </p>
                                    <p class="card-text small text-danger mb-3">
                                        <strong>Venció:</strong> {{ \Carbon\Carbon::parse($tracking->recordatorio_fecha)->format('d/m/Y H:i') }}
                                        <br>
                                        <small>{{ \Carbon\Carbon::parse($tracking->recordatorio_fecha)->diffForHumans() }}</small>
                                    </p>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-primary" onclick="verTracking({{ $tracking->id }})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-success" onclick="marcarCompletado({{ $tracking->id }})">
                                            <i class="fas fa-check"></i> Completar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Próximos a Vencer -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold text-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>Próximos a Vencer (7 días)
                    </h6>
                    <span class="badge bg-warning text-dark" id="contadorProximos">0</span>
                </div>
                <div class="card-body">
                    <div id="proximosVencerList" class="row">
                        @forelse($proximosVencer as $tracking)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card border-warning h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title text-warning mb-0">
                                            {{ $tracking->tracking_codigo }}
                                        </h6>
                                        <span class="badge bg-warning text-dark">
                                            {{ \Carbon\Carbon::parse($tracking->recordatorio_fecha)->diffForHumans() }}
                                        </span>
                                    </div>
                                    <p class="card-text small text-muted mb-2">
                                        <strong>Cliente:</strong> {{ $tracking->cliente->nombre }}
                                    </p>
                                    <p class="card-text small text-muted mb-3">
                                        <strong>Vence:</strong> {{ \Carbon\Carbon::parse($tracking->recordatorio_fecha)->format('d/m/Y H:i') }}
                                    </p>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-primary" onclick="verTracking({{ $tracking->id }})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-success" onclick="marcarCompletado({{ $tracking->id }})">
                                            <i class="fas fa-check"></i> Completar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12 text-center text-muted py-4">
                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                            <p>No hay trackings próximos a vencer</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Temporizadores Activos -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-semibold text-info">
                        <i class="fas fa-stopwatch me-2"></i>Temporizadores Activos
                    </h6>
                </div>
                <div class="card-body">
                    <div id="temporizadoresActivos" class="row">
                        <!-- Los temporizadores se cargarán dinámicamente -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Estilos de inventario aplicados a dashboard de tracking -->
<style>
.card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
.bg-primary.bg-opacity-10, .bg-warning.bg-opacity-10, .bg-danger.bg-opacity-10, .bg-success.bg-opacity-10 {
    background-color: rgba(26,46,117,0.08) !important;
}
.card-title, .card-body h6, .card-header h6 {
    font-size: 0.98rem !important;
}
.btn, .btn-lg, .btn-primary, .btn-outline-secondary, .btn-outline-info, .btn-success {
    font-size: 0.97rem !important;
    border-radius: 0.75rem !important;
}
.badge {
    font-size: 0.75rem;
    padding: 0.5em 0.75em;
    border-radius: 0.75rem;
}
</style>

<!-- Modal para Detalles del Tracking -->
<div class="modal fade" id="trackingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles del Tracking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalContent">
                <!-- Contenido dinámico -->
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Variables globales
let temporizadores = {};

// Función para buscar tracking
function buscarTracking() {
    const codigo = document.getElementById('codigoTracking').value;
    if (!codigo) {
        mostrarAlerta('Por favor ingresa un código de tracking', 'warning');
        return;
    }

    fetch(`/tracking/buscar?codigo=${encodeURIComponent(codigo)}`)
        .then(response => response.json())
        .then(data => {
            const resultadoDiv = document.getElementById('resultadoBusqueda');
            
            if (data.success) {
                const tracking = data.tracking;
                resultadoDiv.innerHTML = `
                    <div class="alert alert-success">
                        <div class="row">
                            <div class="col-md-8">
                                <h6><strong>Código:</strong> ${tracking.tracking_codigo}</h6>
                                <p><strong>Cliente:</strong> ${tracking.cliente.nombre}</p>
                                <p><strong>Estado:</strong> <span class="badge bg-${getEstadoColor(tracking.estado)}">${tracking.estado}</span></p>
                                <p><strong>Vence:</strong> ${new Date(tracking.recordatorio_fecha).toLocaleString()}</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <button class="btn btn-primary btn-sm" onclick="verTracking(${tracking.id})">
                                    <i class="fas fa-eye me-1"></i>Ver Detalles
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                resultadoDiv.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarAlerta('Error al buscar el tracking', 'danger');
        });
}

// Función para cargar próximos a vencer
function cargarProximosVencer() {
    fetch('/tracking/proximos-vencer')
        .then(response => response.json())
        .then(data => {
            const contador = document.getElementById('contadorProximos');
            contador.textContent = data.length;
            
            // Actualizar la lista si es necesario
            if (data.length > 0) {
                // Aquí podrías actualizar la lista dinámicamente
                mostrarAlerta(`Se encontraron ${data.length} trackings próximos a vencer`, 'info');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Función para ver tracking
function verTracking(id) {
    window.location.href = `/tracking/${id}`;
}

// Función para marcar como completado
function marcarCompletado(id) {
    if (confirm('¿Estás seguro de que quieres marcar este tracking como completado?')) {
        fetch(`/tracking/${id}/completar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarAlerta('Tracking marcado como completado', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                mostrarAlerta('No se pudo completar el tracking', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarAlerta('Error al actualizar el estado', 'danger');
        });
    }
}

// Función para obtener color del estado
function getEstadoColor(estado) {
    switch (estado) {
        case 'pendiente': return 'warning';
        case 'en_proceso': return 'info';
        case 'completado': return 'success';
        case 'vencido': return 'danger';
        default: return 'secondary';
    }
}

// Función para mostrar alertas
function mostrarAlerta(mensaje, tipo) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${tipo} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.querySelector('.container-fluid').insertBefore(alertDiv, document.querySelector('.container-fluid').firstChild);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Función para crear temporizador
function crearTemporizador(trackingId, fechaVencimiento) {
    const ahora = new Date().getTime();
    const vencimiento = new Date(fechaVencimiento).getTime();
    const diferencia = vencimiento - ahora;
    
    if (diferencia <= 0) {
        return null; // Ya venció
    }
    
    const temporizador = setInterval(() => {
        const tiempoRestante = new Date(fechaVencimiento).getTime() - new Date().getTime();
        
        if (tiempoRestante <= 0) {
            clearInterval(temporizador);
            mostrarAlerta(`¡El tracking ${trackingId} ha vencido!`, 'danger');
            delete temporizadores[trackingId];
        } else {
            // Actualizar el display del temporizador si existe
            const displayElement = document.getElementById(`temporizador-${trackingId}`);
            if (displayElement) {
                const dias = Math.floor(tiempoRestante / (1000 * 60 * 60 * 24));
                const horas = Math.floor((tiempoRestante % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutos = Math.floor((tiempoRestante % (1000 * 60 * 60)) / (1000 * 60));
                const segundos = Math.floor((tiempoRestante % (1000 * 60)) / 1000);
                
                displayElement.innerHTML = `
                    <div class="text-center">
                        <div class="h4 text-danger">${dias}d ${horas}h ${minutos}m ${segundos}s</div>
                        <small class="text-muted">Tiempo restante</small>
                    </div>
                `;
            }
        }
    }, 1000);
    
    return temporizador;
}

// Inicializar temporizadores al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    // Cargar temporizadores para trackings próximos a vencer
    @foreach($proximosVencer as $tracking)
        const temporizador{{ $tracking->id }} = crearTemporizador({{ $tracking->id }}, '{{ $tracking->recordatorio_fecha }}');
        if (temporizador{{ $tracking->id }}) {
            temporizadores[{{ $tracking->id }}] = temporizador{{ $tracking->id }};
        }
    @endforeach
    
    // Actualizar contador de próximos a vencer
    document.getElementById('contadorProximos').textContent = {{ $proximosVencer->count() }};
});

// Limpiar temporizadores al salir de la página
window.addEventListener('beforeunload', function() {
    Object.values(temporizadores).forEach(temporizador => {
        clearInterval(temporizador);
    });
});
</script>
@endsection 