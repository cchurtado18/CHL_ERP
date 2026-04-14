@extends('layouts.app-new')

@section('title', 'Notificaciones - CH LOGISTICS ERP')
@section('navbar-title', 'Notificaciones')

@section('content')
<div class="mx-auto w-full max-w-[1400px] space-y-8">
    @if (session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-base text-emerald-800" role="alert">
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    {{-- Barra: vista + Nueva Notificación --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="inline-flex rounded-xl border border-slate-200 bg-white p-1 shadow-sm">
            <button type="button" id="viewTable" class="view-toggle rounded-lg px-4 py-2.5 text-base font-medium text-white bg-[#15537c]" title="Ver como tabla"><i class="fas fa-list mr-2"></i>Tabla</button>
            <button type="button" id="viewGrid" class="view-toggle rounded-lg px-4 py-2.5 text-base font-medium text-slate-600 hover:bg-slate-100" title="Ver como tarjetas"><i class="fas fa-th-large mr-2"></i>Tarjetas</button>
        </div>
        <a href="{{ route('notificaciones.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#15537c] px-5 py-2.5 text-base font-semibold text-white shadow-sm hover:bg-[#0f3d5c]"><i class="fas fa-plus"></i> Nueva Notificación</a>
    </div>

    {{-- Tabla --}}
    <div id="containerTable" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full table-fixed border-collapse text-left text-base text-black">
                <colgroup>
                    <col style="width:16%">
                    <col style="width:12%">
                    <col style="width:20%">
                    <col style="width:28%">
                    <col style="width:12%">
                    <col style="width:12%">
                </colgroup>
                <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                    <tr>
                        <th class="px-4 py-2 font-semibold">Usuario</th>
                        <th class="px-4 py-2 font-semibold text-center">Estado</th>
                        <th class="px-4 py-2 font-semibold">Título</th>
                        <th class="px-4 py-2 font-semibold">Mensaje</th>
                        <th class="px-4 py-2 font-semibold text-center">Fecha</th>
                        <th class="px-4 py-2 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notificaciones as $notificacion)
                    <tr class="border-b border-slate-100 {{ $loop->iteration % 2 === 0 ? 'bg-slate-50' : 'bg-white' }} hover:bg-slate-100">
                        <td class="px-4 py-1.5">
                            <div class="truncate font-medium text-black" title="{{ $notificacion->usuario->nombre ?? 'N/D' }}">{{ $notificacion->usuario->nombre ?? 'N/D' }}</div>
                        </td>
                        <td class="px-4 py-1.5 text-center">
                            @if($notificacion->leido)
                                <span class="inline-flex items-center rounded-full bg-emerald-200 px-2 py-0.5 text-sm font-semibold text-emerald-900">Leída</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-amber-200 px-2 py-0.5 text-sm font-semibold text-amber-900">No leída</span>
                            @endif
                        </td>
                        <td class="px-4 py-1.5">
                            <div class="truncate font-semibold text-[#15537c]" title="{{ $notificacion->titulo }}">{{ $notificacion->titulo }}</div>
                        </td>
                        <td class="px-4 py-1.5">
                            <div class="truncate text-slate-700" title="{{ $notificacion->mensaje }}">{{ Str::limit($notificacion->mensaje, 50) }}</div>
                        </td>
                        <td class="px-4 py-1.5 text-center font-medium text-slate-900 whitespace-nowrap">
                            {{ $notificacion->fecha ? \Carbon\Carbon::parse($notificacion->fecha)->format('d/m/Y H:i') : '—' }}
                        </td>
                        <td class="px-4 py-1.5 text-right">
                            <div class="inline-flex items-center justify-end gap-2">
                                <a href="{{ route('notificaciones.show', $notificacion) }}" class="rounded-lg p-2 text-slate-700 hover:bg-slate-100 hover:text-[#15537c]" title="Ver"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('notificaciones.edit', $notificacion) }}" class="rounded-lg p-2 text-slate-700 hover:bg-slate-100 hover:text-[#15537c]" title="Editar"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('notificaciones.destroy', $notificacion) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg p-2 text-slate-700 hover:bg-red-50 hover:text-red-700" onclick="return confirm('¿Eliminar esta notificación?')" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-base text-slate-700">No hay notificaciones. <a href="{{ route('notificaciones.create') }}" class="font-medium text-[#15537c] hover:underline">Crear una</a>.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Vista Tarjetas --}}
    <div id="containerGrid" class="hidden">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse($notificaciones as $notificacion)
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#15537c]/10 text-[#15537c]">
                        <i class="fas fa-user"></i>
                    </div>
                    @if($notificacion->leido)
                        <span class="rounded-full bg-emerald-200 px-2 py-0.5 text-xs font-semibold text-emerald-900">Leída</span>
                    @else
                        <span class="rounded-full bg-amber-200 px-2 py-0.5 text-xs font-semibold text-amber-900">No leída</span>
                    @endif
                </div>
                <p class="text-sm font-medium text-slate-600 truncate" title="{{ $notificacion->usuario->nombre ?? 'N/D' }}">{{ $notificacion->usuario->nombre ?? 'N/D' }}</p>
                <h3 class="mt-2 truncate text-lg font-semibold text-[#15537c]" title="{{ $notificacion->titulo }}">{{ $notificacion->titulo }}</h3>
                <p class="mt-2 text-sm text-slate-600" title="{{ $notificacion->mensaje }}">{{ Str::limit($notificacion->mensaje, 100) }}</p>
                <p class="mt-3 text-xs text-slate-500">{{ $notificacion->fecha ? \Carbon\Carbon::parse($notificacion->fecha)->format('d/m/Y H:i') : '—' }}</p>
                <div class="mt-4 flex gap-2">
                    <a href="{{ route('notificaciones.show', $notificacion) }}" class="rounded-lg border border-slate-300 py-2 px-3 text-sm font-semibold text-slate-800 hover:bg-slate-50"><i class="fas fa-eye mr-1"></i>Ver</a>
                    <a href="{{ route('notificaciones.edit', $notificacion) }}" class="rounded-lg bg-[#15537c] py-2 px-3 text-sm font-semibold text-white hover:bg-[#0f3d5c]"><i class="fas fa-edit mr-1"></i>Editar</a>
                    <form action="{{ route('notificaciones.destroy', $notificacion) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg border border-red-300 py-2 px-3 text-sm font-semibold text-red-700 hover:bg-red-50" onclick="return confirm('¿Eliminar?')"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
            @empty
            <div class="col-span-full rounded-xl border border-slate-200 bg-white p-12 text-center text-base font-medium text-slate-700 sm:col-span-2 lg:col-span-3 xl:col-span-4">No hay notificaciones. <a href="{{ route('notificaciones.create') }}" class="font-semibold text-[#15537c] hover:underline">Crear una</a>.</div>
            @endforelse
        </div>
    </div>

    @if($notificaciones->hasPages())
    <div class="flex justify-center pt-4">
        {{ $notificaciones->links('vendor.pagination.custom') }}
    </div>
    @endif
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
        containerGrid.classList.remove('hidden');
        containerTable.classList.add('hidden');
        viewGrid.classList.add('bg-[#15537c]', 'text-white');
        viewGrid.classList.remove('text-slate-600');
        viewTable.classList.remove('bg-[#15537c]', 'text-white');
        viewTable.classList.add('text-slate-600');
    }
    viewTable.addEventListener('click', showTable);
    viewGrid.addEventListener('click', showGrid);
})();
</script>
@endpush
@endsection
