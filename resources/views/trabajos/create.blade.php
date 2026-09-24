@extends('layouts.app-new')

@section('title', 'Asignar trabajo')
@section('navbar-title', 'Asignar trabajo')

@section('content')
<div class="mx-auto w-full max-w-2xl space-y-6">
    @include('leads._nav')

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">Asignar trabajo</h1>
        <p class="mt-1 text-sm text-slate-600">Solo título, a quién va y qué tan urgente es. El cliente es opcional.</p>

        @if($errors->any())
            <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                <ul class="list-disc space-y-1 pl-5 text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('leads.trabajos.store') }}" class="mt-6 space-y-5">
            @csrf
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Título del trabajo *</label>
                <input type="text" name="titulo" value="{{ old('titulo') }}" required maxlength="200" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" placeholder="Ej. Llevar documentos / Revisar paquete / Ir a sucursal">
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Asignar a *</label>
                <select name="asignado_a" required class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
                    <option value="">Seleccionar persona...</option>
                    @foreach($equipo as $u)
                        <option value="{{ $u->id }}" @selected(old('asignado_a') == $u->id)>{{ $u->nombre ?? $u->email }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Prioridad *</label>
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                    @foreach(\App\Models\Trabajo::PRIORIDADES as $prio)
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-3 text-sm hover:border-[#15537c]/40 {{ old('prioridad', 'promedio') === $prio ? 'ring-2 ring-[#15537c]' : '' }}">
                            <input type="radio" name="prioridad" value="{{ $prio }}" class="text-[#15537c] focus:ring-[#15537c]" @checked(old('prioridad', 'promedio') === $prio) required>
                            <span class="rounded px-2 py-0.5 font-semibold {{ \App\Models\Trabajo::PRIORIDAD_COLORS[$prio] }}">{{ \App\Models\Trabajo::PRIORIDAD_LABELS[$prio] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Cliente <span class="font-normal text-slate-400">(opcional)</span></label>
                <select name="cliente_id" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
                    <option value="">Sin cliente</option>
                    @foreach($clientes as $c)
                        <option value="{{ $c->id }}" @selected(old('cliente_id') == $c->id)>{{ $c->nombre_completo }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Fecha <span class="font-normal text-slate-400">(opcional)</span></label>
                    <input type="date" name="fecha" value="{{ old('fecha', now()->format('Y-m-d')) }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Hora <span class="font-normal text-slate-400">(opcional)</span></label>
                    <input type="time" name="hora" value="{{ old('hora', '09:00') }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Notas <span class="font-normal text-slate-400">(opcional)</span></label>
                <textarea name="descripcion" rows="3" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" placeholder="Detalle extra si hace falta...">{{ old('descripcion') }}</textarea>
            </div>

            <div class="flex flex-wrap gap-3 pt-1">
                <button type="submit" class="rounded-xl bg-orange-600 px-5 py-2.5 text-base font-semibold text-white hover:bg-orange-700">Asignar trabajo</button>
                <a href="{{ route('leads.trabajos.calendar') }}" class="rounded-xl border border-slate-300 px-5 py-2.5 text-base font-medium text-slate-700 hover:bg-slate-50">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
