@extends('layouts.app')

@section('title', 'Registrar Factura - CH Logistics ERP')
@section('page-title', '')

@section('content')
@php
    $esEncFamiliar = old('tipo_factura', $tipoInicial ?? 'paqueteria') === 'encomienda_familiar';
@endphp
<div class="facturacion-page-shell">
    <div class="facturacion-top-banner d-flex align-items-center justify-content-between flex-wrap gap-2 gap-md-3" role="banner">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="mb-0">CH LOGISTICS ERP</span>
            @if($esEncFamiliar)
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
                        <form id="factura-form" action="{{ route('facturacion.store') }}" method="POST" novalidate>
                            @csrf
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            @if(!$esEncFamiliar)
                            <div class="mb-3 position-relative">
                                <label for="cliente_id" class="form-label fw-semibold">Cliente</label>
                                <div class="d-flex align-items-center" style="gap: 8px;">
                                    <span class="bg-white d-flex align-items-center justify-content-center" style="width: 38px; height: 48px; border: 1.5px solid #e3e8f0; border-radius: 8px; color: #15537c; font-size: 1.1rem;"><i class="fas fa-user"></i></span>
                                    <select id="cliente_id" name="cliente_id" class="form-select select2" required style="flex:1; min-width:0;">
                                        <option value="">Seleccione un cliente</option>
                                        @foreach($clientes as $cliente)
                                            <option value="{{ $cliente->id }}" {{ (string) old('cliente_id') === (string) $cliente->id ? 'selected' : '' }}>{{ $cliente->nombre_completo }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div id="cliente_resumen" class="mb-3"></div>
                            <div id="paquetes_container" class="mb-3"></div>
                            <div id="facturas_historial" class="mb-3"></div>
                            @endif
                            @if($esEncFamiliar)
                            <div id="encomienda_bloque" class="mb-3">
                                <label for="encomienda_id" class="form-label fw-semibold">Encomienda familiar <span class="text-danger">*</span></label>
                                <select name="encomienda_id" id="encomienda_id" class="form-select" required>
                                    <option value="">Seleccione una encomienda</option>
                                </select>
                                @error('encomienda_id') <div class="text-danger">{{ $message }}</div> @enderror
                                <small class="text-muted d-block mt-1">Solo encomiendas sin facturar. Remitente y destinatario salen automáticamente en el PDF.</small>
                            </div>
                            @endif

                            @if($esEncFamiliar)
                            <div id="encomienda_items_container" class="mb-3">
                                <div class="alert alert-info p-2 small mb-0">
                                    Selecciona una encomienda para ver sus ítems y completar reparto / montos en la tabla.
                                </div>
                            </div>
                            @endif

                            @if(!$esEncFamiliar)
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
                                    <input type="date" name="fecha_factura" id="fecha_factura" class="form-control" value="{{ old('fecha_factura', now()->format('Y-m-d')) }}">
                                </div>
                                @error('fecha_factura') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="numero_acta" class="form-label fw-semibold">Número de Acta <span class="text-muted fw-normal">(opcional)</span></label>
                                <input type="text" name="numero_acta" id="numero_acta" class="form-control" value="{{ old('numero_acta') }}" placeholder="Ej. NO-00101 — puede dejarse vacío" autocomplete="off">
                                @error('numero_acta') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            @else
                            <input type="hidden" name="fecha_factura" id="fecha_factura" value="{{ old('fecha_factura', now()->format('Y-m-d')) }}">
                            <input type="hidden" name="moneda" value="USD">
                            @endif
                            {{-- El tipo se elige en la pantalla anterior; aquí va fijo (no se muestra el selector) --}}
                            <input type="hidden" name="tipo_factura" id="tipo_factura" value="{{ old('tipo_factura', $tipoInicial ?? 'paqueteria') }}">
                            @error('tipo_factura') <div class="text-danger">{{ $message }}</div> @enderror
                            @if(!$esEncFamiliar)
                            <div class="mb-3">
                                <label for="moneda" class="form-label fw-semibold">Moneda</label>
                                <select name="moneda" id="moneda" class="form-select" required>
                                    <option value="USD" {{ old('moneda', 'USD') == 'USD' ? 'selected' : '' }}>USD</option>
                                    <option value="NIO" {{ old('moneda') == 'NIO' ? 'selected' : '' }}>NIO</option>
                                </select>
                                @error('moneda') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            @endif
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
                                <label for="nota" class="form-label fw-semibold">Nota interna</label>
                                <p class="small text-muted mb-2">Referencia solo para el sistema (ej. cancelación vía transferencia, acuerdos con el cliente). <strong>No</strong> se imprime en el PDF.</p>
                                <textarea name="nota" id="nota" class="form-control" rows="3" placeholder="Ej. Factura será cancelada por transferencia a cuenta X">{{ old('nota') }}</textarea>
                                @error('nota') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            @if(!$esEncFamiliar)
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Datos de entrega</label>
                                <div class="row g-2">
                                    <div class="col-12">
                                        <input type="text" name="entrega_nombre" class="form-control" placeholder="Entrega a (nombre receptor)" value="{{ old('entrega_nombre') }}">
                                        @error('entrega_nombre') <div class="text-danger">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="entrega_cedula" class="form-control" placeholder="Cédula receptor" value="{{ old('entrega_cedula') }}">
                                        @error('entrega_cedula') <div class="text-danger">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="entrega_telefono" class="form-control" placeholder="Teléfono entrega" value="{{ old('entrega_telefono') }}">
                                        @error('entrega_telefono') <div class="text-danger">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            @endif
                            <div id="inputs_paquetes"></div>
                            <input type="hidden" name="monto_total" id="monto_total" value="0">
                            <input type="hidden" name="monto_local" id="monto_local" value="0">
                            <input type="hidden" name="tasa_cambio" id="tasa_cambio" value="">
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
                            <iframe id="preview-pdf" src="" style="width:100%; min-height:600px; border:none; background:#fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(21,83,124,0.04);" allowfullscreen></iframe>
                            <div id="preview-placeholder" class="w-100 text-center text-muted position-absolute" style="display:none;">
                                <i class="fas fa-file-pdf fa-3x mb-2"></i><br>
                                <span id="preview-placeholder-msg">@if($esEncFamiliar)Seleccione una encomienda para previsualizar la nota de cobro.@else Selecciona paquetes para previsualizar la factura en PDF.@endif</span>
                            </div>
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
    const ES_ENCOMIENDA_FAMILIAR = @json($esEncFamiliar);
    if (!ES_ENCOMIENDA_FAMILIAR) {
        $('#cliente_id').select2({
            placeholder: 'Seleccione un cliente',
            width: '100%'
        });
    }
    let paquetesSeleccionados = [];
    let hayPendientesCliente = false;
    let encomiendaItemsList = [];
    const oldEncomiendaId = @json(old('encomienda_id'));

    function cargarEncomiendasSelect() {
        var $s = $('#encomienda_id');
        var prev = $s.val() || (oldEncomiendaId ? String(oldEncomiendaId) : '');
        $.get(@json(route('facturacion.encomiendas-disponibles')), function(resp) {
            $s.empty().append($('<option>', { value: '', text: 'Seleccione una encomienda' }));
            if (resp.success && resp.encomiendas && resp.encomiendas.length) {
                resp.encomiendas.forEach(function(e) {
                    var t = e.codigo + ' — ' + e.remitente + ' → ' + e.destinatario + ' ($' + Number(e.total).toFixed(2) + ')';
                    $s.append($('<option>', { value: e.id, text: t }));
                });
            }
            if (prev) {
                $s.val(prev);
                cargarEncomiendaItems(prev);
            }
            revisarBotonGuardarEncomienda();
        }).fail(function() {
            $s.empty().append($('<option>', { value: '', text: 'Error al cargar encomiendas' }));
        });
    }

    function revisarBotonGuardarEncomienda() {
        if (!ES_ENCOMIENDA_FAMILIAR) {
            return;
        }
        var actaOk = !$('input[name="numero_acta"]').hasClass('is-invalid');
        var encOk = !!$('#encomienda_id').val();
        $('#btn_guardar_factura').prop('disabled', !(actaOk && encOk));
    }

    const ENCOMIENDA_ITEMS_BASE_URL = @json(url('/facturacion/encomienda-items'));

    function renderEncomiendaItemsTable(items) {
        encomiendaItemsList = Array.isArray(items) ? items : [];

        const $container = $('#encomienda_items_container');
        if (!$container.length) return;

        if (!encomiendaItemsList.length) {
            $container.html('<div class="alert alert-warning p-2 small mb-0">Esta encomienda no tiene items.</div>');
            return;
        }

        let rows = '';
        encomiendaItemsList.forEach(function(it, idx) {
            const wrh = String(idx + 1).padStart(5, '0');
            const det = (it.descripcion && String(it.descripcion).trim() !== '') ? String(it.descripcion) : '—';

            const srv = it.metodo_cobro === 'peso' ? 'Peso' : (it.metodo_cobro === 'pie_cubico' ? 'Pie Cúbico' : (it.metodo_cobro ?? '—'));
            const checked = it.incluye_delivery ? 'checked' : '';
            const monto = (it.delivery_monto !== null && it.delivery_monto !== undefined && it.delivery_monto !== '') ? Number(it.delivery_monto).toFixed(2) : '';
            const showMonto = it.incluye_delivery ? '' : 'display:none;';

            const safeLabel = String(det).replaceAll('"', '&quot;');
            rows += `
                <tr data-item-id="${it.id}" data-item-label="${safeLabel}">
                    <td>${wrh}</td>
                    <td>${safeLabel}</td>
                    <td>${srv}</td>
                    <td style="text-align:center;">
                        <input type="checkbox"
                               class="form-check-input delivery-item-checkbox"
                               name="items_delivery[${it.id}]"
                               value="1"
                               ${checked}>
                    </td>
                    <td style="text-align:center;">
                        <input type="text"
                               inputmode="decimal"
                               autocomplete="off"
                               class="form-control form-control-sm delivery-item-monto"
                               name="items_delivery_monto[${it.id}]"
                               value="${monto}"
                               placeholder="Incl."
                               style="max-width:110px; margin:0 auto; ${showMonto}">
                        <small class="text-muted d-block" style="${showMonto}">Vacío = Incl.</small>
                    </td>
                </tr>
            `;
        });

        $container.html(`
            <table class="table table-sm table-bordered align-middle shadow-sm rounded-3 fact-table" style="background:#fff;">
                <thead>
                    <tr>
                        <th style="width:14%;">WRH</th>
                        <th>Detalle</th>
                        <th style="width:22%;">Servicio</th>
                        <th style="width:14%; text-align:center;">delivery</th>
                        <th style="width:18%; text-align:center;">Delivery $</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        `);
    }

    function cargarEncomiendaItems(encomiendaId) {
        const $container = $('#encomienda_items_container');
        if (!$container.length) return;

        if (!encomiendaId) {
            $container.html('<div class="alert alert-info p-2 small mb-0">Selecciona una encomienda para ver sus items.</div>');
            encomiendaItemsList = [];
            return;
        }

        $container.html('<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div></div>');

        $.get(ENCOMIENDA_ITEMS_BASE_URL + '/' + encomiendaId, function(resp) {
            if (resp && resp.success) {
                renderEncomiendaItemsTable(resp.items || []);
                schedulePreview(250);
            } else {
                $container.html('<div class="alert alert-danger p-2 small mb-0">No se pudieron cargar los items.</div>');
                encomiendaItemsList = [];
            }
        }).fail(function() {
            $container.html('<div class="alert alert-danger p-2 small mb-0">Error al cargar items de la encomienda.</div>');
            encomiendaItemsList = [];
        });
    }

    function aplicarModoFactura() {
        if (ES_ENCOMIENDA_FAMILIAR) {
            if ($('#paquetes_container').length) {
                $('#paquetes_container').hide();
            }
            if ($('#facturas_historial').length) {
                $('#facturas_historial').hide();
            }
            $('#inputs_paquetes').empty();
            paquetesSeleccionados = [];
            cargarEncomiendasSelect();
            revisarBotonGuardarEncomienda();
            schedulePreview(250);
            return;
        }
        if ($('#encomienda_id').length) {
            $('#encomienda_id').val('');
        }
        if ($('#paquetes_container').length) {
            $('#paquetes_container').show();
        }
        if ($('#facturas_historial').length) {
            $('#facturas_historial').show();
        }
        var cid = $('#cliente_id').val();
        if (cid) {
            $('#cliente_id').trigger('change');
        } else {
            $('#btn_guardar_factura').prop('disabled', true);
        }
        schedulePreview(250);
    }
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
    if (!ES_ENCOMIENDA_FAMILIAR) {
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
                                    <i class="fas fa-boxes me-2 text-brand"></i>
                                    Paquetes Disponibles
                                </h6>
                                <small class="text-muted">
                                    <span class="fw-medium paquetes-count text-brand">${resp.paquetes.length}</span> paquetes disponibles
                                </small>
                            </div>
                            <div class="paquetes-actions">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-secondary" id="select-all-packages">
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
                                <span class="badge badge-soft border">
                                    <i class="fas fa-barcode me-1"></i>
                                    ${p.numero_guia ?? '-'}
                                </span>
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge badge-soft border">
                                    <i class="fas fa-truck me-1"></i>
                                    ${p.tracking_codigo ?? '-'}
                                </span>
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge badge-soft border">
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
                                <span class="fw-semibold text-brand">$${tarifa.toFixed(2)}</span>
                            </td>
                            <td class="text-end align-middle">
                                <span class="fw-bold text-brand">$${monto.toFixed(2)}</span>
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
                        let estadoLabel = 'Pendiente';
                        if (f.estado_pago === 'entregado_pagado') {
                            estadoLabel = 'Entregado';
                        } else if (f.estado_pago === 'pagado') {
                            estadoLabel = 'Pagado';
                        } else if (f.estado_pago === 'parcial') {
                            estadoLabel = 'Parcial';
                        } else if (f.estado_pago === 'entregado_sin_pagar') {
                            estadoLabel = 'Entregado sin Pagar';
                        } else if (f.estado_pago === 'pagado_sin_entregar') {
                            estadoLabel = 'Pagado sin Entregar';
                        }
                        const estadoBadge = `<span class="badge estado-badge">${estadoLabel}</span>`;
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
        if (ES_ENCOMIENDA_FAMILIAR) {
            return;
        }
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
    } // !ES_ENCOMIENDA_FAMILIAR (cliente + paquetes)

    function actualizarMontosFactura() {
        let total = 0;
        $('.paquete-checkbox:checked').each(function() {
            var fila = $(this).closest('tr');
            total += parseFloat(fila.find('td').eq(6).text().replace('$','')) || 0;
        });
        $('#monto_total').val(total.toFixed(2));
        $('#monto_local').val(total.toFixed(2)); // Si tienes lógica de moneda local, cámbiala aquí
    }

    // PDF Preview: debounce + abort para evitar parpadeo al teclear; mismo endpoint y datos
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
        var na = form.numero_acta ? (form.numero_acta.value || '') : '';
        formData.set('numero_acta', na);

        var modoEnc = form.tipo_factura && form.tipo_factura.value === 'encomienda_familiar';
        if (modoEnc) {
            if (!form.encomienda_id || !form.encomienda_id.value) {
                document.getElementById('preview-pdf').src = '';
                document.getElementById('preview-placeholder').style.display = 'block';
                lastPreviewPayloadHash = '';
                return;
            }
            document.getElementById('preview-placeholder').style.display = 'none';
        } else {
            if ($('.paquete-checkbox:checked').length === 0) {
                document.getElementById('preview-pdf').src = '';
                document.getElementById('preview-placeholder').style.display = 'block';
                lastPreviewPayloadHash = '';
                return;
            }
            document.getElementById('preview-placeholder').style.display = 'none';
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
                            if ($('#tipo_factura').val() === 'encomienda_familiar') {
                                revisarBotonGuardarEncomienda();
                            } else {
                                $('#btn_guardar_factura').prop('disabled', $('.paquete-checkbox:checked').length === 0);
                            }
                        }
                    }
                });
            }, 500);
        } else {
            $('.numero-acta-error').remove();
            $('input[name="numero_acta"]').removeClass('is-invalid');
            if ($('#tipo_factura').val() === 'encomienda_familiar') {
                revisarBotonGuardarEncomienda();
            } else {
                $('#btn_guardar_factura').prop('disabled', $('.paquete-checkbox:checked').length === 0);
            }
        }
    });

    $('#encomienda_id').on('change', function() {
        revisarBotonGuardarEncomienda();
        cargarEncomiendaItems($(this).val());
        schedulePreview(300);
    });

    $(document).on('change', '.delivery-item-checkbox', function() {
        const $tr = $(this).closest('tr');
        const checked = $(this).is(':checked');
        const $monto = $tr.find('input.delivery-item-monto');
        const $hint = $tr.find('small.text-muted');

        if (checked) {
            $monto.show();
            $hint.show();
        } else {
            $monto.hide().val('');
            $hint.hide();
        }
        schedulePreview(350);
    });

    if (!ES_ENCOMIENDA_FAMILIAR) {
        $('#cliente_id').on('change', function() { schedulePreview(350); });
        $(document).on('change', '.paquete-checkbox', function() { schedulePreview(350); });
    }
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

    aplicarModoFactura();

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
        if (ES_ENCOMIENDA_FAMILIAR) {
            const encId = $('#encomienda_id').val();
            const tieneFilasItems = $('#encomienda_items_container tbody tr').length > 0;
            if (encId && !tieneFilasItems) {
                e.preventDefault();
                alert('Espera a que carguen los ítems de la encomienda o elige una encomienda que tenga ítems.');
                return;
            }
        }

        if (!ES_ENCOMIENDA_FAMILIAR && hayPendientesCliente) {
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
    /* Sin márgenes negativos horizontales: evitan que el texto del banner se recorte (p. ej. la “C”). */
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
    .card {
        border-radius: 18px;
        box-shadow: 0 2px 8px rgba(21,83,124,0.04);
    }
    .form-control, .form-select {
        border-radius: 8px !important;
        font-size: 1.08rem;
        padding: 0.7rem 1.1rem;
        border: 1.5px solid #e3e8f0;
        background: #f8fafc;
        color: #15537c;
        font-weight: 500;
        box-shadow: none;
        transition: border 0.18s;
    }
    .form-control:focus, .form-select:focus {
        border-color: #2d6a9a;
        outline: none;
        background: #fff;
    }
    .btn-primary {
        background: linear-gradient(90deg, #15537c 0%, #2d6a9a 100%);
        border: none;
        color: #fff;
        font-weight: 700;
        border-radius: 8px;
    }
    .btn-primary:hover, .btn-primary:focus {
        background: linear-gradient(90deg, #2d6a9a 0%, #15537c 100%);
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
        color: #15537c;
    }
    .input-group .input-group-text {
        background: #f8fafc;
        border: 1.5px solid #e3e8f0;
        border-right: none;
        color: #15537c;
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
        border: 2px solid #2d6a9a;
        border-radius: 14px;
        box-shadow: 0 2px 8px rgba(21,83,124,0.04);
        padding: 1.2rem 1.5rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 18px;
    }
    .resumen-cliente-icon {
        color: #15537c;
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
        color: #15537c;
        font-weight: 600;
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
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(21,83,124,0.1);
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
        color: #15537c !important;
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
        border-color: #15537c;
        color: #15537c;
        transform: none;
        box-shadow: none;
    }
    
    .paquetes-actions .btn:focus {
        box-shadow: 0 0 0 0.2rem rgba(21,83,124,0.15);
    }
    
    .paquetes-actions .btn.active {
        background: #15537c;
        border-color: #15537c;
        color: #ffffff;
    }
    
    /* Estilos para la tabla mejorada */
    .fact-table {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(21,83,124,0.08);
        border: 1px solid #e9ecef;
    }
    
    .fact-table thead th {
        background: linear-gradient(135deg, #15537c 0%, #2d6a9a 100%) !important;
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
        background: #f3f6fb !important;
    }
    
    /* Estilos para checkboxes */
    .form-check-input {
        width: 1.2rem;
        height: 1.2rem;
        border: 2px solid #15537c;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .form-check-input:checked {
        background-color: #15537c;
        border-color: #15537c;
        box-shadow: 0 0 0 0.2rem rgba(21,83,124,0.25);
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
    .badge-soft {
        background: #f3f6fb !important;
        color: #15537c !important;
        border-color: #d4deec !important;
    }
    .estado-badge {
        background: #15537c !important;
        color: #fff !important;
        border: 1px solid #15537c !important;
        font-weight: 600;
    }
    .text-brand {
        color: #15537c !important;
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
        $('.paquetes-info small .paquetes-count').text(checkedCount + '/' + totalCount);
        
        // Cambiar el color según la selección
        // Mantener un solo color corporativo para una UI más limpia.
        
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
