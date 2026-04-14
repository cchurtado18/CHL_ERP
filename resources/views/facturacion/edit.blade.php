@extends('layouts.app')

@section('title', 'Editar Factura - CH Logistics ERP')
@section('page-title', '')

@section('content')
@php
    $editEsEncFamiliar = ($factura->tipo_factura ?? '') === 'encomienda_familiar';
@endphp
<div class="facturacion-page-shell">
    <div class="facturacion-top-banner d-flex align-items-center justify-content-between flex-wrap gap-2 gap-md-3" role="banner">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="mb-0">CH LOGISTICS ERP</span>
            @if($editEsEncFamiliar)
                <span class="badge rounded-pill px-3 py-2 fw-semibold" style="background: linear-gradient(135deg, #b45309 0%, #d97706 100%); color: #fff;"><i class="fas fa-people-carry me-1"></i>Encomienda familiar</span>
            @else
                <span class="badge rounded-pill px-3 py-2 fw-semibold text-white" style="background: #15537c;"><i class="fas fa-boxes me-1"></i>Paquetería</span>
            @endif
        </div>
        <a href="{{ route('facturacion.index') }}" class="btn btn-light btn-sm fw-semibold rounded-pill px-3 py-2 shadow-sm border-0 flex-shrink-0 facturacion-btn-volver">
            <i class="fas fa-arrow-left me-1" aria-hidden="true"></i> Volver
        </a>
    </div>
    <div class="container-fluid px-3 px-lg-4 pt-3 pb-2">
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
                            @if(($factura->tipo_factura ?? '') !== 'encomienda_familiar')
                            <div class="mb-3">
                                <label for="delivery" class="form-label fw-semibold">Costo Delivery (opcional)</label>
                                <input type="number" step="0.01" min="0" name="delivery" id="delivery" class="form-control" value="{{ old('delivery', $factura->delivery) }}">
                            </div>
                            @else
                            <input type="hidden" name="delivery" id="delivery" value="{{ old('delivery', $factura->delivery) }}">
                            @endif
                            <div class="mb-3">
                                <label for="fecha_factura" class="form-label fw-semibold">Fecha de Factura</label>
                                <input type="date" name="fecha_factura" class="form-control" value="{{ old('fecha_factura', $factura->fecha_factura) }}">
                                @error('fecha_factura') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="numero_acta" class="form-label fw-semibold">Número de Acta <span class="text-muted fw-normal">(opcional)</span></label>
                                <input type="text" name="numero_acta" class="form-control" value="{{ old('numero_acta', $factura->numero_acta) }}" placeholder="Puede dejarse vacío" autocomplete="off">
                                @error('numero_acta') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="tipo_factura" class="form-label fw-semibold">Tipo</label>
                                <select name="tipo_factura" id="tipo_factura" class="form-select" required>
                                    <option value="paqueteria" {{ old('tipo_factura', $factura->tipo_factura ?? 'paqueteria') == 'paqueteria' ? 'selected' : '' }}>Paquetería</option>
                                    <option value="encomienda_familiar" {{ old('tipo_factura', $factura->tipo_factura ?? '') == 'encomienda_familiar' ? 'selected' : '' }}>Encomienda familiar</option>
                                </select>
                                @error('tipo_factura') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            @if(($factura->tipo_factura ?? '') === 'encomienda_familiar' && $factura->encomienda_id && $factura->encomienda)
                                <div class="mb-3 rounded border border-info bg-light p-3 small">
                                    <strong>Encomienda vinculada:</strong> {{ $factura->encomienda->codigo }}
                                    — {{ $factura->encomienda->remitente->nombre_completo ?? '—' }} → {{ $factura->encomienda->destinatario->nombre_completo ?? '—' }}
                                    <a href="{{ route('encomiendas.show', $factura->encomienda_id) }}" class="ms-2">Ver encomienda</a>
                                </div>
                            @endif
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
                                <label for="nota" class="form-label fw-semibold">Nota interna</label>
                                <p class="small text-muted mb-2 mb-0">Solo uso interno; no aparece en el PDF. Consúltala en el <a href="{{ route('facturacion.show', $factura->id) }}">detalle de la factura</a>.</p>
                                <textarea name="nota" id="nota" class="form-control" rows="3" placeholder="Ej. Pago acordado por transferencia">{{ old('nota', $factura->nota) }}</textarea>
                                @error('nota') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Datos de entrega</label>
                                <div class="row g-2">
                                    <div class="col-12">
                                        <input type="text" name="entrega_nombre" class="form-control" placeholder="Entrega a (nombre receptor)" value="{{ old('entrega_nombre', $factura->entrega_nombre) }}">
                                        @error('entrega_nombre') <div class="text-danger">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="entrega_cedula" class="form-control" placeholder="Cédula receptor" value="{{ old('entrega_cedula', $factura->entrega_cedula) }}">
                                        @error('entrega_cedula') <div class="text-danger">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="entrega_telefono" class="form-control" placeholder="Teléfono entrega" value="{{ old('entrega_telefono', $factura->entrega_telefono) }}">
                                        @error('entrega_telefono') <div class="text-danger">{{ $message }}</div> @enderror
                                    </div>
                                </div>
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

    // PDF Preview: debounce + abort (misma ruta y datos que antes)
    var previewXhr = null;
    var previewScheduleTimer = null;
    var lastPreviewPayloadHash = '';
    var previewObjectUrl = null;

    function hashFormDataForPreview(fd) {
        var pairs = [];
        try {
            fd.forEach(function(v, k) {
                if (v instanceof File) return;
                pairs.push(k + '=' + String(v));
            });
        } catch (e) { return ''; }
        pairs.sort();
        return pairs.join('|');
    }

    function schedulePreview(delayMs) {
        clearTimeout(previewScheduleTimer);
        previewScheduleTimer = setTimeout(function() {
            previewScheduleTimer = null;
            updatePreview();
        }, delayMs);
    }

    var SLOW_PREVIEW_NAMES = { entrega_nombre: 1, entrega_cedula: 1, entrega_telefono: 1, numero_acta: 1 };

    function previewDelayForElement(el, evtKind) {
        if (!el) return 400;
        var tag = el.tagName;
        if (tag === 'SELECT') return 350;
        var typ = el.type || '';
        if (typ === 'checkbox' || typ === 'radio') return 350;
        if (SLOW_PREVIEW_NAMES[el.name]) return evtKind === 'focusout' ? 0 : 900;
        if (el.classList && el.classList.contains('delivery-item-monto')) return 500;
        if (typ === 'number' || typ === 'text' || typ === 'tel' || typ === 'search') return 600;
        return 450;
    }

    function updatePreview() {
        var form = document.getElementById('factura-form');
        var formData = new FormData(form);
        var cliente = getClienteData();
        formData.set('cliente_nombre', cliente.nombre_completo || '');
        formData.set('cliente_direccion', cliente.direccion || '');
        formData.set('cliente_telefono', cliente.telefono || '');

        if ($('.paquete-checkbox:checked').length === 0) {
            document.getElementById('preview-pdf').src = '';
            lastPreviewPayloadHash = '';
            return;
        }

        var deliveryInput = document.getElementById('delivery');
        if (deliveryInput && deliveryInput.value) {
            formData.set('delivery', deliveryInput.value);
        }

        try { formData.delete('nota'); } catch (e) {}

        var payloadHash = hashFormDataForPreview(formData);
        if (payloadHash && payloadHash === lastPreviewPayloadHash) {
            return;
        }

        if (previewXhr) {
            try { previewXhr.abort(); } catch (e) {}
        }

        var req = new XMLHttpRequest();
        previewXhr = req;
        req.open('POST', '{{ route('facturacion.preview-live') }}', true);
        req.responseType = 'blob';
        req.onload = function() {
            if (previewXhr !== req) return;
            previewXhr = null;
            if (req.status !== 200) return;
            if (previewObjectUrl) {
                try { URL.revokeObjectURL(previewObjectUrl.split('#')[0]); } catch (e) {}
            }
            previewObjectUrl = URL.createObjectURL(req.response);
            document.getElementById('preview-pdf').src = previewObjectUrl + '#toolbar=0&navpanes=0&scrollbar=0';
            lastPreviewPayloadHash = payloadHash;
        };
        req.onerror = function() {
            if (previewXhr === req) previewXhr = null;
        };
        req.send(formData);
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

    // Validación en tiempo real del número de acta para edición
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
                        factura_id: {{ $factura->id }},
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
            $('#btn_guardar_factura').prop('disabled', false);
        }
    });

    $('#cliente_id').on('change', function() { schedulePreview(350); });
    $(document).on('change', '.paquete-checkbox', function() { schedulePreview(350); });
    $('#factura-form').on('input', 'input, select, textarea', function(e) {
        if (e.target.name === 'nota') return;
        schedulePreview(previewDelayForElement(e.target, 'input'));
    });
    $('#factura-form').on('change', 'input, select, textarea', function(e) {
        if (e.target.name === 'nota') return;
        var el = e.target;
        var tag = el.tagName;
        var typ = el.type || '';
        if (tag === 'SELECT' || typ === 'checkbox' || typ === 'radio') {
            schedulePreview(350);
            return;
        }
        schedulePreview(previewDelayForElement(el, 'change'));
    });
    $('#factura-form').on('focusout', 'input, textarea', function(e) {
        if (e.target.name === 'nota') return;
        if (SLOW_PREVIEW_NAMES[e.target.name]) {
            schedulePreview(0);
        }
    });
});
</script>
@endsection

