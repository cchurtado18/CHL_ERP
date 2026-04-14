@extends('layouts.app')

@section('title', 'Editar Tracking - CH Logistics ERP')

@section('content')
<div class="container-fluid px-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="rounded-4 shadow-sm px-4 py-4 mb-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(90deg, #15537c 0%, #2d6a9a 100%); min-height:90px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width:60px; height:60px; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                        <i class="fas fa-stopwatch text-primary" style="font-size:2.2rem;"></i>
                    </div>
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="letter-spacing:1px;">Editar Tracking</h1>
                        <p class="mb-0 text-white-50" style="font-size:1.1rem;">Modifica los datos del seguimiento con temporizador</p>
                    </div>
                </div>
                <a href="{{ route('tracking.dashboard') }}" class="btn btn-outline-light fw-semibold shadow-sm px-4">
                    <i class="fas fa-arrow-left me-2"></i> Volver al Dashboard
                </a>
            </div>
        </div>
    </div>
    <!-- Form Card -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-semibold text-dark">
                        <i class="fas fa-edit me-2 text-primary"></i>
                        Información del Tracking
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('tracking.update', $tracking) }}" method="POST" id="trackingForm">
                        @csrf
                        @method('PUT')
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="cliente_id" class="form-label fw-semibold">
                                    <i class="fas fa-user me-1 text-muted"></i>
                                    Cliente <span class="text-danger">*</span>
                                </label>
                                <select name="cliente_id" id="cliente_id" class="form-select @error('cliente_id') is-invalid @enderror" required>
                                    <option value="">Selecciona un cliente</option>
                                    @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" {{ old('cliente_id', $tracking->cliente_id) == $cliente->id ? 'selected' : '' }}>
                                            {{ $cliente->nombre_completo }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('cliente_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="tracking_codigo" class="form-label fw-semibold">
                                    <i class="fas fa-barcode me-1 text-muted"></i>
                                    Código de Tracking <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="tracking_codigo" id="tracking_codigo" value="{{ old('tracking_codigo', $tracking->tracking_codigo) }}" class="form-control @error('tracking_codigo') is-invalid @enderror" placeholder="Ej: TRK-2024-001" required>
                                @error('tracking_codigo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="estado" class="form-label fw-semibold">
                                    <i class="fas fa-info-circle me-1 text-muted"></i>
                                    Estado <span class="text-danger">*</span>
                                </label>
                                <select name="estado" id="estado" class="form-select @error('estado') is-invalid @enderror" required>
                                    <option value="">Selecciona un estado</option>
                                    <option value="pendiente" {{ old('estado', $tracking->estado) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="en_proceso" {{ old('estado', $tracking->estado) == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                                    <option value="completado" {{ old('estado', $tracking->estado) == 'completado' ? 'selected' : '' }}>Completado</option>
                                    <option value="cancelado" {{ old('estado', $tracking->estado) == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                                </select>
                                @error('estado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="duracion_horas" class="form-label fw-semibold">
                                    <i class="fas fa-hourglass-half me-1 text-muted"></i>
                                    Duración del Temporizador <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="duracion_horas" id="duracion_horas" value="{{ old('duracion_horas', $tracking->duracion_horas) }}" class="form-control @error('duracion_horas') is-invalid @enderror" min="1" max="720" required>
                                    <span class="input-group-text">horas</span>
                                </div>
                                <small class="form-text text-muted">Máximo 30 días (720 horas)</small>
                                @error('duracion_horas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="recordatorio_fecha" class="form-label fw-semibold">
                                    <i class="fas fa-calendar-alt me-1 text-muted"></i>
                                    Fecha y Hora del Recordatorio <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" name="recordatorio_fecha" id="recordatorio_fecha" value="{{ old('recordatorio_fecha', $tracking->recordatorio_fecha ? \Carbon\Carbon::parse($tracking->recordatorio_fecha)->format('Y-m-d\TH:i') : '') }}" class="form-control @error('recordatorio_fecha') is-invalid @enderror" required>
                                @error('recordatorio_fecha')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-stopwatch me-1 text-muted"></i>
                                    Vista Previa del Temporizador
                                </label>
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <div id="temporizadorPreview" class="h4 text-primary">
                                            <i class="fas fa-clock me-2"></i>Configura la fecha
                                        </div>
                                        <small class="text-muted">Tiempo restante hasta el recordatorio</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="nota" class="form-label fw-semibold">
                                    <i class="fas fa-sticky-note me-1 text-muted"></i>
                                    Nota Adicional
                                </label>
                                <textarea name="nota" id="nota" rows="4" class="form-control @error('nota') is-invalid @enderror" placeholder="Agrega una nota o descripción del tracking...">{{ old('nota', $tracking->nota) }}</textarea>
                                @error('nota')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!-- Información del Temporizador -->
                        <div class="alert alert-info mt-4">
                            <div class="row">
                                <div class="col-md-8">
                                    <h6 class="alert-heading">
                                        <i class="fas fa-info-circle me-2"></i>Información del Temporizador
                                    </h6>
                                    <ul class="mb-0 small">
                                        <li>Se enviará una notificación automática cuando venza el tiempo</li>
                                        <li>El tracking se marcará automáticamente como "vencido"</li>
                                        <li>Puedes actualizar el estado manualmente en cualquier momento</li>
                                        <li>Las notificaciones se enviarán a todos los usuarios del sistema</li>
                                    </ul>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="h5 text-info mb-2">
                                        <i class="fas fa-bell"></i>
                                    </div>
                                    <small class="text-muted">Notificaciones Automáticas</small>
                                </div>
                            </div>
                        </div>
                        <!-- Botones -->
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('tracking.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                Actualizar Tracking
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 