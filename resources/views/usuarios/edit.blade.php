@extends('layouts.app-new')

@section('title', 'Editar Usuario - CH Logistics')
@section('navbar-title', 'Editar Usuario')

@section('content')
@php
    $permisosActuales = old('permisos', $usuario->permisos ?? []);
    if (!is_array($permisosActuales)) {
        $permisosActuales = [];
    }
@endphp
<div class="mx-auto w-full max-w-4xl space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Editar usuario</h1>
            <p class="mt-1 text-base text-slate-600">Actualiza datos y módulos permitidos para <strong>{{ $usuario->nombre }}</strong>.</p>
        </div>
        <a href="{{ route('usuarios.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST" class="space-y-6" autocomplete="off" id="formUsuario">
        @csrf
        @method('PUT')

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            <h2 class="text-lg font-semibold text-slate-800">Datos básicos</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-600">Nombre</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $usuario->nombre) }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c] @error('nombre') border-rose-400 @enderror">
                    @error('nombre') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-600">Correo electrónico</label>
                    <input type="email" name="email" value="{{ old('email', $usuario->email) }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c] @error('email') border-rose-400 @enderror">
                    @error('email') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-600">Nueva contraseña <span class="font-normal text-slate-400">(opcional)</span></label>
                    <input type="password" name="password" placeholder="Dejar vacío para no cambiar" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c] @error('password') border-rose-400 @enderror">
                    @error('password') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-600">Rol</label>
                    <select name="rol" id="rolSelect" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c] @error('rol') border-rose-400 @enderror">
                        <option value="admin" {{ old('rol', $usuario->rol) == 'admin' ? 'selected' : '' }}>Administrador (acceso total)</option>
                        <option value="agente" {{ old('rol', $usuario->rol) == 'agente' ? 'selected' : '' }}>Agente</option>
                        <option value="auditor" {{ old('rol', $usuario->rol) == 'auditor' ? 'selected' : '' }}>Auditor</option>
                        <option value="basico" {{ old('rol', $usuario->rol) == 'basico' ? 'selected' : '' }}>Básico</option>
                    </select>
                    @error('rol') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-end">
                    <label class="inline-flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="estado" value="1" class="h-5 w-5 rounded border-slate-300 text-[#15537c] focus:ring-[#15537c]" {{ old('estado', $usuario->estado) ? 'checked' : '' }}>
                        <span class="text-base font-medium text-slate-700">Usuario activo</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm" id="permisosBox">
            <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">Permisos por módulo</h2>
                    <p class="mt-1 text-sm text-slate-500">Para que registre cobros sin ser admin, marca <strong>Registrar cobros</strong>.</p>
                </div>
                <div class="flex gap-2">
                    <button type="button" id="btnAll" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Todos</button>
                    <button type="button" id="btnNone" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Ninguno</button>
                </div>
            </div>

            <div id="adminNotice" class="mb-4 hidden rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                El rol <strong>Administrador</strong> tiene acceso a todos los módulos automáticamente.
            </div>

            <div class="grid gap-3 sm:grid-cols-2" id="permisosGrid">
                @foreach($modulos as $key => $mod)
                <label class="permiso-item flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-3 hover:border-[#15537c]/40 hover:bg-slate-50 {{ $key === 'contabilidad.cobros' ? 'border-emerald-300 bg-emerald-50/50' : '' }}">
                    <input type="checkbox" name="permisos[]" value="{{ $key }}" class="permiso-check mt-1 h-4 w-4 rounded border-slate-300 text-[#15537c] focus:ring-[#15537c]"
                        {{ in_array($key, $permisosActuales, true) || $usuario->rol === 'admin' ? 'checked' : '' }}>
                    <span>
                        <span class="flex items-center gap-2 font-semibold text-slate-800">
                            <i class="fas {{ $mod['icono'] }} text-[#15537c] w-5 text-center"></i>
                            {{ $mod['label'] }}
                        </span>
                        <span class="mt-0.5 block text-sm text-slate-500">{{ $mod['descripcion'] }}</span>
                    </span>
                </label>
                @endforeach
            </div>
            @error('permisos') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
            @error('permisos.*') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('usuarios.index') }}" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-base font-medium text-slate-700 hover:bg-slate-50">Cancelar</a>
            <button type="submit" class="rounded-xl bg-[#15537c] px-6 py-2.5 text-base font-semibold text-white shadow-sm hover:bg-[#0f3d5c]">Actualizar usuario</button>
        </div>
    </form>
</div>

<script>
(function () {
    const rol = document.getElementById('rolSelect');
    const notice = document.getElementById('adminNotice');
    const grid = document.getElementById('permisosGrid');
    const checks = () => document.querySelectorAll('.permiso-check');

    function syncAdmin() {
        const isAdmin = rol.value === 'admin';
        notice.classList.toggle('hidden', !isAdmin);
        grid.style.opacity = isAdmin ? '0.45' : '1';
        checks().forEach(c => {
            c.disabled = isAdmin;
            if (isAdmin) c.checked = true;
        });
    }

    document.getElementById('btnAll').addEventListener('click', () => {
        if (rol.value === 'admin') return;
        checks().forEach(c => c.checked = true);
    });
    document.getElementById('btnNone').addEventListener('click', () => {
        if (rol.value === 'admin') return;
        checks().forEach(c => c.checked = false);
    });
    rol.addEventListener('change', syncAdmin);
    syncAdmin();
})();
</script>
@endsection
