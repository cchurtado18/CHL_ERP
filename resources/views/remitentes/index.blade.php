@extends('layouts.app-new')

@section('title', 'Remitentes - CH LOGISTICS ERP')
@section('navbar-title', 'Remitentes')

@section('content')
<div class="mx-auto w-full max-w-[1300px] space-y-6">
    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div class="min-w-[250px] flex-1">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Buscar</label>
                <input type="text" name="busqueda" value="{{ $busqueda }}" placeholder="Nombre, teléfono, correo..." class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
            </div>
            <button type="submit" class="rounded-lg bg-[#15537c] px-5 py-2.5 font-medium text-white">Filtrar</button>
            <a href="{{ route('remitentes.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 font-medium text-slate-700">Limpiar</a>
        </form>
    </div>

    <div class="flex items-center justify-between">
        <div class="text-sm text-slate-600">Total: <span class="font-semibold">{{ $remitentes->total() }}</span></div>
        <a href="{{ route('remitentes.create') }}" class="rounded-xl bg-[#15537c] px-5 py-2.5 font-semibold text-white"><i class="fas fa-plus mr-1"></i> Nuevo Remitente</a>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[#15537c] text-white">
                    <tr>
                        <th class="px-4 py-2">Nombre</th>
                        <th class="px-4 py-2">Teléfono</th>
                        <th class="px-4 py-2">Correo</th>
                        <th class="px-4 py-2">Ubicación</th>
                        <th class="px-4 py-2 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($remitentes as $remitente)
                        <tr class="border-b border-slate-100">
                            <td class="px-4 py-2">{{ $remitente->nombre_completo }}</td>
                            <td class="px-4 py-2">{{ $remitente->telefono }}</td>
                            <td class="px-4 py-2">{{ $remitente->correo ?: '—' }}</td>
                            <td class="px-4 py-2">{{ trim(($remitente->ciudad ?: '') . ' ' . ($remitente->estado ?: '')) ?: '—' }}</td>
                            <td class="px-4 py-2 text-right">
                                <a href="{{ route('remitentes.show', $remitente->id) }}" class="px-2 text-slate-700"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('remitentes.edit', $remitente->id) }}" class="px-2 text-slate-700"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('remitentes.destroy', $remitente->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="px-2 text-red-700" onclick="return confirm('¿Eliminar remitente?')"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-slate-600">No hay remitentes registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($remitentes->hasPages())
        <div class="flex justify-center">{{ $remitentes->links('vendor.pagination.custom') }}</div>
    @endif
</div>
@endsection
