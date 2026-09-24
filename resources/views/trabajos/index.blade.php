@extends('layouts.app-new')

@section('title', 'Trabajos - Lista')
@section('navbar-title', 'Trabajos del equipo')

@section('content')
<div class="mx-auto w-full max-w-[1400px] space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800">{{ session('success') }}</div>
    @endif

    @include('leads._nav')

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $esAdmin ? 'Trabajos asignados' : 'Mis trabajos' }}</h1>
                <p class="text-sm text-slate-600">Actualiza el estado conforme avances el trabajo.</p>
            </div>
        </div>

        <form method="GET" class="mb-5 flex flex-wrap items-end gap-3">
            <div class="min-w-[200px] flex-1">
                <label class="mb-1 block text-sm font-medium text-slate-600">Buscar</label>
                <input type="text" name="busqueda" value="{{ request('busqueda') }}" placeholder="Título o cliente..." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div class="w-44">
                <label class="mb-1 block text-sm font-medium text-slate-600">Estado</label>
                <select name="estado" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Todos</option>
                    @foreach($estados as $st)
                        <option value="{{ $st }}" @selected(request('estado') === $st)>{{ \App\Models\Trabajo::ESTADO_LABELS[$st] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-44">
                <label class="mb-1 block text-sm font-medium text-slate-600">Prioridad</label>
                <select name="prioridad" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Todas</option>
                    @foreach($prioridades as $prio)
                        <option value="{{ $prio }}" @selected(request('prioridad') === $prio)>{{ \App\Models\Trabajo::PRIORIDAD_LABELS[$prio] }}</option>
                    @endforeach
                </select>
            </div>
            @if($esAdmin)
            <div class="w-52">
                <label class="mb-1 block text-sm font-medium text-slate-600">Asignado a</label>
                <select name="asignado_a" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="" @selected(! request()->filled('asignado_a'))>Mis trabajos</option>
                    <option value="todos" @selected(request('asignado_a') === 'todos')>Todo el equipo</option>
                    @foreach($equipo as $u)
                        <option value="{{ $u->id }}" @selected((string) request('asignado_a') === (string) $u->id)>{{ $u->nombre ?? $u->email }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <button class="rounded-lg bg-[#15537c] px-4 py-2 text-sm font-semibold text-white">Filtrar</button>
            <a href="{{ route('leads.trabajos.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm">Limpiar</a>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] border-collapse text-left text-sm">
                <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                    <tr>
                        <th class="px-4 py-2 font-semibold">Trabajo</th>
                        <th class="px-4 py-2 font-semibold">Cliente</th>
                        @if($esAdmin)
                        <th class="px-4 py-2 font-semibold">Asignado a</th>
                        @endif
                        <th class="px-4 py-2 font-semibold text-center">Prioridad</th>
                        <th class="px-4 py-2 font-semibold text-center">Fecha</th>
                        <th class="px-4 py-2 font-semibold text-center">Estado</th>
                        <th class="px-4 py-2 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trabajos as $t)
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-slate-900">{{ $t->titulo }}</div>
                            @if($t->descripcion)
                                <div class="mt-0.5 line-clamp-1 text-xs text-slate-500">{{ $t->descripcion }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-800">{{ $t->cliente->nombre_completo ?? '—' }}</td>
                        @if($esAdmin)
                        <td class="px-4 py-3 text-slate-700">{{ $t->asignado->nombre ?? $t->asignado->email ?? '—' }}</td>
                        @endif
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $t->colorPrioridad() }}">{{ $t->labelPrioridad() }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-slate-700">{{ $t->fecha_programada->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $t->colorEstado() }}">{{ $t->labelEstado() }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('leads.trabajos.show', $t->id) }}" class="rounded-lg bg-[#15537c] px-3 py-1.5 text-xs font-semibold text-white hover:bg-[#0f3d5c]">Ver / estado</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $esAdmin ? 7 : 6 }}" class="px-4 py-12 text-center text-slate-500">
                            No hay trabajos.
                            @if($esAdmin)
                                <a href="{{ route('leads.trabajos.create') }}" class="font-semibold text-[#15537c] hover:underline">Asignar el primero</a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($trabajos->hasPages())
            <div class="mt-4">{{ $trabajos->links('vendor.pagination.custom') }}</div>
        @endif
    </div>
</div>
@endsection
