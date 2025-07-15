@extends('layouts.app')

@section('title', 'Nuevo Paquete - SkylinkOne CRM')
@section('page-title', 'Registrar Nuevo Paquete')

@section('content')
<div class="container-fluid px-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="rounded-4 shadow-sm px-4 py-4 mb-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(90deg, #1A2E75 0%, #5C6AC4 100%); min-height:90px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width:60px; height:60px; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                        <i class="fas fa-plus-circle text-primary" style="font-size:2.2rem;"></i>
                    </div>
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="letter-spacing:1px;">Registrar Nuevo Paquete</h1>
                        <p class="mb-0 text-white-50" style="font-size:1.1rem;">Completa la información del paquete para agregarlo al inventario</p>
                    </div>
                </div>
                <a href="{{ route('inventario.index') }}" class="btn btn-outline-light fw-semibold shadow-sm px-4">
                    <i class="fas fa-arrow-left me-2"></i> Volver al Inventario
                </a>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-semibold text-dark">
                        <i class="fas fa-box me-2 text-primary"></i>
                        Información del Paquete
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('inventario.store') }}" method="POST" id="inventarioForm">
                        @csrf

                        <div class="row g-4">
                            <!-- Cliente -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="clienteAutocomplete" class="form-label fw-semibold">
                                        <i class="fas fa-user me-1 text-muted"></i>
                                        Buscar cliente *
                                    </label>
                                    <div class="autocomplete-wrapper position-relative">
                                        <input type="text" id="clienteAutocomplete" class="form-control filtro-input" placeholder="Escribe el nombre del cliente..." autocomplete="off" required>
                                        <ul id="autocompleteList" class="list-group position-absolute w-100 shadow-sm" style="z-index:10; display:none; max-height:180px; overflow-y:auto;"></ul>
                                        <input type="hidden" name="cliente_id" id="cliente_id" value="{{ old('cliente_id') }}">
                                    </div>
                                    @error('cliente_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Servicio -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="servicio_id" class="form-label fw-semibold">
                                        <i class="fas fa-shipping-fast me-1 text-muted"></i>
                                        Servicio *
                                    </label>
                                    <select name="servicio_id" class="form-select @error('servicio_id') is-invalid @enderror" required>
                                        <option value="">Seleccione un servicio</option>
                                        @foreach($servicios as $servicio)
                                            <option value="{{ $servicio->id }}" {{ old('servicio_id') == $servicio->id ? 'selected' : '' }}>
                                                {{ $servicio->tipo_servicio }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('servicio_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Peso -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="peso_lb" class="form-label fw-semibold">
                                        <i class="fas fa-weight-hanging me-1 text-muted"></i>
                                        Peso (lb) *
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">lb</span>
                                        <input type="number" step="0.01" name="peso_lb" class="form-control @error('peso_lb') is-invalid @enderror" 
                                               value="{{ old('peso_lb') }}" required placeholder="0.00">
                                    </div>
                                    @error('peso_lb')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Tracking -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tracking_codigo" class="form-label fw-semibold">
                                        <i class="fas fa-barcode me-1 text-muted"></i>
                                        Código de Tracking
                                    </label>
                                    <input type="text" name="tracking_codigo" class="form-control @error('tracking_codigo') is-invalid @enderror" value="{{ old('tracking_codigo') }}" placeholder="Ingrese el código de tracking">
                                    @error('tracking_codigo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Número de Guía -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="numero_guia" class="form-label fw-semibold">
                                        <i class="fas fa-barcode me-1 text-muted"></i>
                                        Número de Guía
                                    </label>
                                    <input type="text" name="numero_guia" id="numero_guia" class="form-control @error('numero_guia') is-invalid @enderror" 
                                           value="{{ old('numero_guia') }}" placeholder="Debe tener exactamente 6 dígitos" required pattern="\d{6}" maxlength="6" minlength="6" title="El número de guía debe tener exactamente 6 dígitos">
                                    <div id="guia-error" class="form-text text-danger" style="display:none;">El número de guía es obligatorio y debe tener exactamente 6 dígitos.</div>
                                    @error('numero_guia')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Estado -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="estado" class="form-label fw-semibold">
                                        <i class="fas fa-info-circle me-1 text-muted"></i>
                                        Estado *
                                    </label>
                                    <select name="estado" class="form-select @error('estado') is-invalid @enderror" required disabled>
                                        <option value="recibido" selected>📦 Recibido (entrada)</option>
                                    </select>
                                    <input type="hidden" name="estado" value="recibido">
                                    @error('estado')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Tarifa Manual -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tarifa_manual" class="form-label fw-semibold">
                                        <i class="fas fa-dollar-sign me-1 text-muted"></i>
                                        Tarifa Manual (opcional)
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" name="tarifa_manual" id="tarifa_manual" class="form-control @error('tarifa_manual') is-invalid @enderror" 
                                               value="{{ old('tarifa_manual') }}" placeholder="0.00">
                                        <span class="input-group-text" id="tarifa-loading" style="display:none;">
                                            <i class="fas fa-spinner fa-spin text-primary"></i>
                                        </span>
                                    </div>
                                    <small class="form-text text-muted">Dejar vacío para calcular automáticamente</small>
                                    <small class="form-text text-success" id="tarifa-info" style="display:none;">
                                        <i class="fas fa-check-circle"></i> Tarifa automática aplicada
                                    </small>
                                    @error('tarifa_manual')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Monto Calculado -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="monto_calculado" class="form-label fw-semibold">
                                        <i class="fas fa-calculator me-1 text-muted"></i>
                                        Monto Calculado ($) *
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" name="monto_calculado" class="form-control @error('monto_calculado') is-invalid @enderror" 
                                               value="{{ old('monto_calculado') }}" required placeholder="0.00">
                                    </div>
                                    @error('monto_calculado')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Notas -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="notas" class="form-label fw-semibold">
                                        <i class="fas fa-sticky-note me-1 text-muted"></i>
                                        Notas Adicionales
                                    </label>
                                    <textarea name="notas" class="form-control @error('notas') is-invalid @enderror" 
                                              rows="4" placeholder="Información adicional sobre el paquete...">{{ old('notas') }}</textarea>
                                    @error('notas')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <hr class="my-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('inventario.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>
                                        Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        Guardar Paquete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-semibold text-dark">
                        <i class="fas fa-info-circle me-2 text-info"></i>
                        Información Útil
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="fw-semibold text-dark mb-2">
                            <i class="fas fa-lightbulb text-warning me-1"></i>
                            Consejos
                        </h6>
                        <ul class="list-unstyled small text-muted">
                            <li class="mb-2">• Asegúrate de verificar el peso y volumen</li>
                            <li class="mb-2">• El número de guía es opcional pero recomendado</li>
                            <li class="mb-2">• Las notas ayudan a identificar el paquete</li>
                            <li>• El estado se puede actualizar después</li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="fw-semibold text-dark mb-2">
                            <i class="fas fa-calculator text-primary me-1"></i>
                            Cálculo de Tarifa
                        </h6>
                        <p class="small text-muted mb-0">
                            La tarifa se calcula automáticamente basada en el peso, volumen y tipo de servicio seleccionado.
                        </p>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-semibold text-dark">
                        <i class="fas fa-clock text-success me-2"></i>
                        Estados Disponibles
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-success bg-opacity-10 text-success me-2">📦</span>
                        <small class="text-muted">Recibido - Paquete en almacén</small>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-primary bg-opacity-10 text-primary me-2">✅</span>
                        <small class="text-muted">Entregado - Completado</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.form-label {
    color: #495057;
}

.input-group-text {
    background-color: #f8f9fa;
    border-color: #dee2e6;
    color: #6c757d;
    border-radius: 0.6rem 0 0 0.6rem !important;
    font-size: 1rem;
    min-height: 40px !important;
    padding: 0.45rem 0.9rem !important;
}

/* Mejoras avanzadas para Select2 y campos compactos */
.select2-container--bootstrap4 .select2-selection {
    min-height: 40px;
    border-radius: 0.6rem;
    font-size: 1rem;
    border: 1.3px solid #ced4da;
    padding: 0.45rem 0.9rem;
    background: #fff;
    box-shadow: 0 1px 4px rgba(26,46,117,0.03);
    transition: border-color 0.2s, box-shadow 0.2s;
    display: flex;
    align-items: center;
}
.select2-container--bootstrap4 .select2-selection:focus,
.select2-container--bootstrap4 .select2-selection--single:focus {
    border-color: #1A2E75;
    box-shadow: 0 0 0 0.13rem rgba(26,46,117,0.10);
}
.select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
    color: #495057;
    font-size: 1rem;
    line-height: 1.5rem;
    padding-left: 0;
}
.select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
    height: 40px;
    right: 14px;
    top: 50%;
    width: 22px;
    transform: translateY(-50%);
    display: flex;
    align-items: center;
    justify-content: center;
}
.select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow b {
    border-color: #1A2E75 transparent transparent transparent;
    border-width: 6px 6px 0 6px;
    margin-top: 0;
}
.select2-container--bootstrap4 .select2-dropdown {
    border-radius: 0.6rem;
    box-shadow: 0 4px 16px rgba(26,46,117,0.08);
    font-size: 1rem;
    border: 1.3px solid #ced4da;
    margin-top: 2px;
}
.select2-container--bootstrap4 .select2-results__option {
    padding: 0.6rem 1rem;
    font-size: 1rem;
    border-radius: 0.4rem;
    margin: 2px 0;
}
.select2-container--bootstrap4 .select2-results__option--highlighted {
    background: #1A2E75 !important;
    color: #fff !important;
}
.select2-container--bootstrap4 .select2-results__option[aria-selected=true] {
    background: #5C6AC4 !important;
    color: #fff !important;
}
.select2-container--bootstrap4 .select2-selection__clear {
    color: #BF1E2E;
    font-size: 1.2em;
    margin-right: 8px;
    display: flex;
    align-items: center;
    height: 100%;
    position: absolute;
    left: 0.8rem;
    top: 50%;
    transform: translateY(-50%);
}
.select2-container--bootstrap4 .select2-selection--single {
    display: flex;
    align-items: center;
    position: relative;
    padding-left: 0 !important;
}
.select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
    flex: 1 1 auto;
    display: flex;
    align-items: center;
    padding-left: 0.5em;
}
.select2-container--bootstrap4 .select2-selection--single .select2-selection__clear {
    color: #BF1E2E;
    font-size: 1.1em;
    margin-right: 0.5em;
    position: static;
    display: flex;
    align-items: center;
    height: auto;
    top: auto;
    left: auto;
    transform: none;
}
.select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
    height: 40px;
    right: 14px;
    top: 50%;
    width: 22px;
    transform: translateY(-50%);
    display: flex;
    align-items: center;
    justify-content: center;
    position: absolute;
}
.select2-container--bootstrap4 {
    width: 100% !important;
}
.select2-container--bootstrap4 .select2-selection--single {
    height: 40px !important;
    line-height: 40px !important;
}

