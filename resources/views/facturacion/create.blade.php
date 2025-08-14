@extends('layouts.app')

@section('title', 'Registrar Factura - SkylinkOne CRM')
@section('page-title', '')

@section('content')
<div class="container-fluid px-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="rounded-4 shadow-sm px-4 py-4 mb-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(90deg, #1A2E75 0%, #5C6AC4 100%); min-height:90px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width:60px; height:60px; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                        <i class="fas fa-file-invoice-dollar text-primary" style="font-size:2.2rem;"></i>
                    </div>
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="letter-spacing:1px;">Registrar Factura</h1>
                        <p class="mb-0 text-white-50" style="font-size:1.1rem;">Completa los datos para registrar una nueva factura</p>
                    </div>
                </div>
                <a href="{{ route('facturacion.index') }}" class="btn btn-lg fw-semibold shadow-sm px-4" style="background:#fff; color:#1A2E75; border:2px solid #1A2E75; box-shadow:0 2px 8px rgba(26,46,117,0.08); font-size:1.2rem;">
                    <i class="fas fa-arrow-left me-2"></i> Volver
                </a>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card p-4">
                <div class="row g-0 align-items-stretch">
                    <div class="col-lg-6 p-3 border-end" style="min-width:320px;">
                        <form id="factura-form" action="{{ route('facturacion.store') }}" method="POST">
                            @csrf
                            <div class="mb-3 position-relative">
                                <label for="cliente_id" class="form-label fw-semibold">Cliente</label>
                                <div class="d-flex align-items-center" style="gap: 8px;">
                                    <span class="bg-white d-flex align-items-center justify-content-center" style="width: 38px; height: 48px; border: 1.5px solid #e3e8f0; border-radius: 8px; color: #1A2E75; font-size: 1.1rem;"><i class="fas fa-user"></i></span>
                                    <select id="cliente_id" name="cliente_id" class="form-select select2" required style="flex:1; min-width:0;">
                                        <option value="">Seleccione un cliente</option>
                                        @foreach($clientes as $cliente)
                                            <option value="{{ $cliente->id }}">{{ $cliente->nombre_completo }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div id="cliente_resumen" class="mb-3"></div>
                            <div id="paquetes_container" class="mb-3"></div>
                            <div id="facturas_historial" class="mb-3"></div>
                            <div class="mb-3 position-relative">
                                <label for="delivery" class="form-label fw-semibold">Costo Delivery (opcional)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-truck text-success"></i></span>
                                    <input type="number" step="0.01" min="0" name="delivery" id="delivery" class="form-control" value="{{ old('delivery') }}">
                                </div>
                            </div>
                            <div class="mb-3 position-relative">
                                <label for="fecha_factura" class="form-label fw-semibold">Fecha de Factura</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-calendar-alt text-primary"></i></span>
                                    <input type="date" name="fecha_factura" class="form-control" value="{{ old('fecha_factura', date('Y-m-d')) }}">
                                </div>
                                @error('fecha_factura') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="numero_acta" class="form-label fw-semibold">Número de Acta</label>
                                <input type="text" name="numero_acta" class="form-control" value="{{ old('numero_acta') }}" required>
                                @error('numero_acta') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="moneda" class="form-label fw-semibold">Moneda</label>
                                <select name="moneda" class="form-select" required>
                                    <option value="USD" {{ old('moneda') == 'USD' ? 'selected' : '' }}>USD</option>
                                    <option value="NIO" {{ old('moneda') == 'NIO' ? 'selected' : '' }}>NIO</option>
                                </select>
                                @error('moneda') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="estado_pago" class="form-label fw-semibold">Estado de Pago</label>
                                <select name="estado_pago" class="form-select">
                                    <option value="entregado_pagado" {{ old('estado_pago') == 'entregado_pagado' ? 'selected' : '' }}>Entregado y Pagado</option>
                                    <option value="entregado_sin_pagar" {{ old('estado_pago') == 'entregado_sin_pagar' ? 'selected' : '' }}>Entregado sin Pagar</option>
                                    <option value="pagado_sin_entregar" {{ old('estado_pago') == 'pagado_sin_entregar' ? 'selected' : '' }}>Pagado sin Entregar</option>
                                    <option value="facturado_npne" {{ old('estado_pago') == 'facturado_npne' ? 'selected' : '' }}>Facturado NPNE</option>
                                </select>
                                @error('estado_pago') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="nota" class="form-label fw-semibold">Nota</label>
                                <textarea name="nota" class="form-control" rows="3">{{ old('nota') }}</textarea>
                                @error('nota') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div id="inputs_paquetes"></div>
                            <input type="hidden" name="monto_total" id="monto_total" value="0">
                            <input type="hidden" name="monto_local" id="monto_local" value="0">
                            <div class="d-flex gap-2 justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary px-4 py-2 fw-bold shadow-sm" id="btn_guardar_factura" style="border-radius:8px;">
                                    <i class="fas fa-save me-2"></i> Guardar
                                </button>
                                <a href="{{ route('facturacion.index') }}" class="btn btn-outline-secondary px-4 py-2" style="border-radius:8px;">Cancelar</a>
                            </div>
                        </form>
                    </div>
                    <div class="col-lg-6 p-3 d-flex flex-column align-items-stretch justify-content-start" style="min-width:320px; background: #f8fafc; border-radius: 0 22px 22px 0;">
                        <h5 class="fw-bold mb-3 d-flex align-items-center"><i class="fas fa-eye text-primary me-2"></i>Previsualización</h5>
                        <div class="flex-grow-1 d-flex align-items-center justify-content-center" style="min-height: 600px;">
                            <iframe id="preview-pdf" src="" style="width:100%; min-height:600px; border:none; background:#fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(26,46,117,0.04);" allowfullscreen></iframe>
                            <div id="preview-placeholder" class="w-100 text-center text-muted position-absolute" style="display:none;">
                                <i class="fas fa-file-pdf fa-3x mb-2"></i><br>
                                <span>Selecciona paquetes para previsualizar la factura en PDF.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para paquetes pendientes -->
<div class="modal fade" id="modalPendientes" tabindex="-1" aria-labelledby="modalPendientesLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalPendientesLabel"><i class="fas fa-exclamation-triangle text-warning me-2"></i>Paquetes Pendientes</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        El cliente aún tiene paquetes <b>pendientes de pago</b>. ¿Está seguro que desea proceder con la nueva factura?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnConfirmarPendientes">Sí, proceder</button>
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
    let hayPendientesCliente = false;
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
    $('#cliente_id').on('change', function() {
        const clienteId = $(this).val();
        limpiarContenedores();
        if (!clienteId) return;
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
                window.ultimoClienteSeleccionado = c;
                $('#cliente_resumen').html(`
                    <div class="resumen-cliente-card"><i class="fas fa-user resumen-cliente-icon"></i><div class="resumen-cliente-info"><p><strong>Nombre:</strong> ${c.nombre_completo ?? '-'}<br><strong>Dirección:</strong> ${c.direccion ?? '-'}<br><strong>Teléfono:</strong> ${c.telefono ?? '-'}</p></div></div>
                `);
                // Paquetes
                if (!resp.paquetes || resp.paquetes.length === 0) {
                    $('#paquetes_container').html('<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-1"></i> No hay paquetes disponibles para facturar a este cliente. Todos los paquetes han sido entregados o ya están facturados.</div>');
                    $('#btn_guardar_factura').prop('disabled', true);
                    // Limpiar preview cuando no hay paquetes
                    document.getElementById('preview-pdf').src = '';
                    document.getElementById('preview-placeholder').style.display = 'block';
                } else {
                    let tabla = `<div class="paquetes-header mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="paquetes-info">
                                <h6 class="mb-1 fw-semibold text-dark">
                                    <i class="fas fa-boxes me-2 text-primary"></i>
                                    Paquetes Disponibles
                                </h6>
                                <small class="text-muted">
                                    <span class="fw-medium text-primary">${resp.paquetes.length}</span> paquetes disponibles
                                </small>
                            </div>
                            <div class="paquetes-actions">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary" id="select-all-packages">
                                        <i class="fas fa-check me-1"></i>
                                        Todos
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="deselect-all-packages">
                                        <i class="fas fa-times me-1"></i>
                                        Ninguno
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table fact-table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th width="60" class="text-center">
                                        <div class="form-check d-flex justify-content-center">
                                            <input type="checkbox" id="select-all-checkbox" class="form-check-input">
                                        </div>
                                    </th>
                                    <th><i class="fas fa-barcode me-1"></i>Guía</th>
                                    <th><i class="fas fa-truck me-1"></i>Tracking</th>
                                    <th><i class="fas fa-shipping-fast me-1"></i>Servicio</th>
                                    <th><i class="fas fa-weight-hanging me-1"></i>Peso (lb)</th>
                                    <th><i class="fas fa-dollar-sign me-1"></i>Precio Unit.</th>
                                    <th><i class="fas fa-calculator me-1"></i>Monto</th>
                                </tr>
                            </thead>
                            <tbody>`;
                    resp.paquetes.forEach(p => {
                        const peso = parseFloat(p.peso_lb ?? 0);
                        const tarifa = parseFloat(p.tarifa_manual ?? p.tarifa ?? 1);
                        const monto = peso * tarifa;
                        tabla += `<tr class="paquete-row" data-paquete-id="${p.id}" data-notas="${p.notas || ''}">
                            <td class="text-center align-middle">
                                <div class="form-check d-flex justify-content-center">
                                    <input type="checkbox" class="paquete-checkbox form-check-input" value="${p.id}">
                                </div>
                            </td>
                            <td class="align-middle">
                                <span class="badge bg-light text-dark border">
                                    <i class="fas fa-barcode me-1"></i>
                                    ${p.numero_guia ?? '-'}
                                </span>
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge bg-info bg-opacity-10 text-info border">
                                    <i class="fas fa-truck me-1"></i>
                                    ${p.tracking_codigo ?? '-'}
                                </span>
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge bg-primary bg-opacity-10 text-primary border">
                                    <i class="fas fa-shipping-fast me-1"></i>
                                    ${p.servicio ?? '-'}
                                </span>
                            </td>
                            <td class="text-center align-middle">
                                <div class="d-flex flex-column align-items-center">
                                    <span class="fw-semibold">${p.peso_lb ?? '-'}</span>
                                    <small class="text-muted">lb</small>
                                </div>
                            </td>
                            <td class="text-end align-middle">
                                <span class="fw-semibold text-success">$${tarifa.toFixed(2)}</span>
                            </td>
                            <td class="text-end align-middle">
                                <span class="fw-bold text-primary">$${monto.toFixed(2)}</span>
                            </td>
                        </tr>`;
                    });
                    tabla += '</tbody></table></div>';
                    // Inputs ocultos para paquetes seleccionados
                    tabla += '<div id="inputs_paquetes"></div>';
                    $('#paquetes_container').html(tabla);
                    $('#btn_guardar_factura').prop('disabled', true);
                    
                    // Disparar evento personalizado para inicializar la selección masiva
                    $(document).trigger('paquetesLoaded');
                    
                    // Inicializar checkboxes inmediatamente
                    setTimeout(function() {
                        console.log('Inicializando checkboxes después de cargar paquetes...');
                        
                        // Checkbox principal
                        $('#select-all-checkbox').off('change').on('change', function() {
                            console.log('Checkbox principal clickeado:', this.checked);
                            var isChecked = this.checked;
                            $('.paquete-checkbox').prop('checked', isChecked).trigger('change');
                        });
                        
                        // Botón "Todos"
                        $('#select-all-packages').off('click').on('click', function(e) {
                            e.preventDefault();
                            console.log('Botón Todos clickeado');
                            $('.paquete-checkbox').prop('checked', true).trigger('change');
                            $('#select-all-checkbox').prop('checked', true);
                        });
                        
                        // Botón "Ninguno"
                        $('#deselect-all-packages').off('click').on('click', function(e) {
                            e.preventDefault();
                            console.log('Botón Ninguno clickeado');
                            $('.paquete-checkbox').prop('checked', false).trigger('change');
                            $('#select-all-checkbox').prop('checked', false);
                        });
                        
                        console.log('Checkboxes inicializados correctamente');
                    }, 100);
                }
                // Historial
                if (!resp.historial || resp.historial.length === 0) {
                    $('#facturas_historial').html('<div class="alert alert-secondary">Sin historial de facturas.</div>');
                } else {
                    let hist = `<div class="card mb-2"><div class="card-body"><h6 class="fw-semibold">Últimas 5 facturas</h6><table class="table fact-table table-sm table-bordered mb-0"><thead class="table-light"><tr><th>#</th><th>Fecha</th><th>Monto</th><th>Estado</th></tr></thead><tbody>`;
                    resp.historial.forEach(f => {
                        let estadoBadge = '';
                        if (f.estado_pago === 'entregado_pagado') {
                            estadoBadge = '<span class="badge bg-success">Entregado</span>';
                        } else if (f.estado_pago === 'pagado') {
                            estadoBadge = '<span class="badge bg-success">Pagado</span>';
                        } else if (f.estado_pago === 'parcial') {
                            estadoBadge = '<span class="badge bg-warning text-dark">Parcial</span>';
                        } else if (f.estado_pago === 'entregado_sin_pagar') {
                            estadoBadge = '<span class="badge bg-info">Entregado sin Pagar</span>';
                        } else if (f.estado_pago === 'pagado_sin_entregar') {
                            estadoBadge = '<span class="badge bg-primary">Pagado sin Entregar</span>';
                        } else {
                            estadoBadge = '<span class="badge bg-danger">Pendiente</span>';
                        }
                        hist += `<tr><td>${f.id}</td><td>${f.fecha_factura}</td><td>$${parseFloat(f.monto_total).toFixed(2)}</td><td>${estadoBadge}</td></tr>`;
                    });
                    hist += '</tbody></table></div></div>';
                    $('#facturas_historial').html(hist);
                }
                revisarPendientesCliente(resp.historial);
            },
            error: function() {
                $('#cliente_resumen').html('<div class="alert alert-danger">Error al cargar los datos del cliente.</div>');
                $('#paquetes_container').html('');
                $('#facturas_historial').html('');
                $('#btn_guardar_factura').prop('disabled', true);
            }
        });
    });
    // Manejo de selección de paquetes
    $(document).on('change', '.paquete-checkbox', function() {
        console.log('Checkbox individual cambiado:', this.value, this.checked);
        
        paquetesSeleccionados = $('.paquete-checkbox:checked').map(function(){ return this.value; }).get();
        
        // Lógica de alerta por paquetes no pagados
        if (paquetesSeleccionados.length > 1) {
            let hayPendientes = false;
            let paquetePendiente = null;
            paquetesSeleccionados.forEach(id => {
                var fila = $(".paquete-checkbox[value='"+id+"']").closest('tr');
                // Suponiendo que el estado está en la columna 7 (ajustado sin columna de notas)
                var estado = fila.find('td').eq(7).text().trim().toLowerCase();
                if (estado === 'pendiente' || estado === 'no pagado') {
                    hayPendientes = true;
                    paquetePendiente = id;
                }
            });
            if (hayPendientes) {
                if (!confirm('El cliente aún tiene paquetes sin cancelar. ¿Está seguro que desea proceder?')) {
                    // Desmarcar el último paquete seleccionado
                    $(this).prop('checked', false);
                    paquetesSeleccionados = $('.paquete-checkbox:checked').map(function(){ return this.value; }).get();
                    actualizarMontosFactura();
                    return;
                }
            }
        }
        
        // Actualiza inputs ocultos
        let inputs = '';
        paquetesSeleccionados.forEach(id => {
            var fila = $(".paquete-checkbox[value='"+id+"']").closest('tr');
            var notas = fila.attr('data-notas') || '';
            inputs += `<input type="hidden" name="paquetes[]" value="${id}">`;
            inputs += `<input type="hidden" name="paquete_guia_${id}" value="${fila.find('td').eq(1).text().trim()}">`;
            inputs += `<input type="hidden" name="paquete_descripcion_${id}" value="${notas}">`; // Usar las notas reales del paquete
            inputs += `<input type="hidden" name="paquete_tracking_${id}" value="${fila.find('td').eq(2).text().trim()}">`;
            inputs += `<input type="hidden" name="paquete_servicio_${id}" value="${fila.find('td').eq(3).text().trim()}">`;
            inputs += `<input type="hidden" name="paquete_peso_${id}" value="${fila.find('td').eq(4).text().trim()}">`;
            inputs += `<input type="hidden" name="paquete_tarifa_${id}" value="${parseFloat(fila.find('td').eq(5).text().replace('$','')) || 0}">`;
            inputs += `<input type="hidden" name="paquete_valor_${id}" value="${parseFloat(fila.find('td').eq(6).text().replace('$','')) || 0}">`;
        });
        $('#inputs_paquetes').html(inputs);
        
        // Habilita o deshabilita el botón guardar
        $('#btn_guardar_factura').prop('disabled', paquetesSeleccionados.length === 0);
        
        // Actualizar UI de selección
        updateSelectAllCheckbox();
        updateSelectionUI();
        
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
        
        // Enviar los datos del cliente en campos separados y limpios
        formData.set('cliente_nombre', cliente.nombre_completo || '');
        formData.set('cliente_direccion', cliente.direccion || '');
        formData.set('cliente_telefono', cliente.telefono || '');
        // Enviar número de acta
        formData.set('numero_acta', form.numero_acta.value || '');

        // Si no hay paquetes seleccionados, limpiar el PDF y no enviar nada
        if ($('.paquete-checkbox:checked').length === 0) {
            document.getElementById('preview-pdf').src = '';
            document.getElementById('preview-placeholder').style.display = 'block';
            return;
        }
        
        // Ocultar placeholder si hay paquetes seleccionados
        document.getElementById('preview-placeholder').style.display = 'none';

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
        // Si tienes el objeto del cliente en JS, úsalo directamente
        if (window.ultimoClienteSeleccionado) {
            return {
                nombre_completo: window.ultimoClienteSeleccionado.nombre_completo || '',
                direccion: window.ultimoClienteSeleccionado.direccion || '',
                telefono: window.ultimoClienteSeleccionado.telefono || ''
            };
        }
        // Fallback: método anterior (por si acaso)
        const resumen = $('#cliente_resumen').text();
        const nombreMatch = resumen.match(/Nombre:\s*([^\n]+)/);
        const direccionMatch = resumen.match(/Dirección:\s*([^\n]+)/);
        const telefonoMatch = resumen.match(/Teléfono:\s*([^\n]+)/);
        return {
            nombre_completo: nombreMatch ? nombreMatch[1].trim() : '',
            direccion: direccionMatch ? direccionMatch[1].trim() : '',
            telefono: telefonoMatch ? telefonoMatch[1].trim() : ''
        };
    }

    // Validación en tiempo real del número de acta
    let timeoutNumeroActa;
    $('input[name="numero_acta"]').on('input', function() {
        clearTimeout(timeoutNumeroActa);
        const numeroActa = $(this).val().trim();
        
        if (numeroActa.length > 0) {
            timeoutNumeroActa = setTimeout(function() {
                $.ajax({
                    url: '{{ route("facturacion.validar-numero-acta") }}',
                    method: 'POST',
                    data: {
                        numero_acta: numeroActa,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        const input = $('input[name="numero_acta"]');
                        const errorDiv = input.siblings('.numero-acta-error');
                        
                        if (response.exists) {
                            if (errorDiv.length === 0) {
                                input.after('<div class="numero-acta-error text-danger mt-1"><i class="fas fa-exclamation-triangle me-1"></i>' + response.message + '</div>');
                            }
                            input.addClass('is-invalid');
                            $('#btn_guardar_factura').prop('disabled', true);
                        } else {
                            errorDiv.remove();
                            input.removeClass('is-invalid');
                            $('#btn_guardar_factura').prop('disabled', false);
                        }
                    }
                });
            }, 500);
        } else {
            $('.numero-acta-error').remove();
            $('input[name="numero_acta"]').removeClass('is-invalid');
        }
    });

    // Llamar updatePreview cuando se selecciona cliente o paquetes o cambia algún campo relevante
    $('#cliente_id').on('change', function() { setTimeout(updatePreview, 300); });
    $(document).on('change', '.paquete-checkbox', function() { setTimeout(updatePreview, 300); });
    $('#factura-form').on('input change', 'input, select, textarea', function() { setTimeout(updatePreview, 300); });

    // Cuando se carga el historial del cliente, detecta si hay facturas pendientes
    function revisarPendientesCliente(historial) {
        hayPendientesCliente = false;
        if (historial && Array.isArray(historial)) {
            // Considerar como pendientes: pendiente, parcial, entregado_sin_pagar, pagado_sin_entregar
            hayPendientesCliente = historial.some(f => 
                f.estado_pago === 'pendiente' || 
                f.estado_pago === 'parcial' || 
                f.estado_pago === 'entregado_sin_pagar' || 
                f.estado_pago === 'pagado_sin_entregar'
            );
        }
    }
    // Intercepta el submit del formulario
    $('#factura-form').on('submit', function(e) {
        if (hayPendientesCliente) {
            e.preventDefault();
            var modal = new bootstrap.Modal(document.getElementById('modalPendientes'));
            modal.show();
            $('#btnConfirmarPendientes').off('click').on('click', function() {
                modal.hide();
                $('#factura-form')[0].submit();
            });
        }
    });
});
</script>
@endsection

