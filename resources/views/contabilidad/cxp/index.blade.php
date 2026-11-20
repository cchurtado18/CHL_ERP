@extends('layouts.app-new')

@section('title', 'Cuentas por pagar - Contabilidad')
@section('navbar-title', 'Contabilidad')

@section('content')
<div class="mx-auto w-full max-w-[1400px] space-y-8">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-base text-emerald-800">{{ session('success') }}</div>
    @endif
    @if(!empty($setupPendiente))
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-amber-900">Ejecuta <code class="rounded bg-amber-100 px-1">php artisan migrate</code> para activar Cuentas por pagar.</div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Cuentas por pagar</h1>
                <p class="mt-1 text-base text-slate-600">Las facturas se arman con los paquetes que ingresas (proveedor + código). Al pagar se pide referencia, foto y se marcan los paquetes como cancelados.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('contabilidad.proveedores.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50"><i class="fas fa-truck"></i> Proveedores</a>
                <a href="{{ route('contabilidad.cxp.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#15537c] px-5 py-2.5 text-base font-semibold text-white hover:bg-[#0f3d5c]"><i class="fas fa-plus"></i> Registrar factura</a>
            </div>
        </div>
        <form class="mt-5 flex flex-wrap items-end gap-4" method="GET">
            <div class="min-w-[200px] flex-1">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Proveedor</label>
                <input type="text" name="proveedor" value="{{ request('proveedor') }}" placeholder="Nombre..." class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
            </div>
            <div class="w-full sm:w-56">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Estado</label>
                <select name="estado" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-base">
                    <option value="">Todos</option>
                    @foreach(['al_dia'=>'Al día','vencida'=>'Vencida','pagada'=>'Pagada'] as $val=>$lab)
                        <option value="{{ $val }}" @selected(request('estado')===$val)>{{ $lab }}</option>
                    @endforeach
                </select>
            </div>
            <button class="inline-flex items-center gap-2 rounded-lg bg-[#15537c] px-5 py-2.5 font-medium text-white hover:bg-[#0f3d5c]"><i class="fas fa-search"></i> Filtrar</button>
        </form>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-4">
        <a href="{{ route('contabilidad.dashboard') }}" class="inline-flex items-center gap-2 font-medium text-[#15537c] hover:underline"><i class="fas fa-arrow-left"></i> Tablero</a>
        <p class="text-base font-semibold text-rose-800">Total por pagar: ${{ number_format($totalSaldo ?? 0, 2) }}</p>
    </div>

    @if(empty($setupPendiente))
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[960px] text-left text-base">
                <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                    <tr>
                        <th class="px-4 py-2.5 font-semibold">Documento</th>
                        <th class="px-4 py-2.5 font-semibold">Proveedor</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Paquetes</th>
                        <th class="px-4 py-2.5 font-semibold">Descripción</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Emisión</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Factura</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Paquetes $</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Saldo</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Estado</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $cxp)
                        <tr class="border-b border-slate-100 {{ $loop->even ? 'bg-slate-50' : '' }}">
                            <td class="px-4 py-2 font-semibold text-[#15537c]">{{ $cxp->etiquetaDocumento() }}</td>
                            <td class="px-4 py-2 font-medium">{{ $cxp->proveedor->nombre ?? '—' }}</td>
                            <td class="px-4 py-2 text-center tabular-nums">{{ $cxp->paquetes_count ?? 0 }}</td>
                            <td class="px-4 py-2 text-slate-700">{{ \Illuminate\Support\Str::limit($cxp->descripcion, 50) }}</td>
                            <td class="px-4 py-2 text-center">{{ $cxp->fecha_emision?->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-right tabular-nums">${{ number_format((float)$cxp->monto_original, 2) }}</td>
                            <td class="px-4 py-2 text-right tabular-nums">${{ number_format($cxp->montoPaquetesAsignados(), 2) }}</td>
                            <td class="px-4 py-2 text-right font-bold tabular-nums {{ (float)$cxp->saldo_actual > 0 ? 'text-rose-700' : 'text-emerald-700' }}">${{ number_format((float)$cxp->saldo_actual, 2) }}</td>
                            <td class="px-4 py-2 text-center">{{ ucwords(str_replace('_',' ', $cxp->estado_pago)) }}</td>
                            <td class="px-4 py-2 text-right">
                                <a href="{{ route('contabilidad.cxp.show', $cxp->id) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-[#15537c]/30 bg-[#15537c]/5 px-3 py-1.5 text-sm font-semibold text-[#15537c] hover:bg-[#15537c]/10">Ver / pagar</a>
                            </td>
                        </tr>
                    @empty
                            <tr><td colspan="10" class="px-4 py-12 text-center text-slate-600">No hay cuentas por pagar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($items, 'hasPages') && $items->hasPages())
        <div class="border-t border-slate-100 px-4 py-4">{{ $items->links('vendor.pagination.custom') }}</div>
        @endif
    </div>
    @endif
</div>
@endsection