/* Unifica y compacta todos los campos del formulario */
.form-control, .form-select, textarea {
    border-radius: 0.6rem !important;
    border: 1.3px solid #ced4da !important;
    font-size: 1rem !important;
    min-height: 40px !important;
    box-shadow: 0 1px 4px rgba(26,46,117,0.03) !important;
    transition: border-color 0.2s, box-shadow 0.2s;
    background: #fff !important;
    padding: 0.45rem 0.9rem !important;
}
.form-control:focus, .form-select:focus, textarea:focus {
    border-color: #1A2E75 !important;
    box-shadow: 0 0 0 0.13rem rgba(26,46,117,0.10) !important;
}
.input-group-text {
    background-color: #f8f9fa;
    border-color: #dee2e6;
    color: #6c757d;
    border-radius: 0.6rem 0 0 0.6rem !important;
    font-size: 1rem;
    min-height: 40px !important;
    padding: 0.45rem 0.9rem !important;
}
textarea.form-control {
    min-height: 90px !important;
    padding-top: 0.7rem !important;
}
/* Alineación vertical de los campos en grid */
.row.g-4 > [class^='col-'],
.row.g-4 > [class*=' col-'] {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}

.autocomplete-wrapper { position: relative; }
#clienteAutocomplete {
    border-radius: 0.75rem;
    border: 1.5px solid #1A2E75;
    font-size: 1.08rem;
    padding: 0.5rem 1.2rem;
    background: #f8fafc;
    color: #1A2E75;
    height: 48px;
    box-shadow: none;
    transition: border 0.18s;
}
#clienteAutocomplete:focus {
    border-color: #5C6AC4;
    box-shadow: 0 0 0 0.15rem rgba(92,106,196,0.10);
}
#autocompleteList {
    border-radius: 0.75rem;
    background: #fff;
    margin-top: 2px;
    border: 1.5px solid #e3e8f0;
    font-size: 1.05rem;
    box-shadow: 0 2px 8px rgba(26,46,117,0.07);
    padding: 0;
    z-index: 20;
}
#autocompleteList li {
    cursor: pointer;
    padding: 0.6rem 1rem;
    border-bottom: 1px solid #f0f0f0;
    transition: background 0.13s;
    list-style: none;
    font-size: 1.08rem;
    color: #1A2E75;
    background: #fff;
}
#autocompleteList li:last-child { border-bottom: none; }
#autocompleteList li:hover, #autocompleteList li.active {
    background: #F0F4FF;
    color: #1A2E75;
    font-weight: 600;
}
</style>

