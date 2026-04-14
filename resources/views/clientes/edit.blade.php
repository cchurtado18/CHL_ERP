@extends('layouts.app')

@section('title', 'Editar Cliente - CH Logistics ERP')
@section('page-title', 'Editar Cliente')

@section('content')
<div class="container-fluid px-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="rounded-4 shadow-sm px-4 py-4 mb-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(90deg, #15537c 0%, #2d6a9a 100%); min-height:90px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width:60px; height:60px; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                        <i class="fas fa-user-edit text-primary" style="font-size:2.2rem;"></i>
                    </div>
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="letter-spacing:1px;">Editar Cliente</h1>
                        <p class="mb-0 text-white-50" style="font-size:1.1rem;">Modifica los datos del cliente seleccionado</p>
                    </div>
                </div>
                <a href="{{ route('clientes.index') }}" class="btn btn-lg fw-semibold shadow-sm px-4" style="background:#fff; color:#15537c; border:2px solid #15537c; box-shadow:0 2px 8px rgba(21,83,124,0.08); font-size:1.2rem;">
                    <i class="fas fa-arrow-left me-2"></i> Volver
                </a>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card p-4">
                <div class="row g-0 align-items-stretch">
                    <form id="cliente-form" method="POST" action="{{ route('clientes.update', $cliente->id) }}" class="d-flex flex-wrap">
                        @csrf
                        @method('PUT')
                        <div class="col-lg-6 p-3 border-end" style="min-width:320px;">
                            <div class="mb-3">
                                <label for="nombre_completo" class="form-label fw-semibold">Nombre completo</label>
                                <input type="text" class="form-control form-control-lg rounded-3" id="nombre_completo" name="nombre_completo" value="{{ old('nombre_completo', $cliente->nombre_completo) }}" required>
                                @error('nombre_completo') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="correo" class="form-label fw-semibold">Correo</label>
                                <input type="email" class="form-control form-control-lg rounded-3" id="correo" name="correo" value="{{ old('correo', $cliente->correo) }}">
                                @error('correo') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="telefono" class="form-label fw-semibold">Teléfono</label>
                                <input type="text" class="form-control form-control-lg rounded-3" id="telefono" name="telefono" value="{{ old('telefono', $cliente->telefono) }}">
                                @error('telefono') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="direccion" class="form-label fw-semibold">Dirección</label>
                                <textarea class="form-control form-control-lg rounded-3" id="direccion" name="direccion" rows="2">{{ old('direccion', $cliente->direccion) }}</textarea>
                                @error('direccion') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-4">
                                <label for="tipo_cliente" class="form-label fw-semibold">Tipo de Cliente</label>
                                <select class="form-select form-select-lg rounded-3" id="tipo_cliente" name="tipo_cliente">
                                    <option value="normal" {{ old('tipo_cliente', $cliente->tipo_cliente) == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="subagencia" {{ old('tipo_cliente', $cliente->tipo_cliente) == 'subagencia' ? 'selected' : '' }}>Subagencia</option>
                                </select>
                                @error('tipo_cliente') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-lg-6 p-3 d-flex flex-column justify-content-between" style="min-width:320px;">
                            <div>
                                <h5 class="fw-bold mb-3"><i class="fas fa-dollar-sign text-primary me-2"></i>Tarifas por Servicio</h5>
                                <div class="mb-3 d-flex align-items-center gap-3">
                                    <span class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:38px; height:38px;"><i class="fas fa-plane text-primary"></i></span>
                                    <label class="form-label mb-0 flex-grow-1">Aéreo <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" class="form-control tarifa-input" id="tarifa_aereo" name="tarifa_aereo" placeholder="0.00" required style="max-width:120px;" value="{{ $tarifas->first(function($t){ return str_replace(['á','é','í','ó','ú'],['a','e','i','o','u'], strtolower($t->servicio->tipo_servicio ?? '')) === 'aereo'; })->tarifa ?? '' }}">
                                </div>
                                <div class="mb-3 d-flex align-items-center gap-3">
                                    <span class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:38px; height:38px;"><i class="fas fa-ship text-primary"></i></span>
                                    <label class="form-label mb-0 flex-grow-1">Marítimo <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" class="form-control tarifa-input" id="tarifa_maritimo" name="tarifa_maritimo" placeholder="0.00" required style="max-width:120px;" value="{{ $tarifas->first(function($t){ return str_replace(['á','é','í','ó','ú'],['a','e','i','o','u'], strtolower($t->servicio->tipo_servicio ?? '')) === 'maritimo'; })->tarifa ?? '' }}">
                                </div>
                                <div class="mb-3 d-flex align-items-center gap-3">
                                    <span class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:38px; height:38px;"><i class="fas fa-bolt text-primary"></i></span>
                                    <label class="form-label mb-0 flex-grow-1">Pie Cúbico</label>
                                    <input type="number" step="0.01" min="0" class="form-control tarifa-input" id="tarifa_pie_cubico" name="tarifa_pie_cubico" placeholder="0.00" style="max-width:120px;" value="{{ $tarifas->first(function($t){
    $nombre = strtolower(str_replace(['á','é','í','ó','ú'],['a','e','i','o','u'], $t->servicio->tipo_servicio ?? ''));
    $nombre = str_replace([' ', '-'], '_', $nombre);
    return $nombre === 'pie_cubico';
})->tarifa ?? '' }}">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary px-4 py-2 fw-bold shadow-sm w-100 mt-4 align-self-end" id="btn_actualizar"><i class="fas fa-save me-2"></i>Actualizar Cliente</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
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
</style>
@endsection
