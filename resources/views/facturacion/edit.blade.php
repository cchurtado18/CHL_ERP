@extends('layouts.app')

@section('title', 'Editar Factura - SkylinkOne CRM')
@section('page-title', '')

@section('content')
<div class="container-fluid px-4 pt-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="rounded-4 shadow-sm px-4 py-4 mb-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(90deg, #1A2E75 0%, #5C6AC4 100%); min-height:90px;">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('facturacion.index') }}" class="btn btn-lg fw-semibold shadow-sm px-4" style="background:#fff; color:#1A2E75; border:2px solid #1A2E75; box-shadow:0 2px 8px rgba(26,46,117,0.08); font-size:1.2rem;">
                        <i class="fas fa-arrow-left me-2"></i> Volver
                    </a>
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width:60px; height:60px; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                        <i class="fas fa-file-invoice-dollar text-primary" style="font-size:2.2rem;"></i>
                    </div>
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="letter-spacing:1px;">Editar Factura</h1>
                        <p class="mb-0 text-white-50" style="font-size:1.1rem;">Modifica los datos de la factura seleccionada</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <a class="nav-link position-relative" href="#" title="Notificaciones"><i class="fas fa-bell fa-lg text-white"></i></a>
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center text-white" href="#" id="userDropdownHeader" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle fa-lg me-1"></i> Usuario
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdownHeader">
                            <li><a class="dropdown-item" href="#">Perfil</a></li>
                            <li><a class="dropdown-item" href="#">Cerrar sesión</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card p-4">
                <div class="row g-0 align-items-stretch">
                    <div class="col-lg-6 p-3 border-end" style="min-width:320px;">
                        <form id="factura-form" action="{{ route('facturacion.update', $factura->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="cliente_id" class="form-label fw-semibold">Cliente</label>
                                <select id="cliente_id" name="cliente_id" class="form-select select2" required>
                                    <option value="">Seleccione un cliente</option>
                                    @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" {{ $factura->cliente_id == $cliente->id ? 'selected' : '' }}>{{ $cliente->nombre_completo }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="cliente_resumen" class="mb-3"></div>
                            <div id="paquetes_container" class="mb-3"></div>
                            <div id="facturas_historial" class="mb-3"></div>
                            <div class="mb-3">
                                <label for="delivery" class="form-label fw-semibold">Costo Delivery (opcional)</label>
                                <input type="number" step="0.01" min="0" name="delivery" id="delivery" class="form-control" value="{{ old('delivery', $factura->delivery) }}">
                            </div>
                            <div class="mb-3">
                                <label for="fecha_factura" class="form-label fw-semibold">Fecha de Factura</label>
                                <input type="date" name="fecha_factura" class="form-control" value="{{ old('fecha_factura', $factura->fecha_factura) }}">
                                @error('fecha_factura') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="numero_acta" class="form-label fw-semibold">Número de Acta</label>
                                <input type="text" name="numero_acta" class="form-control" value="{{ old('numero_acta', $factura->numero_acta) }}">
                                @error('numero_acta') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="moneda" class="form-label fw-semibold">Moneda</label>
                                <select name="moneda" class="form-select" required>
                                    <option value="USD" {{ old('moneda', $factura->moneda) == 'USD' ? 'selected' : '' }}>USD</option>
                                    <option value="NIO" {{ old('moneda', $factura->moneda) == 'NIO' ? 'selected' : '' }}>NIO</option>
                                </select>
                                @error('moneda') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="estado_pago" class="form-label fw-semibold">Estado de Pago</label>
                                <select name="estado_pago" class="form-select">
                                    <option value="pendiente" {{ old('estado_pago', $factura->estado_pago) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="parcial" {{ old('estado_pago', $factura->estado_pago) == 'parcial' ? 'selected' : '' }}>Parcial</option>
                                    <option value="pagado" {{ old('estado_pago', $factura->estado_pago) == 'pagado' ? 'selected' : '' }}>Pagado</option>
                                    <option value="entregado_pagado" {{ old('estado_pago', $factura->estado_pago) == 'entregado_pagado' ? 'selected' : '' }}>Entregado y Pagado</option>
                                    <option value="entregado_sin_pagar" {{ old('estado_pago', $factura->estado_pago) == 'entregado_sin_pagar' ? 'selected' : '' }}>Entregado sin Pagar</option>
                                    <option value="pagado_sin_entregar" {{ old('estado_pago', $factura->estado_pago) == 'pagado_sin_entregar' ? 'selected' : '' }}>Pagado sin Entregar</option>
                                    <option value="facturado_npne" {{ old('estado_pago', $factura->estado_pago) == 'facturado_npne' ? 'selected' : '' }}>Facturado NPNE</option>
                                </select>
                                @error('estado_pago') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="nota" class="form-label fw-semibold">Nota</label>
                                <textarea name="nota" class="form-control" rows="3">{{ old('nota', $factura->nota) }}</textarea>
                                @error('nota') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div id="inputs_paquetes"></div>
                            <input type="hidden" name="monto_total" id="monto_total" value="{{ old('monto_total', $factura->monto_total) }}">
                            <input type="hidden" name="monto_local" id="monto_local" value="{{ old('monto_local', $factura->monto_local) }}">
                            <div class="d-flex gap-2 justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary" id="btn_guardar_factura">
                                    <i class="fas fa-save me-1"></i> Actualizar
                                </button>
                                <a href="{{ route('facturacion.index') }}" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                    <div class="col-lg-6 p-3" style="min-width:320px;">
                        <h5 class="fw-bold mb-3"><i class="fas fa-eye text-primary me-2"></i>Previsualización</h5>
                        <iframe id="preview-pdf" src="" style="width:100%; min-height:600px; border:none; background:#fff;" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('head')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('scripts')
<script>
$(document).ready(function() {
    $('#cliente_id').select2({
        placeholder: 'Seleccione un cliente',
        width: '100%'
    });
    let paquetesSeleccionados = [];
    function mostrarSpinner(contenedor) {
        $(contenedor).html('<div class="text-center py-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></div>');
    }
    function limpiarContenedores() {
        $('#cliente_resumen').empty();
        $('#paquetes_container').empty();
        $('#facturas_historial').empty();
        paquetesSeleccionados = [];
        $('#btn_guardar_factura').prop('disabled', true);
    }
    // Si hay un cliente seleccionado al cargar, cargar sus datos y paquetes
    if ($('#cliente_id').val()) {
        cargarDatosCliente($('#cliente_id').val());
    }
    $('#cliente_id').on('change', function() {
        const clienteId = $(this).val();
        limpiarContenedores();
        if (!clienteId) return;
        cargarDatosCliente(clienteId);
    });
    function cargarDatosCliente(clienteId) {
        mostrarSpinner('#cliente_resumen');
        mostrarSpinner('#paquetes_container');
        mostrarSpinner('#facturas_historial');
        $.ajax({
            url: `/api/clientes/${clienteId}`,
            method: 'GET',
            dataType: 'json',
            success: function(resp) {
                if (!resp.success) {
                    $('#cliente_resumen').html('<div class="alert alert-danger">No se pudo cargar la información del cliente.</div>');
                    return;
                }
                // Resumen cliente
                const c = resp.cliente;
                $('#cliente_resumen').html(`
                    <div class="resumen-cliente-card"><i class="fas fa-user resumen-cliente-icon"></i><div class="resumen-cliente-info"><p><strong>Nombre:</strong> ${c.nombre_completo ?? '-'}<br><strong>Dirección:</strong> ${c.direccion ?? '-'}<br><strong>Teléfono:</strong> ${c.telefono ?? '-'}</p></div></div>
                `);
                // Paquetes
                if (!resp.paquetes || resp.paquetes.length === 0) {
                    $('#paquetes_container').html('<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-1"></i> No hay paquetes disponibles para facturar a este cliente.</div>');
                    $('#btn_guardar_factura').prop('disabled', true);
                } else {
                    let tabla = `<table class="table fact-table table-hover table-bordered align-middle shadow-sm rounded-3" style="background:#fff;">
                        <thead class="table-primary">
                            <tr>
                                <th></th>
                                <th>Guía</th>
                                <th>Notas</th>
                                <th>Tracking</th>
                                <th>Servicio</th>
                                <th>Peso (lb)</th>
                                <th>Precio Unitario</th>
                                <th>Monto</th>
                            </tr>
                        </thead>
                        <tbody>`;
                    resp.paquetes.forEach(p => {
                        const peso = parseFloat(p.peso_lb ?? 0);
                        const tarifa = parseFloat(p.tarifa_manual ?? p.tarifa ?? 1);
                        const monto = peso * tarifa;
                        tabla += `<tr>
                            <td><input type="checkbox" class="paquete-checkbox" value="${p.id}"></td>
                            <td>${p.numero_guia ?? '-'}</td>
                            <td>${p.notas ?? '-'}</td>
                            <td>${p.tracking_codigo ?? '-'}</td>
                            <td>${p.servicio ?? '-'}</td>
                            <td>${p.peso_lb ?? '-'}</td>
                            <td>$${tarifa.toFixed(2)}</td>
                            <td>$${monto.toFixed(2)}</td>
                        </tr>`;
                    });
                    tabla += '</tbody></table>';
                    // Inputs ocultos para paquetes seleccionados
                    tabla += '<div id="inputs_paquetes"></div>';
                    $('#paquetes_container').html(tabla);
                    $('#btn_guardar_factura').prop('disabled', true);
                }
                // Historial
                if (!resp.historial || resp.historial.length === 0) {
                    $('#facturas_historial').html('<div class="alert alert-secondary">Sin historial de facturas.</div>');
                } else {
                    let hist = `<div class="card mb-2"><div class="card-body"><h6 class="fw-semibold">Últimas 5 facturas</h6><table class="table fact-table table-sm table-bordered mb-0"><thead class="table-light"><tr><th>#</th><th>Fecha</th><th>Monto</th><th>Estado</th></tr></thead><tbody>`;
                    resp.historial.forEach(f => {
                        hist += `<tr><td>${f.id}</td><td>${f.fecha_factura}</td><td>$${parseFloat(f.monto_total).toFixed(2)}</td><td>${f.estado_pago === 'pagado' ? '<span class="badge bg-success">Pagado</span>' : f.estado_pago === 'parcial' ? '<span class="badge bg-warning text-dark">Parcial</span>' : '<span class="badge bg-danger">Pendiente</span>'}</td></tr>`;
                    });
                    hist += '</tbody></table></div></div>';
                    $('#facturas_historial').html(hist);
                }
            },
            error: function() {
                $('#cliente_resumen').html('<div class="alert alert-danger">Error al cargar los datos del cliente.</div>');
                $('#paquetes_container').html('');
                $('#facturas_historial').html('');
                $('#btn_guardar_factura').prop('disabled', true);
            }
        });
    }
    // Manejo de selección de paquetes
    $(document).on('change', '.paquete-checkbox', function() {
        paquetesSeleccionados = $('.paquete-checkbox:checked').map(function(){ return this.value; }).get();
        // Actualiza inputs ocultos
        let inputs = '';
        paquetesSeleccionados.forEach(id => {
            var fila = $(".paquete-checkbox[value='"+id+"']").closest('tr');
            inputs += `<input type="hidden" name="paquetes[]" value="${id}">`;
            inputs += `<input type="hidden" name="paquete_guia_${id}" value="${fila.find('td').eq(1).text().trim()}">`;
            inputs += `<input type="hidden" name="paquete_descripcion_${id}" value="${fila.find('td').eq(2).text().trim()}">`;
            inputs += `<input type="hidden" name="paquete_tracking_${id}" value="${fila.find('td').eq(3).text().trim()}">`;
            inputs += `<input type="hidden" name="paquete_servicio_${id}" value="${fila.find('td').eq(4).text().trim()}">`;
            inputs += `<input type="hidden" name="paquete_peso_${id}" value="${fila.find('td').eq(5).text().trim()}">`;
            inputs += `<input type="hidden" name="paquete_tarifa_${id}" value="${parseFloat(fila.find('td').eq(6).text().replace('$','')) || 0}">`;
            inputs += `<input type="hidden" name="paquete_valor_${id}" value="${parseFloat(fila.find('td').eq(7).text().replace('$','')) || 0}">`;
        });
        $('#inputs_paquetes').html(inputs);
        // Habilita o deshabilita el botón guardar
        $('#btn_guardar_factura').prop('disabled', paquetesSeleccionados.length === 0);
        actualizarMontosFactura();
    });

    function actualizarMontosFactura() {
        let total = 0;
        $('.paquete-checkbox:checked').each(function() {
            var fila = $(this).closest('tr');
            total += parseFloat(fila.find('td').eq(6).text().replace('$','')) || 0;
        });
        $('#monto_total').val(total.toFixed(2));
        $('#monto_local').val(total.toFixed(2)); // Si tienes lógica de moneda local, cámbiala aquí
    }

    // PDF Preview (mantener tu lógica actual)
    function updatePreview() {
        var form = document.getElementById('factura-form');
        var formData = new FormData(form);
        var cliente = getClienteData();
        formData.append('cliente_nombre', cliente.nombre_completo || '');
        formData.append('cliente_direccion', cliente.direccion || '');
        formData.append('cliente_telefono', cliente.telefono || '');

        // Si no hay paquetes seleccionados, limpiar el PDF y no enviar nada
        if ($('.paquete-checkbox:checked').length === 0) {
            document.getElementById('preview-pdf').src = '';
            return;
        }
        // Ya no agregamos los paquetes manualmente, los inputs ocultos lo hacen

        // Enviar delivery
        var deliveryInput = document.getElementById('delivery');
        if (deliveryInput && deliveryInput.value) {
            formData.set('delivery', deliveryInput.value);
        }

        var xhr = new XMLHttpRequest();
        xhr.open('POST', '{{ route('facturacion.preview-live') }}', true);
        xhr.responseType = 'blob';
        xhr.onload = function() {
            if (xhr.status === 200) {
                var url = URL.createObjectURL(xhr.response) + '#toolbar=0&navpanes=0&scrollbar=0';
                document.getElementById('preview-pdf').src = url;
            }
        };
        xhr.send(formData);
    }

    function getClienteData() {
        const clienteId = $('#cliente_id').val();
        let cliente = {};
        // Extraer datos del resumen del cliente si está presente
        const resumen = $('#cliente_resumen').text();
        cliente.nombre_completo = resumen.match(/Nombre:\s*([^\n]+)/) ? resumen.match(/Nombre:\s*([^\n]+)/)[1].trim() : '';
        cliente.direccion = resumen.match(/Dirección:\s*([^\n]+)/) ? resumen.match(/Dirección:\s*([^\n]+)/)[1].trim() : '';
        cliente.telefono = resumen.match(/Teléfono:\s*([^\n]+)/) ? resumen.match(/Teléfono:\s*([^\n]+)/)[1].trim() : '';
        return cliente;
    }

    // Llamar updatePreview cuando se selecciona cliente o paquetes o cambia algún campo relevante
    $('#cliente_id').on('change', function() { setTimeout(updatePreview, 300); });
    $(document).on('change', '.paquete-checkbox', function() { setTimeout(updatePreview, 300); });
    $('#factura-form').on('input change', 'input, select, textarea', function() { setTimeout(updatePreview, 300); });
});
</script>
@endsection

<style>
    .fact-table thead th {
        background: #1A2E75 !important;
        color: #fff !important;
        border-radius: 0 !important;
        border-bottom: 3px solid #5C6AC4;
        font-weight: 600;
        letter-spacing: 0.5px;
        border-right: 1px solid #e3e6f0 !important;
    }
    .fact-table thead th:last-child {
        border-right: none !important;
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
    .fact-table tbody td {
        border-right: 1px solid #e3e6f0 !important;
    }
    .fact-table tbody td:last-child {
        border-right: none !important;
    }
    .fact-table tbody tr:hover {
        background: #F5F7FA !important;
    }
    .resumen-cliente-card {
        background: #fff;
        border: 2px solid #5C6AC4;
        border-radius: 14px;
        box-shadow: 0 2px 8px rgba(26,46,117,0.04);
        padding: 1.2rem 1.5rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: flex-start;
        gap: 18px;
    }
    .resumen-cliente-icon {
        color: #1A2E75;
        font-size: 2.2rem;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .resumen-cliente-info {
        font-size: 1.08rem;
    }
    .resumen-cliente-info strong {
        color: #1A2E75;
        font-weight: 600;
    }
    .select2-container .select2-selection--single {
        border-radius: 8px !important;
        border: 1.5px solid #d1d5db;
        min-height: 44px;
        font-size: 1rem;
    }
    .form-control, .form-select, textarea {
        border-radius: 8px !important;
        min-height: 44px;
        font-size: 1rem;
    }
    .card {
        border-radius: 18px;
        box-shadow: 0 2px 8px rgba(26,46,117,0.04);
    }
    .btn-primary {
        background: #1A2E75;
        border: none;
        border-radius: 8px;
        font-weight: 600;
    }
    .btn-primary:hover, .btn-primary:focus {
        background: #223a7a;
    }
    .btn-secondary {
        border-radius: 8px;
    }
    .table-primary {
        background: #1A2E75 !important;
        color: #fff !important;
    }
    .alert-secondary {
        border-radius: 8px;
    }
</style>
