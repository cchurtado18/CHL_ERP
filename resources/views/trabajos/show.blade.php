@extends('layouts.app-new')

@section('title', 'Detalle trabajo')
@section('navbar-title', 'Detalle del trabajo')

@section('content')
<div class="mx-auto w-full max-w-3xl space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-red-800">
            <ul class="list-disc space-y-1 pl-5 text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    @include('leads._nav')

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ $trabajo->colorEstado() }}">{{ $trabajo->labelEstado() }}</span>
                    <span class="inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ $trabajo->colorPrioridad() }}">{{ $trabajo->labelPrioridad() }}</span>
                </div>
                <h1 class="mt-3 text-2xl font-bold text-slate-900">{{ $trabajo->titulo }}</h1>
                <p class="mt-1 text-sm text-slate-600">Programado: {{ $trabajo->fecha_programada->format('d/m/Y H:i') }}</p>
            </div>
            <a href="{{ route('leads.trabajos.calendar', ['mes' => $trabajo->fecha_programada->format('Y-m')]) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm">← Calendario</a>
        </div>

        <dl class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="rounded-lg border border-slate-100 bg-slate-50 p-4">
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Cliente</dt>
                <dd class="mt-1 text-base font-semibold text-slate-900">{{ $trabajo->cliente->nombre_completo ?? 'Sin cliente' }}</dd>
            </div>
            <div class="rounded-lg border border-slate-100 bg-slate-50 p-4">
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Asignado a</dt>
                <dd class="mt-1 text-base font-semibold text-slate-900">{{ $trabajo->asignado->nombre ?? $trabajo->asignado->email ?? '—' }}</dd>
            </div>
            <div class="rounded-lg border border-slate-100 bg-slate-50 p-4">
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Asignado por</dt>
                <dd class="mt-1 text-base font-semibold text-slate-900">{{ $trabajo->asignador->nombre ?? $trabajo->asignador->email ?? '—' }}</dd>
            </div>
            <div class="rounded-lg border border-slate-100 bg-slate-50 p-4">
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Creado</dt>
                <dd class="mt-1 text-base font-semibold text-slate-900">{{ $trabajo->created_at->format('d/m/Y H:i') }}</dd>
            </div>
        </dl>

        @if($trabajo->descripcion)
            <div class="mt-5">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Instrucciones</h2>
                <p class="mt-2 whitespace-pre-wrap rounded-lg border border-slate-100 bg-white p-4 text-base text-slate-800">{{ $trabajo->descripcion }}</p>
            </div>
        @endif

        @if($trabajo->nota_estado)
            <div class="mt-4 rounded-lg border border-amber-100 bg-amber-50 p-4 text-sm text-amber-900">
                <strong>Nota del estado:</strong> {{ $trabajo->nota_estado }}
            </div>
        @endif

        @if($puedeCambiarEstado)
        <div class="mt-8 rounded-xl border border-[#15537c]/20 bg-[#15537c]/5 p-5">
            <h2 class="text-lg font-semibold text-slate-900">Actualizar estado del trabajo</h2>
            <p class="mt-1 text-sm text-slate-600">Marca el avance: visto, trabajando, casi termino o finalizado.</p>

            <form method="POST" action="{{ route('leads.trabajos.estado', $trabajo->id) }}" class="mt-4 space-y-4">
                @csrf
                @method('PATCH')
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-5">
                    @foreach(\App\Models\Trabajo::ESTADOS as $st)
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm hover:border-[#15537c]/40 {{ $trabajo->estado === $st ? 'ring-2 ring-[#15537c]' : '' }}">
                            <input type="radio" name="estado" value="{{ $st }}" class="text-[#15537c] focus:ring-[#15537c]" @checked(old('estado', $trabajo->estado) === $st) required>
                            <span class="font-medium {{ \App\Models\Trabajo::ESTADO_COLORS[$st] }} rounded px-1.5 py-0.5">{{ \App\Models\Trabajo::ESTADO_LABELS[$st] }}</span>
                        </label>
                    @endforeach
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Nota (opcional)</label>
                    <textarea name="nota_estado" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Ej. Ya hablé con el cliente, falta firmar...">{{ old('nota_estado', $trabajo->nota_estado) }}</textarea>
                </div>
                <button type="submit" class="rounded-xl bg-[#15537c] px-5 py-2.5 text-base font-semibold text-white hover:bg-[#0f3d5c]">Guardar estado</button>
            </form>
        </div>
        @endif

        @if($esAdmin)
        <form method="POST" action="{{ route('leads.trabajos.destroy', $trabajo->id) }}" class="mt-6" onsubmit="return confirm('¿Eliminar este trabajo?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50">Eliminar trabajo</button>
        </form>
        @endif
    </div>
</div>
@endsection
