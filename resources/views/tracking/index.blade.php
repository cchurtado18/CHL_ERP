@extends('layouts.app')

@section('title', 'Lista de Trackings - SkylinkOne CRM')

@section('content')
<div class="container-fluid px-4">
    <!-- Botón Volver al Dashboard -->
    <div class="mb-3">
        <a href="{{ route('tracking.dashboard') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 px-4 py-2 fw-semibold shadow-sm" style="font-size:1.01em; border-radius:0.75rem;">
            <i class="fas fa-arrow-left me-1"></i> Volver al Dashboard
        </a>
    </div>
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="rounded-4 shadow-sm px-4 py-4 mb-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(90deg, #1A2E75 0%, #5C6AC4 100%); min-height:90px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width:60px; height:60px; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                        <i class="fas fa-clock text-primary" style="font-size:2.2rem;"></i>
                    </div>
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="letter-spacing:1px; font-size:1.15em;">Lista de Trackings</h1>
                        <p class="mb-0 text-white-50" style="font-size:1.01em;">Gestión completa de seguimientos y temporizadores</p>
                    </div>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <a href="{{ route('tracking.create') }}" class="btn btn-primary d-flex align-items-center gap-2 px-4 py-2 fw-semibold shadow-sm" style="font-size:1.01em; border-radius:0.75rem;">
                        <i class="fas fa-plus me-1"></i> Nuevo Tracking
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filtros -->
    <div class="collapse mb-4" id="filtersCollapse">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Estado</label>
                        <select id="filtroEstado" class="form-select">
                            <option value="">Todos los estados</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="en_proceso">En Proceso</option>
                            <option value="completado">Completado</option>
                            <option value="vencido">Vencido</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Cliente</label>
                        <select id="filtroCliente" class="form-select">
                            <option value="">Todos los clientes</option>
                            @foreach($trackings->pluck('cliente.nombre')->unique() as $nombreCliente)
                                @if($nombreCliente)
                                    <option value="{{ $nombreCliente }}">{{ $nombreCliente }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Fecha de Vencimiento</label>
                        <select id="filtroFecha" class="form-select">
                            <option value="">Todas las fechas</option>
                            <option value="hoy">Vence hoy</option>
                            <option value="semana">Vence esta semana</option>
                            <option value="mes">Vence este mes</option>
                            <option value="vencido">Ya venció</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-outline-primary w-100" onclick="aplicarFiltros()">
                            <i class="fas fa-search me-1"></i>
                            Aplicar Filtros
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold text-dark">
                    <i class="fas fa-list me-2 text-primary"></i>
                    Lista de Trackings ({{ $trackings->total() }})
                </h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm" onclick="exportarTrackings()">
                        <i class="fas fa-download me-1"></i>
                        Exportar
                    </button>
                    <button class="btn btn-outline-warning btn-sm" onclick="verificarRecordatorios()">
                        <i class="fas fa-sync me-1"></i>
                        Verificar Recordatorios
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table inventario-table table-hover align-middle mb-0" id="trackingTable">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 px-4 py-3 fw-semibold text-dark">Código</th>
                            <th class="border-0 px-4 py-3 fw-semibold text-dark">Cliente</th>
                            <th class="border-0 px-4 py-3 fw-semibold text-dark">Estado</th>
                            <th class="border-0 px-4 py-3 fw-semibold text-dark">Temporizador</th>
                            <th class="border-0 px-4 py-3 fw-semibold text-dark">Vence</th>
                            <th class="border-0 px-4 py-3 fw-semibold text-dark">Creado por</th>
                            <th class="border-0 px-4 py-3 fw-semibold text-dark text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trackings as $tracking)
                        <tr class="tracking-row align-middle" 
                            data-estado="{{ $tracking->estado }}"
                            data-cliente="{{ $tracking->cliente->nombre ?? '' }}"
                            data-fecha="{{ $tracking->recordatorio_fecha }}">
                            <td class="px-4 py-3">
                                <div class="fw-bold text-primary">{{ $tracking->tracking_codigo }}</div>
                                @if($tracking->nota)
                                    <small class="text-muted">{{ Str::limit($tracking->nota, 50) }}</small>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="fw-medium">{{ $tracking->cliente->nombre_completo ?? 'Cliente no encontrado' }}</div>
                                <small class="text-muted">{{ $tracking->cliente->correo ?? '' }}</small>
                            </td>
                            <td class="px-4 py-3">
                                <span class="badge
                                    @switch($tracking->estado)
                                        @case('pendiente') bg-warning text-dark @break
                                        @case('en_proceso') bg-info text-dark @break
                                        @case('completado') bg-success @break
                                        @case('vencido') bg-danger @break
                                        @case('cancelado') bg-secondary @break
                                        @default bg-secondary
                                    @endswitch
                                ">
                                    {{ ucfirst($tracking->estado) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div id="temporizador-{{ $tracking->id }}" class="temporizador-display">
                                    @if($tracking->recordatorio_fecha && $tracking->estado != 'completado')
                                        @php
                                            $vencimiento = \Carbon\Carbon::parse($tracking->recordatorio_fecha);
                                        @endphp
                                        @if($vencimiento->isFuture())
                                            <div class="text-warning">
                                                <i class="fas fa-clock me-1"></i>
                                                <span class="temporizador-text" data-fecha="{{ $tracking->recordatorio_fecha }}">
                                                    {{ $vencimiento->diffForHumans() }}
                                                </span>
                                            </div>
                                        @else
                                            <div class="text-danger">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                Vencido
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="small">
                                    @if($tracking->creador)
                                        {{ $tracking->creador->name }}
                                    @elseif($tracking->creado_por)
                                        ID: {{ $tracking->creado_por }}
                                    @else
                                        <span class="text-muted">Sin información</span>
                                    @endif
                                </div>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($tracking->created_at)->format('d/m/Y') }}
                                </small>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('tracking.show', $tracking) }}" 
                                       class="btn btn-inv-action btn-inv-view" 
                                       data-bs-toggle="tooltip" 
                                       title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('tracking.edit', $tracking) }}" 
                                       class="btn btn-inv-action btn-inv-edit" 
                                       data-bs-toggle="tooltip" 
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-inv-action btn-inv-delete" 
                                            onclick="eliminarTracking({{ $tracking->id }})"
                                            data-bs-toggle="tooltip" 
                                            title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <h5>No hay trackings disponibles</h5>
                                    <a href="{{ route('tracking.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i>
                                        Crear Primer Tracking
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($trackings->hasPages())
        <div class="d-flex justify-content-center">
            {{ $trackings->links('vendor.pagination.custom') }}
        </div>
    @endif