<style>
    .card {
        border-radius: 18px;
        box-shadow: 0 2px 8px rgba(26,46,117,0.04);
    }
    .form-control, .form-select {
        border-radius: 8px !important;
        font-size: 1.08rem;
        padding: 0.7rem 1.1rem;
        border: 1.5px solid #e3e8f0;
        background: #f8fafc;
        color: #1A2E75;
        font-weight: 500;
        box-shadow: none;
        transition: border 0.18s;
    }
    .form-control:focus, .form-select:focus {
        border-color: #5C6AC4;
        outline: none;
        background: #fff;
    }
    .btn-primary {
        background: linear-gradient(90deg, #1A2E75 0%, #5C6AC4 100%);
        border: none;
        color: #fff;
        font-weight: 700;
        border-radius: 8px;
    }
    .btn-primary:hover, .btn-primary:focus {
        background: linear-gradient(90deg, #5C6AC4 0%, #1A2E75 100%);
        color: #fff;
    }
    .btn-outline-secondary {
        border-radius: 8px;
        border: 1.5px solid #bfc7d8;
        color: #6c7a92;
        background: #f8fafc;
        font-weight: 600;
    }
    .btn-outline-secondary:hover, .btn-outline-secondary:focus {
        background: #e3e8f0;
        color: #1A2E75;
    }
    .input-group .input-group-text {
        background: #f8fafc;
        border: 1.5px solid #e3e8f0;
        border-right: none;
        color: #1A2E75;
        font-size: 1.1rem;
        border-radius: 8px 0 0 8px !important;
        display: flex;
        align-items: center;
        padding: 0.7rem 0.7rem;
        min-width: 38px;
        justify-content: center;
        height: 48px;
    }
    .input-group .input-group-text i {
        font-size: 1.1rem;
        line-height: 1;
    }
    /* Ajuste para el select2 de cliente con icono */
    #cliente_id.select2-hidden-accessible + .select2-container .select2-selection--single {
        height: 48px !important;
        display: flex;
        align-items: center;
        font-size: 1.08rem;
        border-radius: 8px !important;
        border: 1.5px solid #e3e8f0;
        background: #f8fafc;
    }
    #cliente_id.select2-hidden-accessible + .select2-container {
        flex: 1;
        min-width: 0;
    }
    .resumen-cliente-card {
        background: #fff;
        border: 2px solid #5C6AC4;
        border-radius: 14px;
        box-shadow: 0 2px 8px rgba(26,46,117,0.04);
        padding: 1.2rem 1.5rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 18px;
    }
    .resumen-cliente-icon {
        color: #1A2E75;
        font-size: 2.2rem;
        flex-shrink: 0;
        margin-top: 0;
        display: flex;
        align-items: center;
        height: 100%;
    }
    .resumen-cliente-info {
        font-size: 1.08rem;
    }
    .resumen-cliente-info strong {
        color: #1A2E75;
        font-weight: 600;
    }
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
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(26,46,117,0.1);
    }
    
    /* Estilos minimalistas para la sección de paquetes */
    .paquetes-header {
        background: #ffffff;
        border-radius: 8px;
        padding: 1rem 1.25rem;
        border: 1px solid #e9ecef;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    
    .paquetes-info h6 {
        color: #495057;
        font-size: 1rem;
        margin-bottom: 0.25rem;
        font-weight: 600;
    }
    
    .paquetes-info small {
        font-size: 0.875rem;
        color: #6c757d;
    }
    
    .paquetes-info .text-primary {
        color: #1A2E75 !important;
    }
    
    .paquetes-actions .btn-group {
        box-shadow: none;
    }
    
    .paquetes-actions .btn {
        border-radius: 6px;
        font-weight: 500;
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
        border: 1px solid #dee2e6;
        background: #ffffff;
        color: #6c757d;
        transition: all 0.2s ease;
    }
    
    .paquetes-actions .btn:hover {
        background: #f8f9fa;
        border-color: #1A2E75;
        color: #1A2E75;
        transform: none;
        box-shadow: none;
    }
    
    .paquetes-actions .btn:focus {
        box-shadow: 0 0 0 0.2rem rgba(26,46,117,0.15);
    }
    
    .paquetes-actions .btn.active {
        background: #1A2E75;
        border-color: #1A2E75;
        color: #ffffff;
    }
    
    /* Estilos para la tabla mejorada */
    .fact-table {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(26,46,117,0.08);
        border: 1px solid #e9ecef;
    }
    
    .fact-table thead th {
        background: linear-gradient(135deg, #1A2E75 0%, #5C6AC4 100%) !important;
        color: #fff !important;
        font-weight: 600;
        padding: 1rem 0.75rem;
        border: none;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .fact-table tbody td {
        padding: 1rem 0.75rem;
        border-bottom: 1px solid #f1f3f4;
        vertical-align: middle;
    }
    
    .paquete-row {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .paquete-row:hover {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    }
    
    /* Estilos para checkboxes */
    .form-check-input {
        width: 1.2rem;
        height: 1.2rem;
        border: 2px solid #1A2E75;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .form-check-input:checked {
        background-color: #1A2E75;
        border-color: #1A2E75;
        box-shadow: 0 0 0 0.2rem rgba(26,46,117,0.25);
    }
    
    .form-check-input:hover {
        transform: scale(1.1);
    }
    
    /* Badges mejorados */
    .badge {
        font-size: 0.8rem;
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        font-weight: 500;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .paquetes-header {
            padding: 1rem;
        }
        
        .paquetes-info {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }
        
        .paquetes-icon {
            width: 40px;
            height: 40px;
            font-size: 1.2rem;
        }
        
        .paquetes-actions {
            flex-direction: column;
            gap: 0.5rem;
            width: 100%;
        }
        
        .paquetes-actions .btn {
            width: 100%;
            min-width: auto;
        }
        
        .fact-table {
            font-size: 0.85rem;
        }
        
        .fact-table thead th {
            padding: 0.75rem 0.5rem;
            font-size: 0.8rem;
        }
        
        .fact-table tbody td {
            padding: 0.75rem 0.5rem;
        }
        
        .badge {
            font-size: 0.75rem;
            padding: 0.4rem 0.6rem;
        }
    }
    
    @media (max-width: 576px) {
        .paquetes-header {
            padding: 0.75rem;
        }
        
        .paquetes-info h5 {
            font-size: 1.1rem;
        }
        
        .paquetes-info p {
            font-size: 0.85rem;
        }
    }
</style>

<script>
$(document).ready(function() {
    console.log('Document ready - Inicializando sistema de selección...');
    
    // Función para actualizar el estado del checkbox principal
    function updateSelectAllCheckbox() {
        const totalCheckboxes = $('.paquete-checkbox').length;
        const checkedCheckboxes = $('.paquete-checkbox:checked').length;
        
        console.log('Actualizando checkbox principal - Total:', totalCheckboxes, 'Checked:', checkedCheckboxes);
        
        if (checkedCheckboxes === 0) {
            $('#select-all-checkbox').prop('indeterminate', false).prop('checked', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
            $('#select-all-checkbox').prop('indeterminate', false).prop('checked', true);
        } else {
            $('#select-all-checkbox').prop('indeterminate', true);
        }
    }

    // Función para actualizar la UI de selección
    function updateSelectionUI() {
        const checkedCount = $('.paquete-checkbox:checked').length;
        const totalCount = $('.paquete-checkbox').length;
        
        console.log('Actualizando UI - Seleccionados:', checkedCount, 'Total:', totalCount);
        
        // Actualizar el contador en el header
        $('.paquetes-info small .text-primary').text(checkedCount + '/' + totalCount);
        
        // Cambiar el color según la selección
        if (checkedCount > 0) {
            $('.paquetes-info small .text-primary').removeClass('text-primary').addClass('text-success');
        } else {
            $('.paquetes-info small .text-primary').removeClass('text-success').addClass('text-primary');
        }
        
        // Actualizar estado de botones
        if (checkedCount === totalCount && totalCount > 0) {
            $('#select-all-packages').addClass('active');
            $('#deselect-all-packages').removeClass('active');
        } else if (checkedCount === 0) {
            $('#select-all-packages').removeClass('active');
            $('#deselect-all-packages').addClass('active');
        } else {
            $('#select-all-packages').removeClass('active');
            $('#deselect-all-packages').removeClass('active');
        }
    }

    // Inicializar cuando se cargan los paquetes
    $(document).on('paquetesLoaded', function() {
        console.log('Evento paquetesLoaded disparado');
        updateSelectionUI();
    });

    // Inicializar al cargar la página
    console.log('Sistema de selección inicializado');
});
</script>
