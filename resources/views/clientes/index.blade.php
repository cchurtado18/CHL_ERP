@extends('layouts.app')

@section('title', 'Clientes - SkylinkOne CRM')
@section('page-title', 'Clientes')

@section('content')
<div class="container-fluid px-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="rounded-4 shadow-sm px-4 py-4 mb-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(90deg, #1A2E75 0%, #5C6AC4 100%); min-height:90px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width:60px; height:60px; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                        <i class="fas fa-users text-primary" style="font-size:2.2rem;"></i>
                    </div>
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="letter-spacing:1px;">Clientes</h1>
                        <p class="mb-0 text-white-50" style="font-size:1.1rem;">Gestión de clientes registrados en el sistema</p>
                    </div>
                </div>
                <a href="{{ route('clientes.create') }}" class="btn btn-lg fw-semibold shadow-sm px-4" style="background:#1A2E75; color:#fff; border-radius:12px; font-size:1.15rem;"><i class="fas fa-plus me-2"></i> Nuevo Cliente</a>
            </div>
        </div>
    </div>
    <div class="card p-4" style="border-radius:22px; box-shadow:0 2px 8px rgba(26,46,117,0.06);">
        <form class="row mb-3 align-items-center" method="GET" action="{{ route('clientes.index') }}">
            <div class="col-md-6 mb-2 mb-md-0">
                <input type="text" id="busqueda_cliente" name="busqueda" class="form-control form-control-lg rounded-3" placeholder="Buscar por nombre, correo o teléfono..." value="{{ request('busqueda', $busqueda ?? '') }}">
            </div>
            <div class="col-md-3">
                <select id="filtro_tipo_cliente" name="tipo" class="form-select form-select-lg rounded-3">
                    <option value="" {{ !request('tipo', $tipo ?? '') ? 'selected' : '' }}>Todos los tipos</option>
                    <option value="normal" {{ request('tipo', $tipo ?? '') == 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="Subagencia" {{ request('tipo', $tipo ?? '') == 'Subagencia' ? 'selected' : '' }}>Subagencia</option>
                </select>
            </div>
            <div class="col-md-3 d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary px-4"><i class="fas fa-search me-1"></i>Buscar</button>
                <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary px-4"><i class="fas fa-eraser me-1"></i>Limpiar</a>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table clientes-table table-hover align-middle mb-0" style="border-radius:16px; overflow:hidden;" id="tabla_clientes">
                <thead class="table-primary">
                    <tr>
                        <th>Nombre completo</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Tipo</th>
                        <th>Fecha registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clientes as $cliente)
                        <tr data-nombre="{{ strtolower($cliente->nombre_completo) }}" data-correo="{{ strtolower($cliente->correo) }}" data-telefono="{{ strtolower($cliente->telefono) }}" data-tipo="{{ strtolower($cliente->tipo_cliente) }}">
                            <td class="fw-semibold" style="font-size:1.08rem;">{{ $cliente->nombre_completo }}</td>
                            <td style="font-size:1.07rem;">{{ $cliente->correo }}</td>
                            <td style="font-size:1.07rem;">{{ $cliente->telefono }}</td>
                            <td>
                                @php $tipo = strtolower(trim($cliente->tipo_cliente)); @endphp
                                @if($tipo === 'subagencia')
                                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill" style="font-size:1.01rem; letter-spacing:0.5px; background:#FFD700;">Subagencia</span>
                                @else
                                    <span class="badge bg-primary text-white px-3 py-2 rounded-pill" style="font-size:1.01rem; letter-spacing:0.5px; background:#1A2E75;">Normal</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold" style="font-size:1.08rem;">{{ \Carbon\Carbon::parse($cliente->fecha_registro)->format('Y-m-d') }}</div>
                                <div class="text-muted" style="font-size:0.95rem;">{{ \Carbon\Carbon::parse($cliente->fecha_registro)->format('H:i:s') }}</div>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('clientes.show', $cliente->id) }}" class="btn btn-info btn-client-action me-1" title="Previsualizar"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn btn-primary btn-client-action me-1" title="Editar"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-client-action" onclick="return confirm('¿Eliminar cliente?')" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">No hay clientes registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($clientes->hasPages())
        <div class="d-flex justify-content-center">
            {{ $clientes->links('vendor.pagination.custom') }}
        </div>
    @endif
</div>
<style>
    .clientes-table thead th {
        background: #1A2E75 !important;
        color: #fff !important;
        border-radius: 0 !important;
        border-bottom: 3px solid #5C6AC4;
        font-weight: 600;
        letter-spacing: 0.5px;
        border-right: 1px solid #e3e6f0 !important;
        font-size: 1rem;
    }
    .clientes-table thead th:last-child {
        border-right: none !important;
    }
    .clientes-table thead tr {
        border-radius: 0 !important;
    }
    .clientes-table {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(26,46,117,0.04);
    }
    .clientes-table tbody tr {
        background: #fff;
        transition: background 0.2s, box-shadow 0.2s;
        box-shadow: 0 1px 4px rgba(26,46,117,0.03);
    }
    .clientes-table tbody td {
        border-right: 1px solid #e3e6f0 !important;
        font-size: 0.97rem;
    }
    .clientes-table tbody td:last-child {
        border-right: none !important;
    }
    .clientes-table tbody tr:hover {
        background: #F5F7FA !important;
        box-shadow: 0 2px 8px rgba(26,46,117,0.08);
    }
    .btn-client-action {
        border-radius: 10px !important;
        min-width: 34px;
        min-height: 34px;
        padding: 0 10px;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        box-shadow: none;
        transition: background 0.15s, color 0.15s;
    }
    .btn-info.btn-client-action {
        background: #00B6E5;
        color: #fff;
    }
    .btn-primary.btn-client-action {
        background: #1A2E75;
        color: #fff;
    }
    .btn-danger.btn-client-action {
        background: #BF1E2E;
        color: #fff;
    }
    .btn-client-action:hover, .btn-client-action:focus {
        opacity: 0.92;
        color: #fff;
    }
    .card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        border-radius: 22px;
    }
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    .badge.bg-primary {
        background: #1A2E75 !important;
        color: #fff !important;
        font-size: 0.97rem;
        padding: 0.45em 1.1em;
    }
    .badge.bg-accent {
        background: #5C6AC4 !important;
        color: #fff !important;
        font-size: 0.97rem;
        padding: 0.45em 1.1em;
    }
    #busqueda_cliente, #filtro_tipo_cliente {
        font-size: 1rem !important;
        padding: 0.6rem 1rem !important;
        border-radius: 8px !important;
    }
</style>

<!-- Estilos de paginación igual a inventario -->
<style>
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
@endsection