</div>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Estilos de inventario aplicados a tracking -->
<style>
.card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
.inventario-table {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(26,46,117,0.04);
    border-collapse: separate;
    border-spacing: 0;
}
.inventario-table thead th {
    background: #1A2E75 !important;
    color: #fff !important;
    font-weight: 600;
    letter-spacing: 0.5px;
    border: none !important;
    padding: 12px 14px !important;
    font-size: 1.05rem;
    vertical-align: middle;
    white-space: nowrap;
}
.inventario-table thead th:first-child {
    border-top-left-radius: 16px;
}
.inventario-table thead th:last-child {
    border-top-right-radius: 16px;
}
.inventario-table tbody tr {
    background: #fff;
    transition: background 0.2s;
    border-bottom: 1.5px solid #e3e6f0;
}
.inventario-table tbody td {
    border: none !important;
    padding: 10px 14px !important;
    vertical-align: middle !important;
    font-size: 1.01rem;
}
.inventario-table tbody tr:hover {
    background: #F0F4FF !important;
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
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin: 32px 0 16px 0;
    padding: 0;
    list-style: none;
}
.pagination li {
    display: inline-block;
}
.pagination a, .pagination span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 38px;
    min-height: 38px;
    padding: 0 14px;
    border-radius: 8px;
    border: 1.5px solid #1A2E75;
    background: #fff;
    color: #1A2E75;
    font-weight: 600;
    font-size: 1.08rem;
    text-decoration: none !important;
    transition: background 0.15s, color 0.15s;
    margin: 0 2px;
}
.pagination .active span, .pagination a.active {
    background: #1A2E75;
    color: #fff;
    border-color: #1A2E75;
    cursor: default;
}
.pagination a:hover, .pagination a:focus {
    background: #5C6AC4;
    color: #fff;
    border-color: #5C6AC4;
}
.pagination .disabled span, .pagination .disabled a {
    color: #b0b0b0;
    background: #f5f7fa;
    border-color: #e3e6f0;
    cursor: not-allowed;
}
.pagination .page-arrow {
    font-size: 1.3rem;
    padding: 0 10px;
    min-width: 38px;
    min-height: 38px;
    border-radius: 8px;
    border: 1.5px solid #1A2E75;
    background: #fff;
    color: #1A2E75;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s, color 0.15s;
}
.pagination .page-arrow:hover, .pagination .page-arrow:focus {
    background: #5C6AC4;
    color: #fff;
    border-color: #5C6AC4;
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

<!-- Modal para cambiar estado -->
<div class="modal fade" id="estadoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cambiar Estado del Tracking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="estadoForm">
                    <div class="mb-3">
                        <label for="nuevoEstado" class="form-label">Nuevo Estado</label>
                        <select id="nuevoEstado" class="form-select" required>
                            <option value="">Selecciona un estado</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="en_proceso">En Proceso</option>
                            <option value="completado">Completado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmarCambioEstado()">Guardar Cambio</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
let trackingIdActual = null;

// Función para obtener color del estado
function getEstadoColor(estado) {
    switch (estado) {
        case 'pendiente': return 'warning';
        case 'en_proceso': return 'info';
        case 'completado': return 'success';
        case 'vencido': return 'danger';
        case 'cancelado': return 'secondary';
        default: return 'secondary';
    }
}

// Función para aplicar filtros
function aplicarFiltros() {
    const estado = document.getElementById('filtroEstado').value;
    const cliente = document.getElementById('filtroCliente').value;
    const fecha = document.getElementById('filtroFecha').value;
    
    const filas = document.querySelectorAll('.tracking-row');
    
    filas.forEach(fila => {
        let mostrar = true;
        
        // Filtro por estado
        if (estado && fila.dataset.estado !== estado) {
            mostrar = false;
        }
        
        // Filtro por cliente
        if (cliente && fila.dataset.cliente !== cliente) {
            mostrar = false;
        }
        
        // Filtro por fecha
        if (fecha && fila.dataset.fecha) {
            const fechaVencimiento = new Date(fila.dataset.fecha);
            const ahora = new Date();
            
            switch (fecha) {
                case 'hoy':
                    const inicioDia = new Date(ahora.getFullYear(), ahora.getMonth(), ahora.getDate());
                    const finDia = new Date(inicioDia.getTime() + 24 * 60 * 60 * 1000);
                    if (fechaVencimiento < inicioDia || fechaVencimiento >= finDia) {
                        mostrar = false;
                    }
                    break;
                case 'semana':
                    const inicioSemana = new Date(ahora.getTime() - ahora.getDay() * 24 * 60 * 60 * 1000);
                    const finSemana = new Date(inicioSemana.getTime() + 7 * 24 * 60 * 60 * 1000);
                    if (fechaVencimiento < inicioSemana || fechaVencimiento >= finSemana) {
                        mostrar = false;
                    }
                    break;
                case 'mes':
                    const inicioMes = new Date(ahora.getFullYear(), ahora.getMonth(), 1);
                    const finMes = new Date(ahora.getFullYear(), ahora.getMonth() + 1, 1);
                    if (fechaVencimiento < inicioMes || fechaVencimiento >= finMes) {
                        mostrar = false;
                    }
                    break;
                case 'vencido':
                    if (fechaVencimiento > ahora) {
                        mostrar = false;
                    }
                    break;
            }
        }
        
        fila.style.display = mostrar ? '' : 'none';
    });
}

// Función para cambiar estado
function cambiarEstado(trackingId) {
    trackingIdActual = trackingId;
    document.getElementById('nuevoEstado').value = '';
    new bootstrap.Modal(document.getElementById('estadoModal')).show();
}

// Función para confirmar cambio de estado
function confirmarCambioEstado() {
    const nuevoEstado = document.getElementById('nuevoEstado').value;
    
    if (!nuevoEstado) {
        alert('Por favor selecciona un estado');
        return;
    }
    
    fetch(`/tracking/${trackingIdActual}/actualizar-estado`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ estado: nuevoEstado })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error al actualizar el estado');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar el estado');
    });
}

