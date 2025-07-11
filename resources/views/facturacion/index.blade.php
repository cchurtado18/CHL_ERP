@extends('layouts.app')

@section('title', 'Facturación - SkylinkOne CRM')
@section('page-title', 'Facturación')

@section('content')
{{-- Vista principal de facturación: lista, filtros y acciones --}}
<div class="container-fluid px-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="rounded-4 shadow-sm px-4 py-4 mb-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(90deg, #1A2E75 0%, #5C6AC4 100%); min-height:90px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width:60px; height:60px; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                        <i class="fas fa-file-invoice-dollar text-primary" style="font-size:2.2rem;"></i>
                    </div>
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="letter-spacing:1px;">Facturación</h1>
                        <p class="mb-0 text-white-50" style="font-size:1.1rem;">Gestiona todas las facturas del sistema</p>
                    </div>
                </div>
                <a href="{{ route('facturacion.create') }}" class="btn btn-lg fw-semibold shadow-sm px-4" style="background:#1A2E75; color:#fff;">
                    <i class="fas fa-plus me-2"></i> Nueva Factura
                </a>
            </div>
        </div>
    </div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="card p-3">
        {{-- Filtros de búsqueda por cliente, fecha, acta y estado --}}
        <form method="GET" class="row g-2 mb-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Cliente</label>
                <input type="text" name="cliente" value="{{ request('cliente') }}" class="form-control" placeholder="Buscar cliente...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha</label>
                <input type="date" name="fecha" value="{{ request('fecha') }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Acta</label>
                <input type="text" name="acta" value="{{ request('acta') }}" class="form-control" placeholder="N° de acta">
            </div>
            <div class="col-md-2">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="entregado_pagado" {{ request('estado')=='entregado_pagado'?'selected':'' }}>Entregado y Pagado</option>
                    <option value="entregado_sin_pagar" {{ request('estado')=='entregado_sin_pagar'?'selected':'' }}>Entregado sin Pagar</option>
                    <option value="pagado_sin_entregar" {{ request('estado')=='pagado_sin_entregar'?'selected':'' }}>Pagado sin Entregar</option>
                    <option value="facturado_npne" {{ request('estado')=='facturado_npne'?'selected':'' }}>Facturado NPNE</option>
                </select>
            </div>
            <div class="col-md-3 filter-btn-group">
                <button class="btn-filter" type="submit"><i class="fas fa-search"></i> Filtrar</button>
                <a href="{{ route('facturacion.index') }}" class="btn-clear"><i class="fas fa-eraser"></i> Limpiar filtros</a>
            </div>
        </form>
        <div class="table-responsive">
            <style>
                .fact-table thead th {
                    background: #1A2E75 !important;
                    color: #fff !important;
                    border-radius: 0 !important;
                    border-bottom: 3px solid #5C6AC4;
                    font-weight: 600;
                    letter-spacing: 0.5px;
                }
                .fact-table thead tr {
                    border-radius: 0 !important;
                }
                .fact-table {
                    border-radius: 12px;
                    overflow: hidden;
                    box-shadow: 0 2px 8px rgba(26,46,117,0.04);
                }
                .fact-table tbody tr {
                    background: #fff;
                    transition: background 0.2s;
                }
                .fact-table tbody tr:hover {
                    background: #F5F7FA !important;
                }
                .btn-fact {
                    border-radius: 8px !important;
                    min-width: 38px;
                    min-height: 38px;
                    padding: 0 12px;
                    font-size: 1.1rem;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    border: none;
                    box-shadow: none;
                    transition: background 0.15s;
                }
                .btn-fact-view {
                    background: #1A2E75;
                    color: #fff;
                }
                .btn-fact-pdf {
                    background: #5C6AC4;
                    color: #fff;
                }
                .btn-fact-delete {
                    background: #BF1E2E;
                    color: #fff;
                }
                .btn-fact:hover, .btn-fact:focus {
                    opacity: 0.92;
                    color: #fff;
                }
                .btn-filter {
                    background: #1A2E75;
                    color: #fff;
                    border-radius: 8px;
                    border: none;
                    min-width: 120px;
                    min-height: 42px;
                    font-weight: 600;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;
                    font-size: 1rem;
                    box-shadow: none;
                    transition: background 0.15s;
                }
                .btn-filter:hover, .btn-filter:focus {
                    background: #223a7a;
                    color: #fff;
                }
                .btn-clear {
                    background: #F5F7FA;
                    color: #1A2E75;
                    border: 1.5px solid #1A2E75;
                    border-radius: 8px;
                    min-width: 140px;
                    min-height: 42px;
                    font-weight: 600;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;
                    font-size: 1rem;
                    box-shadow: none;
                    transition: background 0.15s;
                    text-decoration: none !important;
                }
                .btn-clear:hover, .btn-clear:focus {
                    background: #e9ecef;
                    color: #1A2E75;
                    border-color: #223a7a;
                }
                .filter-btn-group {
                    display: flex;
                    gap: 10px;
                    align-items: center;
                    justify-content: flex-end;
                }
            </style>
            {{-- Tabla de facturas con acciones --}}
            <table class="table fact-table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Acta</th>
                        <th>Monto Total</th>
                        <th>Moneda</th>
                        <th>Estado</th>
                        <th>Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($facturas as $factura)
                        <tr>
                            <td>{{ $factura->id }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $factura->cliente->nombre_completo ?? 'N/D' }}</div>
                                <small class="text-muted d-none d-lg-block">{{ $factura->cliente->correo ?? 'Sin email' }}</small>
                            </td>
                            <td>{{ $factura->fecha_factura }}</td>
                            <td>{{ $factura->numero_acta }}</td>
                            <td><span class="fw-bold">${{ number_format($factura->monto_total, 2) }}</span></td>
                            <td>{{ $factura->moneda }}</td>
                            <td>
                                <form method="POST" action="{{ route('facturacion.cambiar-estado', $factura->id) }}" class="d-inline">
                                    @csrf
                                    <select name="estado_pago" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()" @if($factura->estado_pago=='entregado_pagado') disabled @endif>
                                        <option value="entregado_pagado" {{ $factura->estado_pago=='entregado_pagado'?'selected':'' }}>Entregado y Pagado</option>
                                        <option value="entregado_sin_pagar" {{ $factura->estado_pago=='entregado_sin_pagar'?'selected':'' }}>Entregado sin Pagar</option>
                                        <option value="pagado_sin_entregar" {{ $factura->estado_pago=='pagado_sin_entregar'?'selected':'' }}>Pagado sin Entregar</option>
                                        <option value="facturado_npne" {{ $factura->estado_pago=='facturado_npne'?'selected':'' }}>Facturado NPNE</option>
                                    </select>
                                </form>
                            </td>
                            <td class="d-flex gap-2">
                                <a href="{{ route('facturacion.preview', $factura->id) }}" class="btn-fact btn-fact-view" title="Previsualizar" target="_blank"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('facturacion.pdf', $factura->id) }}" class="btn-fact btn-fact-pdf" title="Descargar PDF" target="_blank"><i class="fas fa-file-pdf"></i></a>
                                <button type="button" class="btn-fact btn-fact-mail {{ $factura->cliente && $factura->cliente->correo ? '' : 'disabled' }}" title="Enviar por correo" data-bs-toggle="modal" data-bs-target="#modalEnviarCorreo" data-factura-id="{{ $factura->id }}" data-cliente-correo="{{ $factura->cliente->correo ?? '' }}" {{ $factura->cliente && $factura->cliente->correo ? '' : 'disabled' }}>
                                    <i class="fas fa-envelope"></i>
                                </button>
                                @php $user = Auth::user(); @endphp
                                @if($user && $user->rol === 'admin')
                                <form action="{{ route('facturacion.destroy', $factura->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta factura?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-fact btn-fact-delete" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">No hay facturas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($facturas->hasPages())
            <div class="d-flex justify-content-center">
                {{ $facturas->links('vendor.pagination.custom') }}
            </div>
        @endif
    </div>
</div>

<!-- Estilos de animación y paginación igual a inventario -->
<style>
.card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
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
</style>

<!-- Modal para confirmar envío de correo -->
<div class="modal fade" id="modalEnviarCorreo" tabindex="-1" aria-labelledby="modalEnviarCorreoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEnviarCorreoLabel"><i class="fas fa-envelope me-2 text-primary"></i>Enviar factura por correo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p>¿Deseas enviar la factura a <span id="correoClienteModal" class="fw-bold text-primary"></span>?</p>
      </div>
      <div class="modal-footer">
        <form id="formEnviarCorreo" method="POST" action="">
          @csrf
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Enviar</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var modal = document.getElementById('modalEnviarCorreo');
  modal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var facturaId = button.getAttribute('data-factura-id');
    var correo = button.getAttribute('data-cliente-correo');
    document.getElementById('correoClienteModal').textContent = correo;
    var form = document.getElementById('formEnviarCorreo');
    form.action = '/facturacion/' + facturaId + '/enviar-correo';
  });
});
</script>
@endsection
