@extends('layouts.app')

@section('title', 'Editar Notificación - CH Logistics ERP')

@section('content')
<div class="container-fluid px-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="rounded-4 shadow-sm px-4 py-4 mb-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(90deg, #15537c 0%, #2d6a9a 100%); min-height:90px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width:60px; height:60px; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                        <i class="fas fa-bell text-primary" style="font-size:2.2rem;"></i>
                    </div>
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="letter-spacing:1px;">Editar Notificación</h1>
                        <p class="mb-0 text-white-50" style="font-size:1.1rem;">Modifica los datos de la notificación seleccionada</p>
                    </div>
                </div>
                <a href="{{ route('notificaciones.index') }}" class="btn btn-outline-light fw-semibold shadow-sm px-4">
                    <i class="fas fa-arrow-left me-2"></i> Volver a Notificaciones
                </a>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <form action="{{ route('notificaciones.update', $notificacion) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label for="user_id" class="form-label fw-semibold">Usuario Destinatario *</label>
                                <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror">
                                    <option value="">Selecciona un usuario</option>
                                    @foreach($usuarios as $usuario)
                                        <option value="{{ $usuario->id }}" {{ (old('user_id', $notificacion->user_id) == $usuario->id) ? 'selected' : '' }}>
                                            {{ $usuario->name }} ({{ $usuario->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12">
                                <label for="titulo" class="form-label fw-semibold">Título *</label>
                                <input type="text" name="titulo" id="titulo" value="{{ old('titulo', $notificacion->titulo) }}" class="form-control @error('titulo') is-invalid @enderror" placeholder="Ingresa el título de la notificación">
                                @error('titulo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12">
                                <label for="mensaje" class="form-label fw-semibold">Mensaje *</label>
                                <textarea name="mensaje" id="mensaje" rows="6" class="form-control @error('mensaje') is-invalid @enderror" placeholder="Ingresa el mensaje de la notificación">{{ old('mensaje', $notificacion->mensaje) }}</textarea>
                                @error('mensaje')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input type="checkbox" name="leido" value="1" id="leido" class="form-check-input" {{ $notificacion->leido ? 'checked' : '' }}>
                                    <label for="leido" class="form-check-label">Marcar como leída</label>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('notificaciones.index') }}" class="btn btn-outline-secondary px-4">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-2"></i>Actualizar Notificación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 