<style>
    .facturacion-page-shell {
        margin: 0;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        overflow-x: visible;
    }
    .facturacion-top-banner {
        background: linear-gradient(90deg, #15537c 0%, #1a5f8f 48%, #2d6a9a 100%);
        color: #fff;
        font-size: 1.2rem;
        font-weight: 700;
        line-height: 1.4;
        letter-spacing: 0.02em;
        padding: 1rem 1.5rem;
        margin-bottom: 1.25rem;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(21, 83, 124, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.25);
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
    @media (min-width: 992px) {
        .facturacion-top-banner {
            padding: 1.1rem 2rem;
            font-size: 1.3rem;
        }
    }
    .facturacion-btn-volver {
        color: #15537c !important;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .facturacion-btn-volver:hover {
        color: #0f3d5c !important;
        background: #fff !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12) !important;
    }
    .fact-table thead th {
        background: #15537c !important;
        color: #fff !important;
        border-radius: 0 !important;
        border-bottom: 3px solid #2d6a9a;
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
        box-shadow: 0 2px 8px rgba(21,83,124,0.04);
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
        border: 2px solid #2d6a9a;
        border-radius: 14px;
        box-shadow: 0 2px 8px rgba(21,83,124,0.04);
        padding: 1.2rem 1.5rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: flex-start;
        gap: 18px;
    }
    .resumen-cliente-icon {
        color: #15537c;
        font-size: 2.2rem;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .resumen-cliente-info {
        font-size: 1.08rem;
    }
    .resumen-cliente-info strong {
        color: #15537c;
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
        box-shadow: 0 2px 8px rgba(21,83,124,0.04);
    }
    .btn-primary {
        background: #15537c;
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
        background: #15537c !important;
        color: #fff !important;
    }
    .alert-secondary {
        border-radius: 8px;
    }
</style>
