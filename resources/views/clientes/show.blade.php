@extends('layouts.app-new')

@section('title', 'Previsualizar Cliente - CH Logistics')
@section('navbar-title', 'Previsualizar Cliente')

@section('content')
@php
    $tarifaAereo = $tarifas->first(fn($t) => str_replace(['á','é','í','ó','ú'], ['a','e','i','o','u'], strtolower($t->servicio->tipo_servicio ?? '')) === 'aereo');
    $tarifaMaritimo = $tarifas->first(fn($t) => str_replace(['á','é','í','ó','ú'], ['a','e','i','o','u'], strtolower($t->servicio->tipo_servicio ?? '')) === 'maritimo');
    $tarifaPieCubico = $tarifas->first(fn($t) => str_replace([' ', '-', 'á','é','í','ó','ú'], ['_','_','a','e','i','o','u'], strtolower($t->servicio->tipo_servicio ?? '')) === 'pie_cubico');
@endphp
<div class="mx-auto w-full max-w-7xl px-4 sm:px-6 space-y-8">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl px-5 py-4 shadow-sm" style="background: linear-gradient(90deg, #15537c 0%, #2d6a9a 100%); min-height: 90px;">
        <div class="flex items-center gap-3">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-white shadow-sm">
                <i class="fas fa-user text-[#15537c]" style="font-size: 1.75rem;"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-wide text-white">Previsualizar Cliente</h1>
                <p class="text-sm text-white/80">Consulta los datos y tarifas del cliente</p>
            </div>
        </div>
        <a href="{{ route('clientes.index') }}" class="inline-flex items-center gap-2 rounded-lg border-2 border-white/80 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/20">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    {{-- Card detalle --}}
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
        <div class="grid gap-8 lg:grid-cols-2">
            {{-- Datos del cliente --}}
            <div class="min-w-0 space-y-4 border-slate-200 lg:border-r lg:pr-8">
                <div>
                    <p class="mb-1.5 text-sm font-medium text-slate-500">Nombre completo</p>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-slate-800">{{ $cliente->nombre_completo }}</div>
                </div>
                <div>
                    <p class="mb-1.5 text-sm font-medium text-slate-500">Correo</p>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-slate-800">{{ $cliente->correo ?? '-' }}</div>
                </div>
                <div>
                    <p class="mb-1.5 text-sm font-medium text-slate-500">Teléfono</p>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-slate-800">{{ $cliente->telefono ?? '-' }}</div>
                </div>
                <div>
                    <p class="mb-1.5 text-sm font-medium text-slate-500">Dirección</p>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-slate-800">{{ $cliente->direccion ?? '-' }}</div>
                </div>
                <div>
                    <p class="mb-1.5 text-sm font-medium text-slate-500">Tipo de cliente</p>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-slate-800 capitalize">{{ $cliente->tipo_cliente }}</div>
                </div>
                <div>
                    <p class="mb-1.5 text-sm font-medium text-slate-500">Fecha de registro</p>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-slate-800">{{ $cliente->fecha_registro }}</div>
                </div>
            </div>

            {{-- Tarifas por servicio --}}
            <div class="min-w-0 space-y-4 lg:pl-4">
                <h2 class="text-lg font-semibold text-slate-800 border-b border-slate-100 pb-2 mb-2">
                    <i class="fas fa-dollar-sign text-[#15537c] mr-2"></i>Tarifas por servicio
                </h2>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#15537c]/10 text-[#15537c]">
                        <i class="fas fa-plane"></i>
                    </div>
                    <label class="min-w-0 flex-1 text-sm font-medium text-slate-600">Aéreo</label>
                    <div class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-800 sm:w-32">${{ number_format(optional($tarifaAereo)->tarifa ?? 0, 2) }}</div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#15537c]/10 text-[#15537c]">
                        <i class="fas fa-ship"></i>
                    </div>
                    <label class="min-w-0 flex-1 text-sm font-medium text-slate-600">Marítimo</label>
                    <div class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-800 sm:w-32">${{ number_format(optional($tarifaMaritimo)->tarifa ?? 0, 2) }}</div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#15537c]/10 text-[#15537c]">
                        <i class="fas fa-cube"></i>
                    </div>
                    <label class="min-w-0 flex-1 text-sm font-medium text-slate-600">Pie cúbico</label>
                    <div class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-800 sm:w-32">${{ number_format(optional($tarifaPieCubico)->tarifa ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Acceso al portal --}}
    @php $portalUser = $cliente->usuarioPortal; @endphp
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-800">
                    <i class="fas fa-key text-[#15537c] mr-2"></i>Acceso al portal del cliente
                </h2>
                <p class="mt-1 text-sm text-slate-500">Crea credenciales para que el cliente vea sus paquetes y facturas.</p>
            </div>
            @if($portalUser)
                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $portalUser->estado ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                    {{ $portalUser->estado ? 'Activo' : 'Desactivado' }}
                </span>
            @endif
        </div>

        @if($errors->has('portal'))
            <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first('portal') }}</div>
        @endif

        @if(session('portal_credenciales'))
            <div class="mb-4 rounded-xl border border-amber-300 bg-amber-50 px-4 py-4 text-sm text-amber-900">
                <p class="font-bold mb-2"><i class="fas fa-exclamation-triangle mr-1"></i> Anota estas credenciales (solo se muestran una vez):</p>
                <p><strong>Correo:</strong> {{ session('portal_credenciales')['email'] }}</p>
                <p><strong>Contraseña:</strong> <code class="rounded bg-white px-2 py-0.5 font-mono text-base">{{ session('portal_credenciales')['password'] }}</code></p>
                <p class="mt-2 text-amber-800">Entrada: <a href="{{ route('login') }}" class="underline font-semibold" target="_blank">{{ route('login') }}</a> → se redirige al portal.</p>
            </div>
        @endif

        @if(!$portalUser)
            <form method="POST" action="{{ route('clientes.portal.store', $cliente->id) }}" class="space-y-4 max-w-xl">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Correo de acceso</label>
                    <input type="email" name="email" value="{{ old('email', $cliente->correo) }}"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        placeholder="Se usará el correo del cliente si lo dejas vacío">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Contraseña (opcional)</label>
                    <input type="text" name="password" minlength="8"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        placeholder="Si la dejas vacía se genera una automática">
                </div>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-[#15537c] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#0f3d5c]"
                    @disabled(! $cliente->correo)>
                    <i class="fas fa-user-plus"></i> Crear acceso al portal
                </button>
                @unless($cliente->correo)
                    <p class="text-sm text-rose-600">Agrega un correo al cliente antes de crear el acceso.</p>
                @endunless
            </form>
        @else
            <div class="mb-4 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                <p><strong>Usuario portal:</strong> {{ $portalUser->email }}</p>
                <p class="text-slate-500">Rol: cliente · ID usuario: {{ $portalUser->id }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <form method="POST" action="{{ route('clientes.portal.reset', $cliente->id) }}" class="flex flex-wrap items-end gap-2">
                    @csrf
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-500">Nueva contraseña (opcional)</label>
                        <input type="text" name="password" minlength="8"
                            class="rounded-lg border border-slate-300 px-3 py-2 text-sm"
                            placeholder="Auto si vacío">
                    </div>
                    <button type="submit" class="rounded-lg border border-[#15537c] px-4 py-2 text-sm font-semibold text-[#15537c] hover:bg-[#15537c]/5">
                        Resetear contraseña
                    </button>
                </form>
                <form method="POST" action="{{ route('clientes.portal.toggle', $cliente->id) }}">
                    @csrf
                    <button type="submit" class="rounded-lg px-4 py-2 text-sm font-semibold {{ $portalUser->estado ? 'border border-rose-300 text-rose-700 hover:bg-rose-50' : 'bg-emerald-600 text-white hover:bg-emerald-700' }}">
                        {{ $portalUser->estado ? 'Desactivar acceso' : 'Reactivar acceso' }}
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