<script>
const clientesList = @json($clientes ?? []);
let selectedClienteId = null;
const input = document.getElementById('clienteAutocomplete');
const list = document.getElementById('autocompleteList');
const hiddenInput = document.getElementById('cliente_id');

function showSuggestions(term) {
    list.innerHTML = '';
    const filtered = clientesList.filter(c => c.nombre_completo.toLowerCase().includes(term.toLowerCase()));
    if (filtered.length === 0) {
        list.style.display = 'none';
        return;
    }
    filtered.forEach(cliente => {
        const li = document.createElement('li');
        li.textContent = cliente.nombre_completo;
        li.onclick = () => {
            input.value = cliente.nombre_completo;
            selectedClienteId = cliente.id;
            hiddenInput.value = cliente.id;
            list.style.display = 'none';
            // Llamar a la función para obtener tarifa automáticamente
            setTimeout(() => {
                if (typeof obtenerTarifaCliente === 'function') obtenerTarifaCliente();
                if (typeof calculateMonto === 'function') calculateMonto();
            }, 100);
        };
        list.appendChild(li);
    });
    list.style.display = 'block';
}

input.addEventListener('input', function() {
    const term = this.value.trim();
    if (term.length === 0) {
        list.style.display = 'none';
        selectedClienteId = null;
        hiddenInput.value = '';
        return;
    }
    showSuggestions(term);
});

