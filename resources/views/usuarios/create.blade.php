@extends('layouts.app')

@section('title', 'Usuarios - CH Logistics ERP')
@section('page-title', 'Gestión de Usuarios')

@section('content')
<div class="container-fluid px-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="rounded-4 shadow-sm px-4 py-4 mb-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(90deg, #15537c 0%, #2d6a9a 100%); min-height:90px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width:60px; height:60px; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                        <i class="fas fa-users text-primary" style="font-size:2.2rem;"></i>
                    </div>
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="letter-spacing:1px;">Usuarios</h1>
                        <p class="mb-0 text-white-50" style="font-size:1.1rem;">Registra nuevos usuarios para el sistema</p>
                    </div>
                </div>
                <a href="{{ route('usuarios.create') }}" class="btn btn-lg fw-semibold shadow-sm px-4" style="background:#15537c; color:#fff;">
                    <i class="fas fa-user-plus me-2"></i> Nuevo Usuario
                </a>
            </div>
        </div>
    </div>
    <!-- Form Card -->
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form action="{{ route('usuarios.store') }}" method="POST" autocomplete="off">
                        @csrf
                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-semibold">Nombre</label>
                            <input type="text" name="nombre" class="form-control form-control-lg rounded-3 @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}">
                            @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control form-control-lg rounded-3 @error('email') is-invalid @enderror" value="{{ old('email') }}">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Contraseña</label>
                            <input type="password" name="password" class="form-control form-control-lg rounded-3 @error('password') is-invalid @enderror">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="rol" class="form-label fw-semibold">Rol</label>
                            <select name="rol" class="form-select form-select-lg rounded-3 @error('rol') is-invalid @enderror">
                                <option value="admin" {{ old('rol') == 'admin' ? 'selected' : '' }}>Administrador</option>
                                <option value="agente" {{ old('rol') == 'agente' ? 'selected' : '' }}>Agente</option>
                                <option value="auditor" {{ old('rol') == 'auditor' ? 'selected' : '' }}>Auditor</option>
                                <option value="basico" {{ old('rol') == 'basico' ? 'selected' : '' }}>Básico</option>
                            </select>
                            @error('rol') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-check form-switch mb-4 ps-0 d-flex align-items-center gap-2">
                            <input class="form-check-input ms-0" type="checkbox" role="switch" name="estado" id="estado" value="1" {{ old('estado') ? 'checked' : '' }} style="height:1.5em;width:2.5em;">
                            <label class="form-check-label fw-semibold" for="estado">Activo</label>
                        </div>
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-semibold" style="font-size:1.1rem;">Guardar Usuario</button>
                            <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3 fw-semibold" style="font-size:1.1rem;">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
