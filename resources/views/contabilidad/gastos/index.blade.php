@extends('layouts.app-new')

@section('title', 'Gastos - CH LOGISTICS ERP')
@section('navbar-title', 'Gastos')

@section('content')
<div class="mx-auto w-full max-w-[1400px] space-y-8">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-base text-emerald-800">{{ session('success') }}</div>
    @endif

    {{-- Cabecera --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <nav class="mb-1 flex items-center gap-2 text-sm text-slate-500">
                    <a href="{{ route('contabilidad.dashboard') }}" class="hover:text-[#15537c]">Contabilidad</a>
                    <i class="fas fa-chevron-right text-xs"></i>
                    <span class="font-semibold text-slate-700">Gastos</span>
                </nav>
                <h1 class="flex items-center gap-3 text-2xl font-bold text-slate-800">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-rose-500 to-pink-600 text-white shadow"><i class="fas fa-receipt text-xl"></i></span>
                    Gastos operativos
                </h1>
                <p class="mt-1 text-base text-slate-600">Registrá los gastos del mes. Se genera el asiento contable automáticamente.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('contabilidad.dashboard') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50"><i class="fas fa-arrow-left text-[#15537c]"></i> Volver al panel</a>
                <a href="{{ route('contabilidad.rentabilidad.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-800 hover:bg-emerald-100"><i class="fas fa-chart-line"></i> Ver rentabilidad</a>
                <a href="{{ route('contabilidad.gastos.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#15537c] px-5 py-2.5 text-base font-semibold text-white shadow-sm hover:bg-[#0f3d5c]">
                    <i class="fas fa-plus-circle"></i> Registrar gasto
                </a>
            </div>
        </div>
    </div>

    {{-- KPIs del rango filtrado --}}
    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
        <div class="rounded-xl border border-rose-200 bg-gradient-to-br from-rose-50 to-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-rose-700">Total gastos</p>
            <p class="mt-1 text-3xl font-bold tabular-nums text-rose-700">${{ number_format($resumen['total'], 2) }}</p>
            <p class="mt-1 text-xs text-slate-500">En el rango filtrado (o mes actual)</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cantidad</p>
            <p class="mt-1 text-3xl font-bold tabular-nums text-slate-900">{{ $resumen['cantidad'] }}</p>
            <p class="mt-1 text-xs text-slate-500">Gastos registrados</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Categoría #1</p>
            @php $top = $resumen['por_categoria']->first(); @endphp
            @if($top)
                <p class="mt-1 text-xl font-bold text-slate-900">{{ $top->nombre }}</p>
                <p class="mt-1 text-sm font-semibold text-rose-700 tabular-nums">${{ number_format((float) $top->total, 2) }}</p>
            @else
                <p class="mt-1 text-base text-slate-500">Sin datos</p>
            @endif
        </div>
    </div>

    {{-- Filtros --}}
    <form method="GET" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-5">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Desde</label>
                <input type="date" name="desde" value="{{ request('desde') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Hasta</label>
                <input type="date" name="hasta" value="{{ request('hasta') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Categoría</label>
                <select name="categoria_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Todas</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" {{ request('categoria_id') == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Cuenta de pago</label>
                <select name="cuenta_pago_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Todas</option>
                    @foreach($cuentasPago as $c)
                        <option value="{{ $c->id }}" {{ request('cuenta_pago_id') == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-[#15537c] px-4 py-2 text-sm font-semibold text-white"><i class="fas fa-filter"></i> Filtrar</button>
                <a href="{{ route('contabilidad.gastos.index') }}" class="text-sm text-slate-500 hover:underline">Limpiar</a>
            </div>
        </div>
    </form>

    {{-- Tabla --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] border-collapse text-left text-base">
                <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                    <tr>
                        <th class="px-4 py-2.5 font-semibold">Fecha</th>
                        <th class="px-4 py-2.5 font-semibold">Categoría</th>
                        <th class="px-4 py-2.5 font-semibold">Descripción</th>
                        <th class="px-4 py-2.5 font-semibold">Pagado desde</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Monto</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($gastos as $g)
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="px-4 py-2.5 font-medium text-slate-800">{{ $g->fecha->format('d/m/Y') }}</td>
                        <td class="px-4 py-2.5">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-800">
                                @if($g->categoria?->icono)<i class="fas {{ $g->categoria->icono }}"></i>@endif
                                {{ $g->categoria?->nombre ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 max-w-md truncate text-slate-700" title="{{ $g->descripcion }}">{{ $g->descripcion }}</td>
                        <td class="px-4 py-2.5 text-sm text-slate-600">{{ $g->cuentaPago?->nombre }}</td>
                        <td class="px-4 py-2.5 text-right font-bold tabular-nums text-rose-700">${{ number_format((float) $g->monto, 2) }} <span class="text-xs font-medium text-slate-500">{{ $g->moneda }}</span></td>
                        <td class="px-4 py-2.5 text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('contabilidad.gastos.show', $g->id) }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-300 px-3 py-1 text-sm font-medium text-slate-700 hover:bg-slate-50"><i class="fas fa-eye"></i> Ver</a>
                                <form method="POST" action="{{ route('contabilidad.gastos.destroy', $g->id) }}" onsubmit="return confirm('¿Anular este gasto? Se generará un asiento de reversión.')" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-rose-300 px-3 py-1 text-sm font-medium text-rose-700 hover:bg-rose-50"><i class="fas fa-times"></i> Anular</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-slate-500">Sin gastos en este rango.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($gastos->hasPages())<div class="border-t border-slate-100 p-4">{{ $gastos->links() }}</div>@endif
    </div>
</div>
@endsection
