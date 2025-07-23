@extends('layouts.app')

@section('title', 'Previsualizar Cliente - SkylinkOne CRM')
@section('page-title', 'Previsualizar Cliente')

@section('content')
<div class="container-fluid px-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="rounded-4 shadow-sm px-4 py-4 mb-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(90deg, #1A2E75 0%, #5C6AC4 100%); min-height:90px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width:60px; height:60px; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                        <i class="fas fa-user text-primary" style="font-size:2.2rem;"></i>
                    </div>
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="letter-spacing:1px;">Previsualizar Cliente</h1>
                        <p class="mb-0 text-white-50" style="font-size:1.1rem;">Consulta los datos y tarifas del cliente</p>
                    </div>
                </div>
                <a href="{{ route('clientes.index') }}" class="btn btn-lg fw-semibold shadow-sm px-4" style="background:#fff; color:#1A2E75; border:2px solid #1A2E75; box-shadow:0 2px 8px rgba(26,46,117,0.08); font-size:1.2rem;">
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
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nombre completo</label>
                            <div class="form-control form-control-lg rounded-3 bg-light">{{ $cliente->nombre_completo }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Correo</label>
                            <div class="form-control form-control-lg rounded-3 bg-light">{{ $cliente->correo }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Teléfono</label>
                            <div class="form-control form-control-lg rounded-3 bg-light">{{ $cliente->telefono }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Dirección</label>
                            <div class="form-control form-control-lg rounded-3 bg-light">{{ $cliente->direccion }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tipo de Cliente</label>
                            <div class="form-control form-control-lg rounded-3 bg-light text-capitalize">{{ $cliente->tipo_cliente }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Fecha de Registro</label>
                            <div class="form-control form-control-lg rounded-3 bg-light">{{ $cliente->fecha_registro }}</div>
                        </div>
                    </div>
                    <div class="col-lg-6 p-3" style="min-width:320px;">
                        <h5 class="fw-bold mb-3"><i class="fas fa-dollar-sign text-primary me-2"></i>Tarifas por Servicio</h5>
                        <div class="mb-3 d-flex align-items-center gap-3">
                            <span class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:38px; height:38px;"><i class="fas fa-plane text-primary"></i></span>
                            <label class="form-label mb-0 flex-grow-1">Aéreo</label>
                            <div class="form-control bg-light" style="max-width:120px;">
                                ${{ number_format(optional($tarifas->first(function($t){ return str_replace(['á','é','í','ó','ú'],['a','e','i','o','u'], strtolower($t->servicio->tipo_servicio ?? '')) === 'aereo'; }))->tarifa, 2) }}
                            </div>
                        </div>
                        <div class="mb-3 d-flex align-items-center gap-3">
                            <span class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:38px; height:38px;"><i class="fas fa-ship text-primary"></i></span>
                            <label class="form-label mb-0 flex-grow-1">Marítimo</label>
                            <div class="form-control bg-light" style="max-width:120px;">
                                ${{ number_format(optional($tarifas->first(function($t){ return str_replace(['á','é','í','ó','ú'],['a','e','i','o','u'], strtolower($t->servicio->tipo_servicio ?? '')) === 'maritimo'; }))->tarifa, 2) }}
                            </div>
                        </div>
                        <div class="mb-3 d-flex align-items-center gap-3">
                            <span class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:38px; height:38px;"><i class="fas fa-bolt text-primary"></i></span>
                            <label class="form-label mb-0 flex-grow-1">Pie Cúbico</label>
                            <div class="form-control bg-light" style="max-width:120px;">
                                ${{ number_format(optional($tarifas->first(function($t){
                                    return str_replace([' ', '-', 'á','é','í','ó','ú','Á','É','Í','Ó','Ú'], ['_','_','a','e','i','o','u','a','e','i','o','u'], strtolower($t->servicio->tipo_servicio ?? '')) === 'pie_cubico';
                                }))->tarifa, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
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
</style>
@endsection 