@extends('layouts.app-new')

@section('title', 'Inventario - CH LOGISTICS ERP')
@section('navbar-title', 'Inventario de Paquetes')

@section('content')
@php $esAgente = auth()->check() && (auth()->user()->rol ?? null) === 'agente'; @endphp
<div class="mx-auto w-full max-w-[1400px] space-y-8">
    {{-- Alert --}}
    @if (session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-base text-emerald-800" role="alert">
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    {{-- Stats - tarjetas más grandes --}}
    <div class="grid grid-cols-2 gap-5 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-[#15537c]/10 text-[#15537c] text-2xl"><i class="fas fa-boxes"></i></div>
                <div>
                    <p class="text-sm font-medium uppercase tracking-wide text-slate-500">Total</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $totalPaquetes }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-amber-100 text-amber-600 text-2xl"><i class="fas fa-check-circle"></i></div>
                <div>
                    <p class="text-sm font-medium uppercase tracking-wide text-slate-500">Recibidos</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $totalRecibidos }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 text-2xl"><i class="fas fa-box-open"></i></div>
                <div>
                    <p class="text-sm font-medium uppercase tracking-wide text-slate-500">Entregados</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $totalEntregados }}</p>
                </div>
            </div>
        </div>
        @if(auth()->user() && auth()->user()->rol === 'admin')
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-sky-100 text-sky-600 text-2xl"><i class="fas fa-dollar-sign"></i></div>
                <div>
                    <p class="text-sm font-medium uppercase tracking-wide text-slate-500">Valor total</p>
                    <p class="text-2xl font-bold text-slate-800">${{ number_format($valorTotal, 2) }}</p>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Filtros - inputs más grandes --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('inventario.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="min-w-[220px] flex-1">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Buscar</label>
                <input type="text" name="busqueda" value="{{ request('busqueda', $busqueda ?? '') }}" placeholder="Cliente, guía, tracking..." class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
            </div>
            <div class="w-44">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Servicio</label>
                <select name="servicio_id" class="w-full appearance-none rounded-lg border border-slate-300 bg-white bg-[length:1.25rem] bg-[right_0.75rem_center] bg-no-repeat px-4 py-2.5 pr-10 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%2364758b%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 9l-7 7-7-7%22/%3E%3C/svg%3E');">
                    <option value="">Todos</option>
                    @foreach($servicios as $s)
                        <option value="{{ $s->id }}" {{ request('servicio_id', $servicio_id ?? '') == $s->id ? 'selected' : '' }}>{{ $s->tipo_servicio }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-40">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Estado</label>
                <select name="estado" class="w-full appearance-none rounded-lg border border-slate-300 bg-white bg-[length:1.25rem] bg-[right_0.75rem_center] bg-no-repeat px-4 py-2.5 pr-10 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%2364758b%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 9l-7 7-7-7%22/%3E%3C/svg%3E');">
                    <option value="">Todos</option>
                    <option value="recibido" {{ request('estado', $estado ?? '') == 'recibido' ? 'selected' : '' }}>Recibido</option>
                    <option value="entregado" {{ request('estado', $estado ?? '') == 'entregado' ? 'selected' : '' }}>Entregado</option>
                </select>
            </div>
            <div class="w-52">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Cliente</label>
                <select name="cliente_id" class="w-full appearance-none rounded-lg border border-slate-300 bg-white bg-[length:1.25rem] bg-[right_0.75rem_center] bg-no-repeat px-4 py-2.5 pr-10 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%2364758b%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 9l-7 7-7-7%22/%3E%3C/svg%3E');">
                    <option value="">Todos</option>
                    @foreach($clientes as $c)
                        <option value="{{ $c->id }}" {{ request('cliente_id', $cliente_id ?? '') == $c->id ? 'selected' : '' }}>{{ $c->nombre_completo }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-[#15537c] px-5 py-2.5 text-base font-medium text-white hover:bg-[#0f3d5c]"><i class="fas fa-search"></i> Filtrar</button>
            <a href="{{ route('inventario.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-5 py-2.5 text-base font-medium text-slate-600 hover:bg-slate-50">Limpiar</a>
        </form>
    </div>

    {{-- Barra: vista + Nuevo Paquete + export --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="inline-flex rounded-xl border border-slate-200 bg-white p-1 shadow-sm">
            <button type="button" id="viewTable" class="view-toggle rounded-lg px-4 py-2.5 text-base font-medium text-white bg-[#15537c]" title="Ver como tabla"><i class="fas fa-list mr-2"></i>Tabla</button>
            <button type="button" id="viewGrid" class="view-toggle rounded-lg px-4 py-2.5 text-base font-medium text-slate-600 hover:bg-slate-100" title="Ver como tarjetas"><i class="fas fa-th-large mr-2"></i>Tarjetas</button>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('inventario.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#15537c] px-5 py-2.5 text-base font-semibold text-white shadow-sm hover:bg-[#0f3d5c]"><i class="fas fa-plus"></i> Nuevo Paquete</a>
            <a href="{{ route('inventario.export-excel') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2.5 text-base font-medium text-slate-600 hover:bg-slate-50"><i class="fas fa-file-excel text-emerald-600"></i> Exportar Excel</a>
        </div>
    </div>

    {{-- Contenedor Tabla --}}
    <div id="containerTable" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full table-fixed border-collapse text-left text-base text-black">
                <colgroup>
                    <col style="width:14%">
                    <col style="width:10%">
                    <col style="width:9%">
                    <col style="width:10%">
                    <col style="width:14%">
                    <col style="width:10%">
                    <col style="width:10%">
                    @unless($esAgente)
                    <col style="width:11%">
                    @endunless
                    <col style="width:12%">
                </colgroup>
                <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                    <tr>
                        <th class="px-4 py-2 font-semibold">Cliente</th>
                        <th class="px-4 py-2 font-semibold text-center">Servicio</th>
                        <th class="px-4 py-2 font-semibold text-center">Peso</th>
                        <th class="px-4 py-2 font-semibold text-center">Guía</th>
                        <th class="px-4 py-2 font-semibold text-center">Tracking</th>
                        <th class="px-4 py-2 font-semibold text-center">Estado</th>
                        <th class="px-4 py-2 font-semibold text-center">Ingreso</th>
                        @unless($esAgente)
                        <th class="px-4 py-2 font-semibold text-center">Monto</th>
                        @endunless
                        <th class="px-4 py-2 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inventarios as $item)
                    <tr class="border-b border-slate-100 {{ $loop->iteration % 2 === 0 ? 'bg-slate-50' : 'bg-white' }} hover:bg-slate-100">
                        <td class="px-4 py-1.5">
                            <div class="truncate font-medium text-black" title="{{ $item->cliente->nombre_completo ?? 'N/A' }}">{{ $item->cliente->nombre_completo ?? 'N/A' }}</div>
                        </td>
                        <td class="px-4 py-1.5 text-center"><span class="rounded-md bg-slate-100 px-2 py-0.5 text-sm font-medium text-black">{{ $item->servicio->tipo_servicio ?? 'N/A' }}</span></td>
                        <td class="px-4 py-1.5 text-center font-medium text-black whitespace-nowrap">{{ number_format($item->peso_lb, 2) }} lb</td>
                        <td class="px-4 py-1.5 text-center"><code class="rounded bg-slate-100 px-1.5 py-0.5 text-sm font-medium text-black">{{ $item->numero_guia }}</code></td>
                        <td class="px-4 py-1.5 text-center">
                            @if(!empty($item->tracking_codigo))
                                <a href="{{ route('tracking.dashboard') }}?codigo={{ urlencode($item->tracking_codigo) }}" class="inline-block max-w-full truncate font-mono text-sm font-medium text-[#15537c] hover:underline" title="{{ $item->tracking_codigo }}">{{ $item->tracking_codigo }}</a>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-1.5 text-center">
                            @php
                                $estadoBadge = match($item->estado) {
                                    'recibido' => 'bg-amber-200 text-amber-900',
                                    'entregado' => 'bg-emerald-200 text-emerald-900',
                                    default => 'bg-slate-200 text-slate-900'
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-sm font-semibold {{ $estadoBadge }}">{{ ucfirst(str_replace('_', ' ', $item->estado)) }}</span>
                        </td>
                        <td class="px-4 py-1.5 text-center font-medium text-slate-900">{{ \Carbon\Carbon::parse($item->fecha_ingreso)->format('d/m/Y') }}</td>
                        @unless($esAgente)
                        <td class="px-4 py-1.5 text-center font-semibold text-emerald-800">${{ number_format($item->monto_calculado, 2) }}</td>
                        @endunless
                        <td class="px-4 py-1.5 text-right">
                            <div class="inline-flex items-center justify-end gap-3">
                                <a href="{{ route('inventario.edit', $item->id) }}" class="rounded-lg p-2 text-slate-700 hover:bg-slate-100 hover:text-[#15537c]" title="Editar"><i class="fas fa-edit"></i></a>
                                <a href="{{ route('inventario.show', $item->id) }}" class="rounded-lg p-2 text-slate-700 hover:bg-slate-100 hover:text-[#15537c]" title="Ver"><i class="fas fa-eye"></i></a>
                                @if(auth()->user() && auth()->user()->rol === 'admin')
                                <button type="button" onclick="confirmDelete({{ $item->id }})" class="rounded-lg p-2 text-slate-700 hover:bg-red-50 hover:text-red-700" title="Eliminar"><i class="fas fa-trash"></i></button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="{{ $esAgente ? '8' : '9' }}" class="px-4 py-12 text-center text-base text-slate-700">No hay paquetes. <a href="{{ route('inventario.create') }}" class="font-medium text-[#15537c] hover:underline">Registrar uno</a>.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Contenedor Grid (tarjetas) - más grandes --}}
    <div id="containerGrid" class="hidden">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse ($inventarios as $item)
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-lg font-semibold text-black">{{ $item->cliente->nombre_completo ?? 'N/A' }}</p>
                        <p class="mt-1 text-sm font-medium text-slate-700">{{ $item->servicio->tipo_servicio ?? 'N/A' }} · {{ number_format($item->peso_lb, 2) }} lb</p>
                    </div>
                    @php $estadoBadge = $item->estado === 'recibido' ? 'bg-amber-200 text-amber-900' : ($item->estado === 'entregado' ? 'bg-emerald-200 text-emerald-900' : 'bg-slate-200 text-slate-900'); @endphp
                    <span class="shrink-0 rounded-full px-3 py-1 text-sm font-semibold {{ $estadoBadge }}">{{ ucfirst($item->estado) }}</span>
                </div>
                <div class="mt-4 grid {{ $esAgente ? 'grid-cols-2' : 'grid-cols-3' }} gap-4 border-t border-slate-100 pt-4">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-700">Guía</p>
                        <p class="font-mono text-base font-semibold text-black truncate" title="{{ $item->numero_guia }}">{{ $item->numero_guia }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-700">Tracking</p>
                        @if(!empty($item->tracking_codigo))
                        <a href="{{ route('tracking.dashboard') }}?codigo={{ urlencode($item->tracking_codigo) }}" class="block truncate text-base font-semibold text-[#15537c] hover:underline" title="{{ $item->tracking_codigo }}">{{ $item->tracking_codigo }}</a>
                        @else
                        <span class="text-slate-400">—</span>
                        @endif
                    </div>
                    @unless($esAgente)
                    <div class="min-w-0 text-right">
                        <p class="text-sm font-medium text-slate-700">Monto</p>
                        <p class="text-lg font-bold text-emerald-800">${{ number_format($item->monto_calculado, 2) }}</p>
                    </div>
                    @endunless
                </div>
                <div class="mt-4 flex gap-2">
                    <a href="{{ route('inventario.edit', $item->id) }}" class="flex-1 rounded-lg border border-slate-300 py-2.5 text-center text-sm font-semibold text-slate-800 hover:bg-slate-50">Editar</a>
                    <a href="{{ route('inventario.show', $item->id) }}" class="flex-1 rounded-lg bg-[#15537c] py-2.5 text-center text-sm font-semibold text-white hover:bg-[#0f3d5c]">Ver</a>
                    @if(auth()->user() && auth()->user()->rol === 'admin')
                    <button type="button" onclick="confirmDelete({{ $item->id }})" class="rounded-lg border border-red-300 py-2.5 px-3 font-semibold text-red-700 hover:bg-red-50" title="Eliminar"><i class="fas fa-trash"></i></button>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-full rounded-xl border border-slate-200 bg-white p-12 text-center text-base font-medium text-slate-700 sm:col-span-2 lg:col-span-3 xl:col-span-4">No hay paquetes. <a href="{{ route('inventario.create') }}" class="font-semibold text-[#15537c] hover:underline">Registrar uno</a>.</div>
            @endforelse
        </div>
    </div>

    {{-- Paginación --}}
    @if($inventarios->hasPages())
    <div class="flex justify-center pt-4">
        {{ $inventarios->links('vendor.pagination.custom') }}
    </div>
    @endif
</div>

{{-- Modal eliminar --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-hidden="true">
    <div class="flex min-h-full items-center justify-center p-6">
        <div class="fixed inset-0 bg-slate-900/50 transition-opacity" onclick="closeDeleteModal()"></div>
        <div class="relative w-full max-w-md rounded-xl bg-white p-8 shadow-xl">
            <div class="flex items-center gap-4 text-amber-600"><i class="fas fa-exclamation-triangle text-2xl"></i><h3 class="text-xl font-semibold text-slate-800">Confirmar eliminación</h3></div>
            <p class="mt-4 text-base text-slate-600">¿Eliminar este paquete? Esta acción no se puede deshacer.</p>
            <div class="mt-8 flex justify-end gap-3">
                <button type="button" onclick="closeDeleteModal()" class="rounded-lg border border-slate-300 px-5 py-2.5 text-base font-medium text-slate-600 hover:bg-slate-50">Cancelar</button>
                <form id="deleteForm" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg bg-red-600 px-5 py-2.5 text-base font-medium text-white hover:bg-red-700">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var containerTable = document.getElementById('containerTable');
    var containerGrid = document.getElementById('containerGrid');
    var viewTable = document.getElementById('viewTable');
    var viewGrid = document.getElementById('viewGrid');
    if (!containerTable || !containerGrid || !viewTable || !viewGrid) return;

    function showTable() {
        containerTable.classList.remove('hidden');
        containerGrid.classList.add('hidden');
        viewTable.classList.add('bg-[#15537c]', 'text-white');
        viewTable.classList.remove('text-slate-600');
        viewGrid.classList.remove('bg-[#15537c]', 'text-white');
        viewGrid.classList.add('text-slate-600');
    }
    function showGrid() {
        containerTable.classList.add('hidden');
        containerGrid.classList.remove('hidden');
        viewGrid.classList.add('bg-[#15537c]', 'text-white');
        viewGrid.classList.remove('text-slate-600');
        viewTable.classList.remove('bg-[#15537c]', 'text-white');
        viewTable.classList.add('text-slate-600');
    }

    viewTable.addEventListener('click', showTable);
    viewGrid.addEventListener('click', showGrid);
})();

function confirmDelete(id) {
    var form = document.getElementById('deleteForm');
    var modal = document.getElementById('deleteModal');
    if (form) form.action = '/inventario/' + id;
    if (modal) modal.classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>
@endpush
@endsection
