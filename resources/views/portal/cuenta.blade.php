@extends('layouts.portal')

@section('title', 'Mi cuenta')
@section('navbar-title', 'Mi cuenta')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-extrabold text-slate-800">Mi cuenta</h1>
    <p class="mt-1 text-sm text-slate-500">Datos de acceso y cambio de contraseña.</p>
</div>

<div class="grid gap-6 lg:grid-cols-2">
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 font-bold text-slate-800">Datos</h2>
        <dl class="space-y-3 text-sm">
            <div>
                <dt class="text-slate-400">Nombre</dt>
                <dd class="font-semibold">{{ $user->nombre }}</dd>
            </div>
            <div>
                <dt class="text-slate-400">Correo de acceso</dt>
                <dd class="font-semibold">{{ $user->email }}</dd>
            </div>
            <div>
                <dt class="text-slate-400">Teléfono</dt>
                <dd class="font-semibold">{{ $cliente->telefono ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-slate-400">Dirección</dt>
                <dd class="font-semibold">{{ $cliente->direccion ?? '—' }}</dd>
            </div>
        </dl>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 font-bold text-slate-800">Cambiar contraseña</h2>
        <form method="POST" action="{{ route('portal.cuenta.password') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Contraseña actual</label>
                <input type="password" name="password_actual" required
                    class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Nueva contraseña</label>
                <input type="password" name="password" required minlength="8"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Confirmar nueva contraseña</label>
                <input type="password" name="password_confirmation" required minlength="8"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
            </div>
            <button type="submit" class="rounded-xl bg-[#15537c] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#0f3d5c]">
                Guardar contraseña
            </button>
        </form>
    </section>
</div>
@endsection
