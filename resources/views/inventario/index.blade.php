@extends('layouts.app')

@section('title', 'Inventario - SkylinkOne CRM')
@section('page-title', 'Inventario de Paquetes')

@section('content')
{{-- Vista principal de inventario: lista, filtros y estadísticas --}}
<div class="container-fluid px-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="rounded-4 shadow-sm px-4 py-4 mb-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(90deg, #1A2E75 0%, #5C6AC4 100%); min-height:90px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width:60px; height:60px; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                        <i class="fas fa-boxes text-primary" style="font-size:2.2rem;"></i>
                    </div>
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="letter-spacing:1px;">Inventario de Paquetes</h1>
                        <p class="mb-0 text-white-50" style="font-size:1.1rem;">Gestiona todos los paquetes en el sistema</p>
                    </div>
                </div>
                <a href="{{ route('inventario.create') }}" class="btn btn-lg fw-semibold shadow-sm px-4" style="background:#1A2E75; color:#fff;">
                    <i class="fas fa-plus me-2"></i> Nuevo Paquete
                </a>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters Section -->
    <div class="collapse mb-4" id="filtersCollapse">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Buscar</label>
                        <input type="text" class="form-control" id="searchInput" placeholder="Buscar por guía, cliente...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Estado</label>
                        <select class="form-select" id="statusFilter">
                            <option value="">Todos</option>
                            <option value="recibido">Recibido</option>
                            <option value="entregado">Entregado</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Servicio</label>
                        <select class="form-select" id="serviceFilter">
                            <option value="">Todos</option>
                            <option value="express">Pie Cúbico</option>
                            <option value="estandar">Estándar</option>
                            <option value="economico">Económico</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Rango de Fechas</label>
                        <div class="input-group">
                            <input type="date" class="form-control" id="dateFrom">
                            <span class="input-group-text">a</span>
                            <input type="date" class="form-control" id="dateTo">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-outline-primary w-100" id="clearFilters">
                            <i class="fas fa-times me-1"></i>
                            Limpiar
                        </button>
                    </div>
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
                                <i class="fas fa-boxes text-primary fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-1">Total Paquetes</h6>
                            <h4 class="mb-0 fw-bold text-dark">{{ $totalPaquetes }}</h4>
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
                                <i class="fas fa-check-circle text-warning fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-1">Recibidos</h6>
                            <h4 class="mb-0 fw-bold text-dark">{{ $totalRecibidos }}</h4>
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
                            <h6 class="card-title text-muted mb-1">Entregados</h6>
                            <h4 class="mb-0 fw-bold text-dark">{{ $totalEntregados }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(auth()->check() && auth()->user()->rol === 'admin')
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                    <i class="fas fa-dollar-sign text-info fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="card-title text-muted mb-1">Valor Total</h6>
                                <h4 class="mb-0 fw-bold text-dark">${{ number_format($valorTotal, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Main Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold text-dark">
                    <i class="fas fa-list me-2 text-primary"></i>
                    Lista de Paquetes
                </h5>
                <div class="d-flex gap-2">
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog me-1"></i>
                            Acciones
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('inventario.export-excel') }}"><i class="fas fa-file-excel me-2 text-success"></i>Exportar Excel</a></li>
                            <li><a class="dropdown-item" href="#" onclick="printInventarioTable(); return false;"><i class="fas fa-print me-2 text-primary"></i>Imprimir</a></li>
                            <li><hr class="dropdown-divider"></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive-md">
                <div class="card mb-3 p-3 shadow-sm border-0" style="border-radius: 16px;">
                    <form class="row g-2 align-items-center flex-nowrap w-100" method="GET" action="{{ route('inventario.index') }}" style="gap:0.5rem 0;">
                        <div class="col-12 col-md-4 d-flex align-items-center">
                            <input type="text" name="busqueda" class="form-control rounded-3 filtro-sm" placeholder="Buscar cliente, tracking, guía..." value="{{ request('busqueda', $busqueda ?? '') }}">
                        </div>
                        <div class="col-6 col-md-3 d-flex align-items-center">
                            <select name="servicio_id" class="form-select form-select-lg rounded-3 filtro-sm">
                                <option value="">Todos los servicios</option>
                                @foreach($servicios as $s)
                                    <option value="{{ $s->id }}" {{ request('servicio_id', $servicio_id ?? '') == $s->id ? 'selected' : '' }}>{{ $s->tipo_servicio }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3 d-flex align-items-center">
                            <select name="estado" class="form-select form-select-lg rounded-3 filtro-sm">
                                <option value="">Todos los estados</option>
                                <option value="recibido" {{ request('estado', $estado ?? '') == 'recibido' ? 'selected' : '' }}>Recibido</option>
                                <option value="entregado" {{ request('estado', $estado ?? '') == 'entregado' ? 'selected' : '' }}>Entregado</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-2 d-flex gap-2 align-items-center">
                            <button type="submit" class="btn btn-primary px-4 filtro-sm d-flex align-items-center justify-content-center" style="height:48px;width:48px;"><i class="fas fa-search"></i></button>
                            <a href="{{ route('inventario.index') }}" class="btn btn-outline-secondary px-3 filtro-sm d-flex align-items-center justify-content-center" style="height:48px;width:48px;"><i class="fas fa-eraser"></i></a>
                        </div>
                    </form>
                </div>
                <table class="table inventario-table table-hover align-middle mb-0" id="inventarioTable">
                    <thead class="table-light align-middle">
                        <tr>
                            <th class="text-nowrap align-middle" style="min-width:120px;"><span class="d-inline-flex align-items-center"><i class="fas fa-user me-2"></i>Cliente</span></th>
                            <th class="text-nowrap align-middle" style="min-width:90px;"><span class="d-inline-flex align-items-center"><i class="fas fa-shipping-fast me-2"></i>Servicio</span></th>
                            <th class="text-nowrap align-middle" style="min-width:70px;"><span class="d-inline-flex align-items-center"><i class="fas fa-balance-scale me-2"></i>Peso</span></th>
                            <th class="text-nowrap align-middle" style="min-width:90px;"><span class="d-inline-flex align-items-center"><i class="fas fa-barcode me-2"></i>Warehouse</span></th>
                            <th class="text-nowrap align-middle" style="min-width:90px;"><span class="d-inline-flex align-items-center"><i class="fas fa-info-circle me-2"></i>Estado</span></th>
                            <th class="text-nowrap align-middle" style="min-width:90px;"><span class="d-inline-flex align-items-center"><i class="fas fa-calendar-alt me-2"></i>Ingreso</span></th>
                            <th class="text-nowrap align-middle" style="min-width:80px;"><span class="d-inline-flex align-items-center"><i class="fas fa-dollar-sign me-2"></i>Monto</span></th>
                            <th class="text-nowrap align-middle" style="min-width:70px;"><span class="d-inline-flex align-items-center"><i class="fas fa-dollar-sign me-2"></i>P. Unit.</span></th>
                            <th class="text-nowrap align-middle" style="min-width:80px;"><span class="d-inline-flex align-items-center"><i class="fas fa-cogs me-2"></i>Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($inventarios as $item)
                            <tr class="align-middle">
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                            <i class="fas fa-user text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $item->cliente->nombre_completo }}</div>
                                            <small class="text-muted d-none d-lg-block">{{ $item->cliente->correo ?? 'Sin email' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-shipping-fast me-1"></i>
                                        {{ $item->servicio->tipo_servicio ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="fw-semibold">{{ number_format($item->peso_lb, 2) }} lb</div>
                                </td>
                                <td class="px-4 py-3">
                                    <code class="bg-light px-2 py-1 rounded">{{ $item->numero_guia }}</code>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $statusColors = [
                                            'recibido' => 'skylink-orange',
                                            'en_transito' => 'warning',
                                            'entregado' => 'skylink-green',
                                            'pendiente' => 'secondary'
                                        ];
                                        $statusIcons = [
                                            'recibido' => 'check-circle',
                                            'en_transito' => 'truck',
                                            'entregado' => 'box-open',
                                            'pendiente' => 'clock'
                                        ];
                                        $color = $statusColors[$item->estado] ?? 'secondary';
                                        $icon = $statusIcons[$item->estado] ?? 'question-circle';
                                    @endphp
                                    <span class="badge badge-estado-fixed @if($item->estado=='recibido') badge-skylink-orange @elseif($item->estado=='entregado') badge-skylink-green @else bg-{{ $color }} bg-opacity-10 text-{{ $color }} border border-{{ $color }} @endif">
                                        <i class="fas fa-{{ $icon }} me-1"></i>
                                        {{ ucfirst(str_replace('_', ' ', $item->estado)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 d-none d-md-table-cell">
                                    <div class="text-muted">
                                        {{ \Carbon\Carbon::parse($item->fecha_ingreso)->format('d/m/Y') }}
                                    </div>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($item->fecha_ingreso)->diffForHumans() }}
                                    </small>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="fw-bold text-success">${{ number_format($item->monto_calculado, 2) }}</div>
                                </td>
                                <td class="px-4 py-3 d-none d-md-table-cell">
                                    ${{ number_format($item->tarifa_manual ?? ($item->tarifa ?? 1.00), 2) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('inventario.edit', $item->id) }}" 
                                           class="btn btn-inv-action btn-inv-edit" 
                                           data-bs-toggle="tooltip" 
                                           title="Editar paquete">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('inventario.show', $item->id) }}" 
                                           class="btn btn-inv-action btn-inv-view" 
                                           data-bs-toggle="tooltip" 
                                           title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @php $user = Auth::user(); @endphp
                                        @if($user && $user->rol === 'admin')
                                        <button type="button" 
                                                class="btn btn-inv-action btn-inv-delete" 
                                                onclick="confirmDelete({{ $item->id }})"
                                                data-bs-toggle="tooltip" 
                                                title="Eliminar paquete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-box-open fa-3x mb-3"></i>
                                        <h5>No hay paquetes registrados</h5>
                                        <p>Comienza agregando tu primer paquete al inventario</p>
                                        <a href="{{ route('inventario.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus me-1"></i>
                                            Registrar Paquete
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
    @if($inventarios->hasPages())
        <div class="d-flex justify-content-center">
            {{ $inventarios->links('vendor.pagination.custom') }}
        </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar este paquete? Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>
                        Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
.card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.table tbody tr {
    transition: background-color 0.2s ease-in-out;
}

.table tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05) !important;
}

.btn-group .btn {
    border-radius: 0.375rem !important;
    margin: 0 1px;
}

.badge {
    font-size: 0.75rem;
    padding: 0.5em 0.75em;
}

.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.alert {
    border: none;
    border-radius: 0.5rem;
}

.navbar-brand {
    font-weight: 700;
    font-size: 1.5rem;
}

@media (max-width: 768px) {
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin: 1px 0;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
}

.inventario-table {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(26,46,117,0.04);
    border-collapse: separate;
    border-spacing: 0;
}
.inventario-table th, .inventario-table td {
    padding: 0.55rem 0.5rem !important;
    font-size: 0.98rem;
    white-space: nowrap;
    vertical-align: middle !important;
}
.inventario-table th {
    font-size: 1.01rem;
    font-weight: 600;
    background: #1A2E75 !important;
    color: #fff !important;
    border-bottom: 3px solid #5C6AC4;
}
.inventario-table th .fa {
    color: #fff !important;
    opacity: 0.92;
}
.inventario-table td {
    vertical-align: middle;
}
.inventario-table thead th span {
    display: flex;
    align-items: center;
    gap: 0.35em;
    justify-content: flex-start;
}
.card.mb-3.p-3 {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(26,46,117,0.07);
    margin-bottom: 1.5rem;
}
.card.mb-3.p-3 form .filtro-sm {
    font-size: 0.97rem !important;
    padding-top: 0.5rem !important;
    padding-bottom: 0.5rem !important;
    height: 48px !important;
    box-shadow: none !important;
}
.card.mb-3.p-3 form .form-select.filtro-sm, .card.mb-3.p-3 form .form-control.filtro-sm {
    min-width: 0;
    width: 100%;
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: visible;
    height: 48px !important;
}
.card.mb-3.p-3 form .btn.filtro-sm {
    height: 48px !important;
    width: 48px !important;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    padding: 0 !important;
}
.card.mb-3.p-3 form .input-group-text {
    display: none !important;
}
.card.mb-3.p-3 form .d-flex.align-items-center {
    margin-bottom: 0 !important;
}
@media (max-width: 992px) {
    .card.mb-3.p-3 form .filtro-sm {
        font-size: 0.93rem !important;
        height: 42px !important;
    }
    .card.mb-3.p-3 form .form-select.filtro-sm, .card.mb-3.p-3 form .form-control.filtro-sm {
        height: 42px !important;
    }
    .card.mb-3.p-3 form .btn.filtro-sm {
        height: 42px !important;
        width: 42px !important;
    }
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
.badge-skylink-orange {
    background: #F59E0B !important;
    color: #fff !important;
    border: 1.5px solid #F59E0B !important;
}
.badge-skylink-green {
    background: #10B981 !important;
    color: #fff !important;
    border: 1.5px solid #10B981 !important;
}
.badge-estado-fixed {
    min-width: 120px;
    max-width: 120px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.98rem;
    font-weight: 600;
    padding: 0.45em 0;
    border-radius: 8px !important;
    text-align: center;
    gap: 0.4em;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('inventarioTable');
    const rows = table.getElementsByTagName('tr');

    searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        
        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const text = row.textContent.toLowerCase();
            
            if (text.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });

    // Status filter
    const statusFilter = document.getElementById('statusFilter');
    statusFilter.addEventListener('change', function() {
        const selectedStatus = this.value.toLowerCase();
        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const statusCell = row.querySelector('td:nth-child(6)');
            if (statusCell) {
                const status = statusCell.textContent.toLowerCase();
                if (!selectedStatus || status.includes(selectedStatus)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }
    });

    // Clear filters
    document.getElementById('clearFilters').addEventListener('click', function() {
        searchInput.value = '';
        statusFilter.value = '';
        document.getElementById('serviceFilter').value = '';
        document.getElementById('dateFrom').value = '';
        document.getElementById('dateTo').value = '';
        
        // Show all rows
        for (let i = 1; i < rows.length; i++) {
            rows[i].style.display = '';
        }
    });
});

function confirmDelete(id) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const form = document.getElementById('deleteForm');
    form.action = `/inventario/${id}`;
    modal.show();
}

// Imprimir solo la tabla de inventario
function printInventarioTable() {
    const table = document.getElementById('inventarioTable').outerHTML;
    const win = window.open('', '', 'width=900,height=700');
    win.document.write(`
        <html>
        <head>
            <title>Imprimir Inventario</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <style>
                body { background: #f4f6fb; padding: 2rem; }
                table { font-size: 0.98rem; }
                th, td { padding: 0.55rem 0.5rem !important; }
                th { background: #1A2E75 !important; color: #fff !important; }
                .table { border-radius: 16px; overflow: hidden; }
            </style>
        </head>
        <body>
            <h2 style="color:#1A2E75; font-weight:700; margin-bottom:1.5rem;">Inventario de Paquetes</h2>
            ${table}
        </body>
        </html>
    `);
    win.document.close();
    win.focus();
    setTimeout(() => win.print(), 500);
}

document.getElementById('printBtn').addEventListener('click', printInventarioTable);
</script>
@endsection
