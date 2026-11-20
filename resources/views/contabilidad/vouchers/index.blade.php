@extends('layouts.app-new')

@section('title', 'Vouchers - Contabilidad')
@section('navbar-title', 'Contabilidad')

@section('content')
<div class="mx-auto w-full max-w-[1400px] space-y-8">
    @if(!empty($setupPendiente))
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-amber-900">Ejecuta <code class="rounded bg-amber-100 px-1">php artisan migrate</code> para activar vouchers.</div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Vouchers</h1>
                <p class="mt-1 text-base text-slate-600">Comprobantes de ingreso (cobros de clientes) y egreso (pagos a proveedores).</p>
            </div>
            <a href="{{ route('contabilidad.dashboard') }}" class="inline-flex items-center gap-2 font-medium text-[#15537c] hover:underline"><i class="fas fa-arrow-left"></i> Tablero</a>
        </div>
        <form class="mt-5 flex flex-wrap items-end gap-4" method="GET">
            <div class="w-full sm:w-48">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Tipo</label>
                <select name="tipo" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-base">
                    <option value="">Todos</option>
                    <option value="ingreso" @selected(request('tipo')==='ingreso')>Ingreso</option>
                    <option value="egreso" @selected(request('tipo')==='egreso')>Egreso</option>
                </select>
            </div>
            <div class="min-w-[200px] flex-1">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Buscar</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Folio, tercero, concepto..." class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base">
            </div>
            <button class="inline-flex items-center gap-2 rounded-lg bg-[#15537c] px-5 py-2.5 font-medium text-white hover:bg-[#0f3d5c]"><i class="fas fa-search"></i> Filtrar</button>
        </form>
    </div>

    @if(empty($setupPendiente))
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[880px] text-left text-base">
                <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                    <tr>
                        <th class="px-4 py-2.5 font-semibold">Folio</th>
                        <th class="px-4 py-2.5 font-semibold">Tipo</th>
                        <th class="px-4 py-2.5 font-semibold">Fecha</th>
                        <th class="px-4 py-2.5 font-semibold">Tercero</th>
                        <th class="px-4 py-2.5 font-semibold">Concepto</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Monto</th>
                        <th class="px-4 py-2.5 font-semibold text-right">PDF</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $v)
                        <tr class="border-b border-slate-100 {{ $loop->even ? 'bg-slate-50' : '' }}">
                            <td class="px-4 py-2 font-semibold text-[#15537c]"><a href="{{ route('contabilidad.vouchers.show', $v->id) }}" class="hover:underline">{{ $v->folio }}</a></td>
                            <td class="px-4 py-2">
                                <span class="rounded-full px-2.5 py-0.5 text-sm font-semibold {{ $v->esIngreso() ? 'bg-emerald-50 text-emerald-800' : 'bg-rose-50 text-rose-800' }}">{{ $v->etiquetaTipo() }}</span>
                            </td>
                            <td class="px-4 py-2">{{ $v->fecha?->format('d/m/Y') }}</td>
                            <td class="px-4 py-2">{{ $v->tercero_nombre }}</td>
                            <td class="px-4 py-2 text-slate-700">{{ \Illuminate\Support\Str::limit($v->concepto, 48) }}</td>
                            <td class="px-4 py-2 text-right font-bold tabular-nums">${{ number_format((float)$v->monto, 2) }}</td>
                            <td class="px-4 py-2 text-right">
                                <a href="{{ route('contabilidad.vouchers.pdf', $v->id) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-[#15537c]/30 bg-[#15537c]/5 px-3 py-1.5 text-sm font-semibold text-[#15537c] hover:bg-[#15537c]/10">PDF</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center text-slate-600">No hay vouchers.</td></tr>
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
