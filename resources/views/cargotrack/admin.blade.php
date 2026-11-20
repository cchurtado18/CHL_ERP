@extends('layouts.app-new')

@section('title', 'CargoTrack Admin - CH Logistics')
@section('navbar-title', 'CargoTrack Admin')

@section('content')
@php
    $tabs = [
        'resumen' => ['label' => 'Resumen', 'icon' => 'fa-chart-pie'],
        'inventario' => ['label' => 'Inventario', 'icon' => 'fa-boxes'],
        'agencias' => ['label' => 'Agencias', 'icon' => 'fa-sitemap'],
        'config' => ['label' => 'Configuración', 'icon' => 'fa-cog'],
    ];
@endphp
<div class="mx-auto w-full max-w-7xl space-y-6 px-4 sm:px-6">
    <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl px-5 py-4 shadow-sm" style="background: linear-gradient(90deg, #15537c 0%, #2d6a9a 100%);">
        <div>
            <h1 class="text-xl font-bold text-white">CargoTrack · recepción admin</h1>
            <p class="text-sm text-white/80">Paquetes GC separados por agencia y asignados a clientes</p>
        </div>
        <div class="rounded-lg border border-white/30 bg-black/10 px-4 py-2.5 text-sm text-white/90">
            Sync admin pausado · no se intenta login en GC
        </div>
    </div>

    {{-- Navegación por función --}}
    <nav class="flex flex-wrap gap-2 rounded-xl border border-slate-200 bg-white p-2 shadow-sm">
        @foreach($tabs as $key => $meta)
            <a href="{{ route('cargotrack.index', ['tab' => $key]) }}"
                class="inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition
                    {{ $tab === $key
                        ? 'bg-[#15537c] text-white shadow-sm'
                        : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fas {{ $meta['icon'] }}"></i>
                {{ $meta['label'] }}
                @if($key === 'inventario' && ($stats['unmapped'] ?? 0) > 0)
                    <span class="ml-0.5 rounded-full {{ $tab === $key ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800' }} px-1.5 py-0.5 text-[10px]">{{ $stats['unmapped'] }}</span>
                @endif
            </a>
        @endforeach
    </nav>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->has('cargotrack'))
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first('cargotrack') }}</div>
    @endif

    {{-- ===== RESUMEN ===== --}}
    @if($tab === 'resumen')
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase text-slate-400">Inbound</p>
                <p class="mt-1 text-2xl font-extrabold text-slate-800">{{ $stats['total'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase text-slate-400">Asignados</p>
                <p class="mt-1 text-2xl font-extrabold text-emerald-700">{{ $stats['assigned'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase text-slate-400">Sin mapa</p>
                <p class="mt-1 text-2xl font-extrabold text-amber-700">{{ $stats['unmapped'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase text-slate-400">En inventario</p>
                <p class="mt-1 text-2xl font-extrabold text-[#15537c]">{{ $stats['en_inventario'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase text-slate-400">Con documento</p>
                <p class="mt-1 text-2xl font-extrabold text-[#15537c]">{{ $stats['with_docs'] }}</p>
            </div>
        </div>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-2 text-lg font-semibold text-slate-800">Accesos rápidos</h2>
            <p class="mb-4 text-sm text-slate-500">Elige la función según lo que necesites hacer.</p>
            <div class="grid gap-3 sm:grid-cols-3">
                <a href="{{ route('cargotrack.index', ['tab' => 'inventario']) }}"
                    class="rounded-xl border border-slate-200 p-4 transition hover:border-[#15537c] hover:bg-[#15537c]/5">
                    <p class="font-bold text-[#15537c]"><i class="fas fa-boxes mr-2"></i>Inventario</p>
                    <p class="mt-1 text-sm text-slate-500">Ver paquetes extraídos, filtrar y asignar a clientes.</p>
                </a>
                <a href="{{ route('cargotrack.index', ['tab' => 'agencias']) }}"
                    class="rounded-xl border border-slate-200 p-4 transition hover:border-[#15537c] hover:bg-[#15537c]/5">
                    <p class="font-bold text-[#15537c]"><i class="fas fa-sitemap mr-2"></i>Agencias</p>
                    <p class="mt-1 text-sm text-slate-500">Mapear CHLOGIX / SHIPIA a clientes del CRM.</p>
                </a>
                <a href="{{ route('cargotrack.index', ['tab' => 'config']) }}"
                    class="rounded-xl border border-slate-200 p-4 transition hover:border-[#15537c] hover:bg-[#15537c]/5">
                    <p class="font-bold text-[#15537c]"><i class="fas fa-cog mr-2"></i>Configuración</p>
                    <p class="mt-1 text-sm text-slate-500">Credenciales admin GC y opciones de sync/documentos.</p>
                </a>
            </div>
            <form method="POST" action="{{ route('cargotrack.sync') }}" class="mt-5">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-[#15537c] px-4 py-2 text-sm font-semibold text-white hover:bg-[#15537c]/90">
                    <i class="fas fa-sync-alt"></i> Sincronizar ahora
                </button>
            </form>
            @if($settings->last_sync_at)
                <p class="mt-5 text-xs text-slate-500">
                    Último sync: {{ $settings->last_sync_at->format('d/m/Y H:i') }}
                    @if($settings->last_sync_message) · {{ $settings->last_sync_message }} @endif
                </p>
            @else
                <p class="mt-5 text-xs text-amber-700">Aún no hay sync. Configura el acceso admin y pulsa “Sincronizar ahora”.</p>
            @endif
        </section>
    @endif

    {{-- ===== INVENTARIO ===== --}}
    @if($tab === 'inventario')
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800"><i class="fas fa-boxes text-[#15537c] mr-2"></i>Inventario CargoTrack</h2>
                    <p class="text-sm text-slate-500">Paquetes extraídos. Asigna a un cliente para ingresarlos al inventario CRM.</p>
                </div>
                <p class="text-xs text-slate-400">{{ $inbound->total() }} resultado(s)</p>
            </div>

            <form method="GET" action="{{ route('cargotrack.index') }}" class="mb-4 grid gap-2 rounded-lg border border-slate-100 bg-slate-50 p-3 sm:grid-cols-2 lg:grid-cols-6">
                <input type="hidden" name="tab" value="inventario">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar guía, tracking, consignee…"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm lg:col-span-2">
                <select name="agencia" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Agencia</option>
                    @foreach($agenciaCodigos as $cod)
                        <option value="{{ $cod }}" @selected(request('agencia') === $cod)>{{ $cod }}</option>
                    @endforeach
                </select>
                <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Estado asignación</option>
                    <option value="sin_asignar" @selected(request('status') === 'sin_asignar')>Sin asignar</option>
                    <option value="assigned" @selected(request('status') === 'assigned')>Asignado</option>
                    <option value="en_inventario" @selected(request('status') === 'en_inventario')>En inventario</option>
                    <option value="unmapped" @selected(request('status') === 'unmapped')>Sin mapa agencia</option>
                </select>
                <select name="cliente_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Cliente</option>
                    @foreach($clientes as $c)
                        <option value="{{ $c->id }}" @selected((string) request('cliente_id') === (string) $c->id)>{{ $c->nombre_completo }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <select name="modalidad" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Modalidad</option>
                        <option value="aereo" @selected(request('modalidad') === 'aereo')>Aéreo</option>
                        <option value="maritimo" @selected(request('modalidad') === 'maritimo')>Marítimo</option>
                    </select>
                    <select name="doc" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Doc</option>
                        <option value="1" @selected(request('doc') === '1')>Con doc</option>
                        <option value="0" @selected(request('doc') === '0')>Sin doc</option>
                    </select>
                </div>
                <div class="flex gap-2 lg:col-span-6">
                    <button type="submit" class="rounded-lg bg-[#15537c] px-4 py-2 text-sm font-semibold text-white hover:bg-[#0f3d5c]">Filtrar</button>
                    <a href="{{ route('cargotrack.index', ['tab' => 'inventario']) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-white">Limpiar</a>
                </div>
            </form>

            <form method="POST" action="{{ route('cargotrack.inbound.assignBulk') }}" id="bulkAssignForm" class="mb-3 flex flex-wrap items-end gap-2 rounded-lg border border-[#15537c]/20 bg-[#15537c]/5 p-3">
                @csrf
                <div class="min-w-[220px] flex-1">
                    <label class="mb-1 block text-xs font-semibold text-slate-600">Asignar seleccionados a cliente</label>
                    <select name="cliente_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Elegir cliente…</option>
                        @foreach($clientes as $c)
                            <option value="{{ $c->id }}">{{ $c->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="rounded-lg bg-[#15537c] px-4 py-2 text-sm font-semibold text-white hover:bg-[#0f3d5c]"
                    onclick="return confirm('¿Asignar los paquetes seleccionados a este cliente e ingresarlos al inventario?')">
                    Asignar selección
                </button>
                <span class="text-xs text-slate-500" id="selectedCount">0 seleccionados</span>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs uppercase text-slate-400">
                        <tr>
                            <th class="px-2 py-2"><input type="checkbox" id="checkAll" class="rounded border-slate-300 text-[#15537c]"></th>
                            <th class="px-2 py-2">Guía</th>
                            <th class="px-2 py-2">Tracking</th>
                            <th class="px-2 py-2">Agencia</th>
                            <th class="px-2 py-2">Consignee</th>
                            <th class="px-2 py-2">Shipper</th>
                            <th class="px-2 py-2">Mod.</th>
                            <th class="px-2 py-2">Peso</th>
                            <th class="px-2 py-2">Cliente / Asignar</th>
                            <th class="px-2 py-2">Inv.</th>
                            <th class="px-2 py-2">Doc</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inbound as $row)
                            <tr class="border-b border-slate-100 align-top hover:bg-slate-50/80">
                                <td class="px-2 py-2">
                                    <input type="checkbox" form="bulkAssignForm" name="ids[]" value="{{ $row->id }}"
                                        class="row-check rounded border-slate-300 text-[#15537c]">
                                </td>
                                <td class="px-2 py-2 font-mono font-semibold">{{ $row->warehouse }}</td>
                                <td class="px-2 py-2 font-mono text-xs text-[#15537c]">{{ $row->tracking_codigo ?: '—' }}</td>
                                <td class="px-2 py-2 font-bold text-[#15537c]">{{ $row->agencia_codigo ?: '—' }}</td>
                                <td class="px-2 py-2">{{ $row->consignee_nombre ?: '—' }}</td>
                                <td class="px-2 py-2">{{ $row->shipper ?: '—' }}</td>
                                <td class="px-2 py-2 capitalize">
                                    @if($row->modalidad === 'aereo')
                                        <i class="fas fa-plane text-[#15537c]" title="Aéreo"></i>
                                    @elseif($row->modalidad === 'maritimo')
                                        <i class="fas fa-ship text-[#15537c]" title="Marítimo"></i>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap">{{ $row->peso_lb !== null ? number_format((float)$row->peso_lb, 2).' lb' : '—' }}</td>
                                <td class="px-2 py-2 min-w-[220px]">
                                    <form method="POST" action="{{ route('cargotrack.inbound.assign', $row->id) }}" class="flex flex-col gap-1.5">
                                        @csrf
                                        <select name="cliente_id" required class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs">
                                            <option value="">Asignar a…</option>
                                            @foreach($clientes as $c)
                                                <option value="{{ $c->id }}" @selected($row->cliente_id == $c->id)>{{ $c->nombre_completo }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="rounded-md border border-[#15537c] px-2 py-1 text-xs font-semibold text-[#15537c] hover:bg-[#15537c]/5">
                                            {{ $row->inventario_id ? 'Reasignar / sync' : 'Asignar e ingresar' }}
                                        </button>
                                    </form>
                                    @if($row->cliente)
                                        <p class="mt-1 text-[11px] text-slate-500">Actual: {{ $row->cliente->nombre_completo }}</p>
                                    @endif
                                </td>
                                <td class="px-2 py-2">
                                    @if($row->inventario_id)
                                        <a href="{{ route('inventario.show', $row->inventario_id) }}" class="text-xs font-semibold text-emerald-700 hover:underline">#{{ $row->inventario_id }}</a>
                                    @else
                                        <span class="text-xs text-amber-700">Pendiente</span>
                                    @endif
                                </td>
                                <td class="px-2 py-2">
                                    @if($row->documento_path)
                                        <a href="{{ route('cargotrack.documento', $row->id) }}" class="font-semibold text-[#15537c] hover:underline" target="_blank">Ver</a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="px-2 py-8 text-center text-slate-400">Sin paquetes. Ve a Configuración, guarda el acceso y sincroniza.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $inbound->links() }}
            </div>
        </section>
    @endif

    {{-- ===== AGENCIAS ===== --}}
    @if($tab === 'agencias')
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-1 text-lg font-semibold text-slate-800"><i class="fas fa-sitemap text-[#15537c] mr-2"></i>Mapa de agencias</h2>
            <p class="mb-5 text-sm text-slate-500">La 1ª línea de Consignee en GC (CHLOGIX, SHIPIA) se mapea a un cliente del CRM.</p>

            <form method="POST" action="{{ route('cargotrack.agencias.store') }}" class="mb-6 grid max-w-3xl gap-3 sm:grid-cols-2">
                @csrf
                <input type="text" name="codigo" required placeholder="Código GC (ej. CHLOGIX)"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input type="text" name="nombre" placeholder="Nombre visible"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <select name="cliente_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm sm:col-span-2">
                    <option value="">— Sin cliente (quedan sin asignar) —</option>
                    @foreach($clientes as $c)
                        <option value="{{ $c->id }}">{{ $c->nombre_completo }}</option>
                    @endforeach
                </select>
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="activo" value="1" checked class="rounded border-slate-300 text-[#15537c]"> Activo
                </label>
                <button type="submit" class="rounded-lg bg-[#15537c] px-3 py-2 text-sm font-semibold text-white hover:bg-[#0f3d5c]">
                    Agregar / actualizar
                </button>
            </form>

            <div class="grid gap-3 md:grid-cols-2">
                @forelse($agencias as $agencia)
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <div class="mb-2 flex items-center justify-between gap-2">
                            <span class="font-mono text-sm font-bold text-[#15537c]">{{ $agencia->codigo }}</span>
                            <form method="POST" action="{{ route('cargotrack.agencias.destroy', $agencia->id) }}"
                                onsubmit="return confirm('¿Eliminar agencia?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline">Eliminar</button>
                            </form>
                        </div>
                        <form method="POST" action="{{ route('cargotrack.agencias.update', $agencia->id) }}">
                            @csrf
                            @method('PUT')
                            <div class="grid gap-2 sm:grid-cols-2">
                                <input type="text" name="nombre" value="{{ $agencia->nombre }}"
                                    class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                <select name="cliente_id" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                    <option value="">— Sin cliente —</option>
                                    @foreach($clientes as $c)
                                        <option value="{{ $c->id }}" @selected($agencia->cliente_id == $c->id)>{{ $c->nombre_completo }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <label class="flex items-center gap-2 text-xs text-slate-600">
                                    <input type="checkbox" name="activo" value="1" @checked($agencia->activo) class="rounded border-slate-300 text-[#15537c]"> Activo
                                </label>
                                <button type="submit" class="text-xs font-bold text-[#15537c] hover:underline">Guardar</button>
                            </div>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 md:col-span-2">Aún no hay agencias. Agrega CHLOGIX y SHIPIA.</p>
                @endforelse
            </div>
        </section>
    @endif

    {{-- ===== CONFIG ===== --}}
    @if($tab === 'config')
        <section class="mx-auto max-w-xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-1 text-lg font-semibold text-slate-800"><i class="fas fa-key text-[#15537c] mr-2"></i>Acceso admin GC</h2>
            <p class="mb-4 text-sm text-slate-500">Un solo login que ve todos los paquetes del warehouse.</p>
            <form method="POST" action="{{ route('cargotrack.settings') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Usuario</label>
                    <input type="text" name="admin_username" value="{{ old('admin_username', $settings->admin_username) }}"
                        autocomplete="off" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">
                        Contraseña
                        @if($settings->admin_password)
                            <span class="font-normal text-slate-400">(guardada · vacío = no cambiar)</span>
                        @endif
                    </label>
                    <input type="password" name="admin_password" value="" autocomplete="new-password"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        placeholder="{{ $settings->admin_password ? '••••••••' : 'Contraseña admin GC' }}">
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="sync_enabled" value="1" class="rounded border-slate-300 text-[#15537c]"
                        @checked(old('sync_enabled', $settings->sync_enabled))>
                    Activar sincronización admin
                </label>
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="fetch_documents" value="1" class="rounded border-slate-300 text-[#15537c]"
                        @checked(old('fetch_documents', $settings->fetch_documents))>
                    Intentar extraer PDF/imagen del detalle
                </label>
                @if($settings->admin_password)
                    <label class="flex items-center gap-2 text-sm text-rose-700">
                        <input type="checkbox" name="clear_password" value="1" class="rounded border-slate-300 text-rose-600">
                        Borrar contraseña
                    </label>
                @endif
                <button type="submit" class="rounded-lg bg-[#15537c] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#0f3d5c]">
                    Guardar acceso
                </button>
                @if($settings->last_sync_at)
                    <p class="text-xs text-slate-500">
                        Último sync: {{ $settings->last_sync_at->format('d/m/Y H:i') }}
                        @if($settings->last_sync_message) · {{ $settings->last_sync_message }} @endif
                    </p>
                @endif
                <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
                    El login automático a GC está pausado para no bloquear cuentas.
                    Puedes usar esta pantalla para mapa de agencias y asignación manual cuando tengas datos por otra vía.
                </p>
            </form>
        </section>
    @endif
</div>

@if($tab === 'inventario')
<script>
    (function () {
        const checkAll = document.getElementById('checkAll');
        const boxes = () => Array.from(document.querySelectorAll('.row-check'));
        const countEl = document.getElementById('selectedCount');
        function refresh() {
            const n = boxes().filter(b => b.checked).length;
            if (countEl) countEl.textContent = n + ' seleccionados';
            if (checkAll) checkAll.checked = n > 0 && n === boxes().length;
        }
        checkAll?.addEventListener('change', () => {
            boxes().forEach(b => { b.checked = checkAll.checked; });
            refresh();
        });
        document.addEventListener('change', (e) => {
            if (e.target.classList?.contains('row-check')) refresh();
        });
        refresh();
    })();
</script>
@endif
@endsection