// Función para eliminar tracking
function eliminarTracking(trackingId) {
    if (confirm('¿Estás seguro de que quieres eliminar este tracking? Esta acción no se puede deshacer.')) {
        fetch(`/tracking/${trackingId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (response.ok) {
                location.reload();
            } else {
                alert('Error al eliminar el tracking');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar el tracking');
        });
    }
}

// Función para exportar trackings
function exportarTrackings() {
    // Aquí podrías implementar la exportación a Excel/CSV
    alert('Función de exportación en desarrollo');
}

// Función para verificar recordatorios
function verificarRecordatorios() {
    fetch('/tracking/verificar-recordatorios')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al verificar recordatorios');
        });
}

// Actualizar temporizadores cada minuto
setInterval(() => {
    document.querySelectorAll('.temporizador-text').forEach(elemento => {
        const fecha = new Date(elemento.dataset.fecha);
        const ahora = new Date();
        const diferencia = fecha - ahora;
        
        if (diferencia > 0) {
            const dias = Math.floor(diferencia / (1000 * 60 * 60 * 24));
            const horas = Math.floor((diferencia % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutos = Math.floor((diferencia % (1000 * 60 * 60)) / (1000 * 60));
            
            if (dias > 0) {
                elemento.textContent = `${dias}d ${horas}h ${minutos}m`;
            } else if (horas > 0) {
                elemento.textContent = `${horas}h ${minutos}m`;
            } else {
                elemento.textContent = `${minutos}m`;
            }
        } else {
            elemento.textContent = 'Vencido';
            elemento.parentElement.className = 'text-danger';
        }
    });
}, 60000);

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    // Configurar event listeners para filtros
    document.getElementById('filtroEstado').addEventListener('change', aplicarFiltros);
    document.getElementById('filtroCliente').addEventListener('change', aplicarFiltros);
    document.getElementById('filtroFecha').addEventListener('change', aplicarFiltros);
});
</script>
@endsection 