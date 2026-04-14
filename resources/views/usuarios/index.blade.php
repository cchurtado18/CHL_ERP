@extends('layouts.app-new')

@section('title', 'Usuarios - CH LOGISTICS ERP')
@section('navbar-title', 'Usuarios')

@section('content')
@if(auth()->check() && auth()->user()->rol === 'admin')
<div class="mx-auto w-full max-w-[1400px] space-y-8">
    @if (session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-base text-emerald-800" role="alert">
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    {{-- Filtros (client-side) --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-end gap-4">
            <div class="min-w-[220px] flex-1">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Buscar</label>
                <input type="text" id="searchInput" placeholder="Nombre, email o rol..." class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
            </div>
            <div class="w-44">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Rol</label>
                <select id="roleFilter" class="w-full appearance-none rounded-lg border border-slate-300 bg-white bg-[length:1.25rem] bg-[right_0.75rem_center] bg-no-repeat px-4 py-2.5 pr-10 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%2364758b%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 9l-7 7-7-7%22/%3E%3C/svg%3E');">
                    <option value="">Todos los roles</option>
                    <option value="admin">Administrador</option>
                    <option value="agente">Agente</option>
                    <option value="contador">Contador</option>
                </select>
            </div>
            <div class="w-40">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Estado</label>
                <select id="statusFilter" class="w-full appearance-none rounded-lg border border-slate-300 bg-white bg-[length:1.25rem] bg-[right_0.75rem_center] bg-no-repeat px-4 py-2.5 pr-10 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%2364758b%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 9l-7 7-7-7%22/%3E%3C/svg%3E');">
                    <option value="">Todos</option>
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>
            <button type="button" id="clearFilters" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-5 py-2.5 text-base font-medium text-slate-600 hover:bg-slate-50">Limpiar</button>
        </div>
    </div>

    {{-- Barra: vista + Nuevo Usuario --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="inline-flex rounded-xl border border-slate-200 bg-white p-1 shadow-sm">
            <button type="button" id="viewTable" class="view-toggle rounded-lg px-4 py-2.5 text-base font-medium text-white bg-[#15537c]" title="Tabla"><i class="fas fa-list mr-2"></i>Tabla</button>
            <button type="button" id="viewGrid" class="view-toggle rounded-lg px-4 py-2.5 text-base font-medium text-slate-600 hover:bg-slate-100" title="Tarjetas"><i class="fas fa-th-large mr-2"></i>Tarjetas</button>
        </div>
        <a href="{{ route('usuarios.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#15537c] px-5 py-2.5 text-base font-semibold text-white shadow-sm hover:bg-[#0f3d5c]"><i class="fas fa-user-plus"></i> Nuevo Usuario</a>
    </div>

    {{-- Tabla --}}
    <div id="containerTable" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full table-fixed border-collapse text-left text-base text-black" id="usuariosTable">
                <colgroup>
                    <col style="width:22%">
                    <col style="width:26%">
                    <col style="width:14%">
                    <col style="width:14%">
                    <col style="width:22%">
                </colgroup>
                <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                    <tr>
                        <th class="px-4 py-2 font-semibold">Nombre</th>
                        <th class="px-4 py-2 font-semibold text-center">Email</th>
                        <th class="px-4 py-2 font-semibold text-center">Rol</th>
                        <th class="px-4 py-2 font-semibold text-center">Estado</th>
                        <th class="px-4 py-2 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $usuario)
                    <tr class="usuario-row border-b border-slate-100 {{ $loop->iteration % 2 === 0 ? 'bg-slate-50' : 'bg-white' }} hover:bg-slate-100"
                        data-nombre="{{ strtolower($usuario->nombre) }}"
                        data-email="{{ strtolower($usuario->email) }}"
                        data-rol="{{ strtolower($usuario->rol) }}"
                        data-estado="{{ $usuario->estado ? 'activo' : 'inactivo' }}">
                        <td class="px-4 py-1.5">
                            <div class="truncate font-medium text-black" title="{{ $usuario->nombre }}">{{ $usuario->nombre }}</div>
                        </td>
                        <td class="px-4 py-1.5 text-center">
                            <div class="truncate text-slate-800" title="{{ $usuario->email }}">{{ $usuario->email }}</div>
                        </td>
                        <td class="px-4 py-1.5 text-center">
                            <span class="inline-flex items-center rounded-full bg-[#15537c]/15 px-2 py-0.5 text-sm font-semibold text-[#15537c] capitalize">{{ $usuario->rol }}</span>
                        </td>
                        <td class="px-4 py-1.5 text-center">
                            @if($usuario->estado)
                                <span class="inline-flex items-center rounded-full bg-emerald-200 px-2 py-0.5 text-sm font-semibold text-emerald-900">Activo</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-slate-200 px-2 py-0.5 text-sm font-semibold text-slate-700">Inactivo</span>
                            @endif
                        </td>
                        <td class="px-4 py-1.5 text-right">
                            <div class="inline-flex items-center justify-end gap-2">
                                <a href="{{ route('usuarios.edit', $usuario->id) }}" class="rounded-lg p-2 text-slate-700 hover:bg-slate-100 hover:text-[#15537c]" title="Editar"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg p-2 text-slate-700 hover:bg-red-50 hover:text-red-700" onclick="return confirm('¿Eliminar este usuario?')" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-12 text-center text-base text-slate-700">No hay usuarios. <a href="{{ route('usuarios.create') }}" class="font-medium text-[#15537c] hover:underline">Crear uno</a>.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Vista Tarjetas --}}
    <div id="containerGrid" class="hidden">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse($usuarios as $usuario)
            <div class="usuario-card rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md"
                data-nombre="{{ strtolower($usuario->nombre) }}"
                data-email="{{ strtolower($usuario->email) }}"
                data-rol="{{ strtolower($usuario->rol) }}"
                data-estado="{{ $usuario->estado ? 'activo' : 'inactivo' }}">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#15537c]/10 text-[#15537c] text-xl">
                        <i class="fas fa-user"></i>
                    </div>
                    @if($usuario->estado)
                        <span class="rounded-full bg-emerald-200 px-2 py-0.5 text-xs font-semibold text-emerald-900">Activo</span>
                    @else
                        <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-semibold text-slate-700">Inactivo</span>
                    @endif
                </div>
                <p class="truncate text-lg font-semibold text-black" title="{{ $usuario->nombre }}">{{ $usuario->nombre }}</p>
                <p class="truncate text-sm text-slate-600" title="{{ $usuario->email }}">{{ $usuario->email }}</p>
                <p class="mt-2"><span class="rounded-full bg-[#15537c]/15 px-2 py-0.5 text-sm font-semibold text-[#15537c] capitalize">{{ $usuario->rol }}</span></p>
                <div class="mt-4 flex gap-2">
                    <a href="{{ route('usuarios.edit', $usuario->id) }}" class="flex-1 rounded-lg bg-[#15537c] py-2.5 text-center text-sm font-semibold text-white hover:bg-[#0f3d5c]">Editar</a>
                    <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg border border-red-300 py-2.5 px-3 text-sm font-semibold text-red-700 hover:bg-red-50" onclick="return confirm('¿Eliminar?')"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
            @empty
            <div class="col-span-full rounded-xl border border-slate-200 bg-white p-12 text-center text-base font-medium text-slate-700 sm:col-span-2 lg:col-span-3 xl:col-span-4">No hay usuarios. <a href="{{ route('usuarios.create') }}" class="font-semibold text-[#15537c] hover:underline">Crear uno</a>.</div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var searchInput = document.getElementById('searchInput');
    var roleFilter = document.getElementById('roleFilter');
    var statusFilter = document.getElementById('statusFilter');
    var clearFilters = document.getElementById('clearFilters');
    var table = document.getElementById('usuariosTable');

    function filterRows() {
        var search = (searchInput && searchInput.value) ? searchInput.value.toLowerCase() : '';
        var role = roleFilter ? roleFilter.value : '';
        var status = statusFilter ? statusFilter.value : '';
        var rows = document.querySelectorAll('.usuario-row');
        var cards = document.querySelectorAll('.usuario-card');
        function show(el) {
            var nom = (el.dataset.nombre || '').toLowerCase();
            var email = (el.dataset.email || '').toLowerCase();
            var rol = (el.dataset.rol || '').toLowerCase();
            var estado = el.dataset.estado || '';
            var match = true;
            if (search && nom.indexOf(search) === -1 && email.indexOf(search) === -1 && rol.indexOf(search) === -1) match = false;
            if (role && rol !== role) match = false;
            if (status && estado !== status) match = false;
            el.style.display = match ? '' : 'none';
        }
        rows.forEach(show);
        cards.forEach(show);
    }
    if (searchInput) searchInput.addEventListener('input', filterRows);
    if (roleFilter) roleFilter.addEventListener('change', filterRows);
    if (statusFilter) statusFilter.addEventListener('change', filterRows);
    if (clearFilters) {
        clearFilters.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            if (roleFilter) roleFilter.value = '';
            if (statusFilter) statusFilter.value = '';
            filterRows();
        });
    }

    var containerTable = document.getElementById('containerTable');
    var containerGrid = document.getElementById('containerGrid');
    var viewTable = document.getElementById('viewTable');
    var viewGrid = document.getElementById('viewGrid');
    if (containerTable && containerGrid && viewTable && viewGrid) {
        viewTable.addEventListener('click', function() {
            containerTable.classList.remove('hidden');
            containerGrid.classList.add('hidden');
            viewTable.classList.add('bg-[#15537c]', 'text-white');
            viewTable.classList.remove('text-slate-600');
            viewGrid.classList.remove('bg-[#15537c]', 'text-white');
            viewGrid.classList.add('text-slate-600');
        });
        viewGrid.addEventListener('click', function() {
            containerGrid.classList.remove('hidden');
            containerTable.classList.add('hidden');
            viewGrid.classList.add('bg-[#15537c]', 'text-white');
            viewGrid.classList.remove('text-slate-600');
            viewTable.classList.remove('bg-[#15537c]', 'text-white');
            viewTable.classList.add('text-slate-600');
        });
    }
})();
</script>
@endpush
@else
<div class="mx-auto w-full max-w-[1400px]">
    <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-base text-red-800" role="alert">
        No tienes permiso para acceder a este módulo.
    </div>
</div>
@endif
@endsection
