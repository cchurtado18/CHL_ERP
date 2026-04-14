@extends('layouts.app-new')

@section('title', 'Clientes - CH LOGISTICS ERP')
@section('navbar-title', 'Clientes')

@section('content')
<div class="mx-auto w-full max-w-[1400px] space-y-8">
    @if (session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-base text-emerald-800" role="alert">
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    {{-- Filtros --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('clientes.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="min-w-[220px] flex-1">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Buscar</label>
                <input type="text" name="busqueda" value="{{ request('busqueda', $busqueda ?? '') }}" placeholder="Nombre, correo o teléfono..." class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
            </div>
            <div class="w-44">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Tipo</label>
                <select name="tipo" class="w-full appearance-none rounded-lg border border-slate-300 bg-white bg-[length:1.25rem] bg-[right_0.75rem_center] bg-no-repeat px-4 py-2.5 pr-10 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%2364758b%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 9l-7 7-7-7%22/%3E%3C/svg%3E');">
                    <option value="">Todos los tipos</option>
                    <option value="normal" {{ request('tipo', $tipo ?? '') == 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="Subagencia" {{ request('tipo', $tipo ?? '') == 'Subagencia' ? 'selected' : '' }}>Subagencia</option>
                </select>
            </div>
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-[#15537c] px-5 py-2.5 text-base font-medium text-white hover:bg-[#0f3d5c]"><i class="fas fa-search"></i> Filtrar</button>
            <a href="{{ route('clientes.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-5 py-2.5 text-base font-medium text-slate-600 hover:bg-slate-50">Limpiar</a>
        </form>
    </div>

    {{-- Barra: Nuevo Cliente --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="text-sm font-medium text-slate-500">
            Total: <span class="font-bold text-slate-800">{{ $clientes->total() }}</span> clientes
        </div>
        <a href="{{ route('clientes.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#15537c] px-5 py-2.5 text-base font-semibold text-white shadow-sm hover:bg-[#0f3d5c]"><i class="fas fa-plus"></i> Nuevo Cliente</a>
    </div>

    {{-- Tabla --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full table-fixed border-collapse text-left text-base text-black">
                <colgroup>
                    <col style="width:20%">
                    <col style="width:20%">
                    <col style="width:16%">
                    <col style="width:12%">
                    <col style="width:16%">
                    <col style="width:16%">
                </colgroup>
                <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                    <tr>
                        <th class="px-4 py-2 font-semibold">Nombre completo</th>
                        <th class="px-4 py-2 font-semibold text-center">Correo</th>
                        <th class="px-4 py-2 font-semibold text-center">Teléfono</th>
                        <th class="px-4 py-2 font-semibold text-center">Tipo</th>
                        <th class="px-4 py-2 font-semibold text-center">Fecha registro</th>
                        <th class="px-4 py-2 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clientes as $cliente)
                    <tr class="border-b border-slate-100 {{ $loop->iteration % 2 === 0 ? 'bg-slate-50' : 'bg-white' }} hover:bg-slate-100" data-nombre="{{ strtolower($cliente->nombre_completo) }}" data-correo="{{ strtolower($cliente->correo ?? '') }}" data-telefono="{{ strtolower($cliente->telefono ?? '') }}" data-tipo="{{ strtolower($cliente->tipo_cliente) }}">
                        <td class="px-4 py-1.5">
                            <div class="truncate font-medium text-black" title="{{ $cliente->nombre_completo }}">{{ $cliente->nombre_completo }}</div>
                        </td>
                        <td class="px-4 py-1.5 text-center">
                            <div class="truncate text-slate-800" title="{{ $cliente->correo }}">{{ $cliente->correo ?? '—' }}</div>
                        </td>
                        <td class="px-4 py-1.5 text-center font-medium text-slate-900">{{ $cliente->telefono ?? '—' }}</td>
                        <td class="px-4 py-1.5 text-center">
                            @php $tipoCliente = strtolower(trim($cliente->tipo_cliente)); @endphp
                            @if($tipoCliente === 'subagencia')
                                <span class="inline-flex items-center rounded-full bg-amber-200 px-2 py-0.5 text-sm font-semibold text-amber-900">Subagencia</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-[#15537c]/15 px-2 py-0.5 text-sm font-semibold text-[#15537c]">Normal</span>
                            @endif
                        </td>
                        <td class="px-4 py-1.5 text-center font-medium text-slate-900 whitespace-nowrap">{{ \Carbon\Carbon::parse($cliente->fecha_registro)->format('d/m/Y') }}</td>
                        <td class="px-4 py-1.5 text-right">
                            <div class="inline-flex items-center justify-end gap-2">
                                <a href="{{ route('clientes.show', $cliente->id) }}" class="rounded-lg p-2 text-slate-700 hover:bg-slate-100 hover:text-[#15537c]" title="Ver"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('clientes.edit', $cliente->id) }}" class="rounded-lg p-2 text-slate-700 hover:bg-slate-100 hover:text-[#15537c]" title="Editar"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg p-2 text-slate-700 hover:bg-red-50 hover:text-red-700" onclick="return confirm('¿Eliminar cliente?')" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-base text-slate-700">No hay clientes registrados. <a href="{{ route('clientes.create') }}" class="font-medium text-[#15537c] hover:underline">Registrar uno</a>.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($clientes->hasPages())
    <div class="flex justify-center pt-4">
        {{ $clientes->links('vendor.pagination.custom') }}
    </div>
    @endif
</div>
@endsection