input.addEventListener('focus', function() {
    if (this.value.trim().length > 0) showSuggestions(this.value.trim());
});

document.addEventListener('click', function(e) {
    if (!list.contains(e.target) && e.target !== input) {
        list.style.display = 'none';
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-calculate monto based on peso and servicio
    const pesoInput = document.querySelector('input[name="peso_lb"]');
    const servicioSelect = document.querySelector('select[name="servicio_id"]');
    const clienteSelect = document.querySelector('select[name="cliente_id"]');
    const montoInput = document.querySelector('input[name="monto_calculado"]');
    const tarifaManualInput = document.querySelector('input[name="tarifa_manual"]');
    
    function calculateMonto() {
        const peso = parseFloat(pesoInput.value) || 0;
        let rate = 0;
        if (tarifaManualInput.value) {
            rate = parseFloat(tarifaManualInput.value) || 0;
        } else if (clienteSelect.value && servicioSelect.value) {
            rate = parseFloat(tarifaManualInput.value) || 0;
        } else {
            rate = 1.00;
        }
        const monto = peso * rate;
        montoInput.value = monto.toFixed(2);
    }
    
    pesoInput.addEventListener('input', calculateMonto);
    servicioSelect.addEventListener('change', function() {
        obtenerTarifaCliente();
        calculateMonto();
    });
    // También escuchar cambios en el campo de cliente autocompletado
    document.getElementById('cliente_id').addEventListener('change', function() {
        obtenerTarifaCliente();
        calculateMonto();
    });
    tarifaManualInput.addEventListener('input', calculateMonto);

    function obtenerTarifaCliente() {
        const clienteId = document.getElementById('cliente_id').value;
        const servicioId = servicioSelect.value;
        const tarifaLoading = document.getElementById('tarifa-loading');
        const tarifaInfo = document.getElementById('tarifa-info');
        
        console.log('Obteniendo tarifa para cliente:', clienteId, 'servicio:', servicioId);
        
        // Ocultar indicadores previos
        tarifaInfo.style.display = 'none';
        
        if (clienteId && servicioId) {
            // Mostrar loading
            tarifaLoading.style.display = 'inline-flex';
            
            fetch("{{ route('inventario.obtener-tarifa') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                },
                body: JSON.stringify({ cliente_id: clienteId, servicio_id: servicioId })
            })
            .then(res => res.json())
            .then(data => {
                console.log('Tarifa obtenida:', data);
                // Ocultar loading
                tarifaLoading.style.display = 'none';
                
                if (data.tarifa !== null && data.tarifa !== undefined) {
                    tarifaManualInput.value = data.tarifa;
                    console.log('Tarifa aplicada:', data.tarifa);
                    // Mostrar indicador de éxito
                    tarifaInfo.style.display = 'block';
                } else {
                    tarifaManualInput.value = '';
                    console.log('No se encontró tarifa para esta combinación');
                }
                calculateMonto();
            })
            .catch(error => {
                console.error('Error al obtener tarifa:', error);
                tarifaManualInput.value = '';
                tarifaLoading.style.display = 'none';
                calculateMonto();
            });
        } else {
            console.log('Faltan datos: clienteId =', clienteId, 'servicioId =', servicioId);
            tarifaManualInput.value = '';
            calculateMonto();
        }
    }
    
    // Form validation
    const form = document.getElementById('inventarioForm');
    form.addEventListener('submit', function(e) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        if (!isValid) {
            e.preventDefault();
            alert('Por favor, completa todos los campos requeridos.');
        }
    });

    // Validación dinámica para número de guía
    const numeroGuiaInput = document.getElementById('numero_guia');
    const guiaError = document.getElementById('guia-error');
    numeroGuiaInput.addEventListener('input', function() {
        const value = numeroGuiaInput.value.trim();
        if (value.length === 0 || !/^\d{6}$/.test(value)) {
            guiaError.style.display = 'block';
        } else {
            guiaError.style.display = 'none';
        }
    });
    // Validar al enviar el formulario
    const inventarioForm = document.getElementById('inventarioForm');
    inventarioForm.addEventListener('submit', function(e) {
        const value = numeroGuiaInput.value.trim();
        if (value.length !== 6 || !/^\d{6}$/.test(value)) {
            guiaError.style.display = 'block';
            numeroGuiaInput.focus();
            e.preventDefault();
        }
    });
});
</script>

@push('scripts')
<!-- Select2 CSS y JS con tema Bootstrap4 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#selectCliente').select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: 'Seleccione un cliente',
        allowClear: true,
        dropdownParent: $('#selectCliente').parent()
    });
});
</script>
<style>
/* Mejoras avanzadas para Select2 y campos compactos */
.select2-container--bootstrap4 .select2-selection {
    min-height: 40px;
    border-radius: 0.6rem;
    font-size: 1rem;
    border: 1.3px solid #ced4da;
    padding: 0.45rem 0.9rem;
    background: #fff;
    box-shadow: 0 1px 4px rgba(26,46,117,0.03);
    transition: border-color 0.2s, box-shadow 0.2s;
    display: flex;
    align-items: center;
}
.select2-container--bootstrap4 .select2-selection:focus,
.select2-container--bootstrap4 .select2-selection--single:focus {
    border-color: #1A2E75;
    box-shadow: 0 0 0 0.13rem rgba(26,46,117,0.10);
}
.select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
    color: #495057;
    font-size: 1rem;
    line-height: 1.5rem;
    padding-left: 0;
}
.select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
    height: 40px;
    right: 14px;
    top: 50%;
    width: 22px;
    transform: translateY(-50%);
    display: flex;
    align-items: center;
    justify-content: center;
}
.select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow b {
    border-color: #1A2E75 transparent transparent transparent;
    border-width: 6px 6px 0 6px;
    margin-top: 0;
}
.select2-container--bootstrap4 .select2-dropdown {
    border-radius: 0.6rem;
    box-shadow: 0 4px 16px rgba(26,46,117,0.08);
    font-size: 1rem;
    border: 1.3px solid #ced4da;
    margin-top: 2px;
}
.select2-container--bootstrap4 .select2-results__option {
    padding: 0.6rem 1rem;
    font-size: 1rem;
    border-radius: 0.4rem;
    margin: 2px 0;
}
.select2-container--bootstrap4 .select2-results__option--highlighted {
    background: #1A2E75 !important;
    color: #fff !important;
}
.select2-container--bootstrap4 .select2-results__option[aria-selected=true] {
    background: #5C6AC4 !important;
    color: #fff !important;
}
.select2-container--bootstrap4 .select2-selection__clear {
    color: #BF1E2E;
    font-size: 1.2em;
    margin-right: 8px;
    display: flex;
    align-items: center;
    height: 100%;
    position: absolute;
    left: 0.8rem;
    top: 50%;
    transform: translateY(-50%);
}
.select2-container--bootstrap4 .select2-selection--single {
    position: relative;
    padding-left: 2.1em !important;
}
.select2-container--bootstrap4 {
    width: 100% !important;
}
.select2-container--bootstrap4 .select2-selection--single {
    height: 40px !important;
    line-height: 40px !important;
}

/* Unifica y compacta todos los campos del formulario */
.form-control, .form-select, textarea {
    border-radius: 0.6rem !important;
    border: 1.3px solid #ced4da !important;
    font-size: 1rem !important;
    min-height: 40px !important;
    box-shadow: 0 1px 4px rgba(26,46,117,0.03) !important;
    transition: border-color 0.2s, box-shadow 0.2s;
    background: #fff !important;
    padding: 0.45rem 0.9rem !important;
}
.form-control:focus, .form-select:focus, textarea:focus {
    border-color: #1A2E75 !important;
    box-shadow: 0 0 0 0.13rem rgba(26,46,117,0.10) !important;
}
.input-group-text {
    background-color: #f8f9fa;
    border-color: #dee2e6;
    color: #6c757d;
    border-radius: 0.6rem 0 0 0.6rem !important;
    font-size: 1rem;
    min-height: 40px !important;
    padding: 0.45rem 0.9rem !important;
}
textarea.form-control {
    min-height: 90px !important;
    padding-top: 0.7rem !important;
}
/* Alineación vertical de los campos en grid */
.row.g-4 > [class^='col-'],
.row.g-4 > [class*=' col-'] {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}
</style>
@endpush
@endsection
