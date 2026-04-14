@extends('layouts.app-new')

@section('title', 'Facturación - CH LOGISTICS ERP')
@section('navbar-title', 'Facturación')

@section('content')
<div class="mx-auto w-full max-w-[1400px] space-y-8">
    @if (session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-base text-emerald-800" role="alert">
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif
    @if (session('info_contabilidad'))
    <div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-base text-amber-900" role="alert">
        <span class="font-medium"><i class="fas fa-calculator mr-2"></i>{{ session('info_contabilidad') }}</span>
    </div>
    @endif

    {{-- Filtros --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('facturacion.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="min-w-[180px] flex-1">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Cliente</label>
                <input type="text" name="cliente" value="{{ request('cliente') }}" placeholder="Buscar cliente..." class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
            </div>
            <div class="w-40">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Fecha</label>
                <input type="date" name="fecha" value="{{ request('fecha') }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
            </div>
            <div class="w-36">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Acta</label>
                <input type="text" name="acta" value="{{ request('acta') }}" placeholder="N° acta" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
            </div>
            <div class="w-52">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Tipo de factura</label>
                <select name="tipo_factura" class="w-full appearance-none rounded-lg border border-slate-300 bg-white bg-[length:1.25rem] bg-[right_0.75rem_center] bg-no-repeat px-4 py-2.5 pr-10 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%2364758b%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 9l-7 7-7-7%22/%3E%3C/svg%3E');">
                    <option value="">Todos</option>
                    <option value="paqueteria" {{ request('tipo_factura') === 'paqueteria' ? 'selected' : '' }}>Paquetería</option>
                    <option value="encomienda_familiar" {{ request('tipo_factura') === 'encomienda_familiar' ? 'selected' : '' }}>Encomienda familiar</option>
                </select>
            </div>
            <div class="w-48">
                <label class="mb-1.5 block text-sm font-medium text-slate-600">Estado</label>
                <select name="estado" class="w-full appearance-none rounded-lg border border-slate-300 bg-white bg-[length:1.25rem] bg-[right_0.75rem_center] bg-no-repeat px-4 py-2.5 pr-10 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%2364758b%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 9l-7 7-7-7%22/%3E%3C/svg%3E');">
                    <option value="">Todos</option>
                    <option value="entregado_pagado" {{ request('estado')=='entregado_pagado'?'selected':'' }}>Entregado y Pagado</option>
                    <option value="entregado_sin_pagar" {{ request('estado')=='entregado_sin_pagar'?'selected':'' }}>Entregado sin Pagar</option>
                    <option value="pagado_sin_entregar" {{ request('estado')=='pagado_sin_entregar'?'selected':'' }}>Pagado sin Entregar</option>
                    <option value="facturado_npne" {{ request('estado')=='facturado_npne'?'selected':'' }}>Facturado NPNE</option>
                </select>
            </div>
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-[#15537c] px-5 py-2.5 text-base font-medium text-white hover:bg-[#0f3d5c]"><i class="fas fa-search"></i> Filtrar</button>
            <a href="{{ route('facturacion.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-5 py-2.5 text-base font-medium text-slate-600 hover:bg-slate-50">Limpiar</a>
        </form>
    </div>

    {{-- Barra: leyenda + Nueva Factura --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
            <p class="font-medium text-slate-600">
                Total: <span class="font-bold text-slate-800">{{ $facturas->total() }}</span> facturas
            </p>
            <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500" aria-hidden="true">
                <span class="inline-flex items-center gap-2 font-medium">
                    <span class="h-3 w-1 shrink-0 rounded-sm bg-[#15537c]" title="Paquetería"></span>
                    Paquetería
                </span>
                <span class="inline-flex items-center gap-2 font-medium">
                    <span class="h-3 w-1 shrink-0 rounded-sm bg-amber-500" title="Encomienda familiar"></span>
                    Encomienda familiar
                </span>
            </div>
        </div>
        <a href="{{ route('facturacion.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#15537c] px-5 py-2.5 text-base font-semibold text-white shadow-sm hover:bg-[#0f3d5c]"><i class="fas fa-plus"></i> Nueva Factura</a>
    </div>

    {{-- Tabla --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full table-fixed border-collapse text-left text-base text-black">
                <colgroup>
                    <col style="width:4%">
                    <col style="width:13%">
                    <col style="width:12%">
                    <col style="width:9%">
                    <col style="width:11%">
                    <col style="width:7%">
                    <col style="width:10%">
                    <col style="width:7%">
                    <col style="width:15%">
                    <col style="width:12%">
                </colgroup>
                <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                    <tr>
                        <th class="px-3 py-2 font-semibold text-center">#</th>
                        <th class="px-3 py-2 font-semibold text-center">Tipo</th>
                        <th class="px-4 py-2 font-semibold">Cliente</th>
                        <th class="px-4 py-2 font-semibold text-center">Fecha</th>
                        <th class="px-4 py-2 font-semibold text-center">Acta</th>
                        <th class="px-4 py-2 font-semibold text-center">Paq.</th>
                        <th class="px-4 py-2 font-semibold text-center">Monto</th>
                        <th class="px-4 py-2 font-semibold text-center">Moneda</th>
                        <th class="px-4 py-2 font-semibold text-center">Estado</th>
                        <th class="px-4 py-2 font-semibold text-right">Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($facturas as $factura)
                    @php
                        $tipoFactura = ($factura->tipo_factura ?? '') === 'encomienda_familiar' ? 'encomienda_familiar' : 'paqueteria';
                        $filaTipoClass = $tipoFactura === 'encomienda_familiar'
                            ? 'border-l-[5px] border-l-amber-500'
                            : 'border-l-[5px] border-l-[#15537c]';
                    @endphp
                    <tr class="border-b border-slate-100 {{ $filaTipoClass }} {{ $loop->iteration % 2 === 0 ? 'bg-slate-50' : 'bg-white' }} hover:bg-slate-100/90">
                        <td class="px-3 py-1.5 text-center font-medium text-slate-700">{{ $factura->id }}</td>
                        <td class="px-2 py-1.5 text-center align-middle">
                            @if($tipoFactura === 'encomienda_familiar')
                                <span class="inline-flex max-w-full items-center justify-center gap-1 rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-900 ring-1 ring-amber-200/80" title="Factura por encomienda familiar">
                                    <i class="fas fa-people-carry shrink-0 text-[10px] opacity-90"></i>
                                    <span class="truncate">Encomienda</span>
                                </span>
                            @else
                                <span class="inline-flex max-w-full items-center justify-center gap-1 rounded-full bg-[#15537c]/12 px-2 py-1 text-xs font-semibold text-[#15537c] ring-1 ring-[#15537c]/20" title="Factura de paquetería (inventario)">
                                    <i class="fas fa-box shrink-0 text-[10px] opacity-90"></i>
                                    <span class="truncate">Paquetería</span>
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-1.5">
                            @php
                                $nombreFacturaCliente = optional($factura->cliente)->nombre_completo
                                    ?? optional(optional($factura->encomienda)->remitente)->nombre_completo
                                    ?? '—';
                            @endphp
                            <div class="truncate font-medium text-black" title="{{ $nombreFacturaCliente }}">{{ $nombreFacturaCliente }}</div>
                        </td>
                        <td class="px-4 py-1.5 text-center font-medium text-slate-900 whitespace-nowrap">{{ \Carbon\Carbon::parse($factura->fecha_factura)->format('d/m/Y') }}</td>
                        <td class="px-4 py-1.5 text-center font-medium text-slate-900">{{ $factura->numero_acta ?: '—' }}</td>
                        <td class="px-4 py-1.5 text-center">
                            <span class="inline-flex items-center rounded-full bg-sky-100 px-2 py-0.5 text-sm font-semibold text-sky-800">{{ $factura->cantidad_paquetes ?? 0 }}</span>
                        </td>
                        <td class="px-4 py-1.5 text-center font-semibold text-emerald-800">${{ number_format($factura->monto_total, 2) }}</td>
                        <td class="px-4 py-1.5 text-center font-medium text-slate-900">{{ $factura->moneda }}</td>
                        <td class="px-4 py-1.5 text-center">
                            <form method="POST" action="{{ route('facturacion.cambiar-estado', $factura->id) }}" class="inline">
                                @csrf
                                <select name="estado_pago" class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-sm focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" onchange="this.form.submit()" {{ $factura->estado_pago === 'entregado_pagado' ? 'disabled' : '' }}>
                                    <option value="entregado_pagado" {{ $factura->estado_pago=='entregado_pagado'?'selected':'' }}>Ent. y Pagado</option>
                                    <option value="entregado_sin_pagar" {{ $factura->estado_pago=='entregado_sin_pagar'?'selected':'' }}>Ent. sin Pagar</option>
                                    <option value="pagado_sin_entregar" {{ $factura->estado_pago=='pagado_sin_entregar'?'selected':'' }}>Pagado sin Ent.</option>
                                    <option value="facturado_npne" {{ $factura->estado_pago=='facturado_npne'?'selected':'' }}>Fact. NPNE</option>
                                </select>
                                @if($factura->estado_pago === 'entregado_pagado' && ($factura->contabilidad_pendiente ?? false))
                                    <div class="mt-1.5">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-900 ring-1 ring-amber-200" title="Registrar cobro en Contabilidad">
                                            <i class="fas fa-calculator"></i> Contab. pendiente
                                        </span>
                                    </div>
                                @endif
                            </form>
                        </td>
                        <td class="px-4 py-1.5 text-right">
                            <div class="inline-flex items-center justify-end gap-2">
                                <a href="{{ route('facturacion.show', $factura->id) }}" class="rounded-lg p-2 text-slate-700 hover:bg-slate-100 hover:text-[#15537c]" title="Ver detalle y nota interna"><i class="fas fa-file-invoice"></i></a>
                                <a href="{{ route('facturacion.preview', $factura->id) }}" class="rounded-lg p-2 text-slate-700 hover:bg-slate-100 hover:text-[#15537c]" title="Previsualizar" target="_blank"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('facturacion.pdf', $factura->id) }}" class="rounded-lg p-2 text-slate-700 hover:bg-slate-100 hover:text-[#15537c]" title="Descargar PDF" target="_blank"><i class="fas fa-file-pdf"></i></a>
                                @if($factura->cliente && $factura->cliente->correo)
                                <button type="button" class="btn-enviar-correo rounded-lg p-2 text-slate-700 hover:bg-slate-100 hover:text-[#15537c]" title="Enviar por correo" data-factura-id="{{ $factura->id }}" data-correo="{{ $factura->cliente->correo }}"><i class="fas fa-envelope"></i></button>
                                @else
                                <span class="rounded-lg p-2 text-slate-300 cursor-not-allowed" title="Sin correo"><i class="fas fa-envelope"></i></span>
                                @endif
                                @if(auth()->user() && auth()->user()->rol === 'admin')
                                <form action="{{ route('facturacion.destroy', $factura->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg p-2 text-slate-700 hover:bg-red-50 hover:text-red-700" onclick="return confirm('¿Eliminar esta factura?')" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="px-4 py-12 text-center text-base text-slate-700">No hay facturas registradas. <a href="{{ route('facturacion.create') }}" class="font-medium text-[#15537c] hover:underline">Crear una</a>.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($facturas->hasPages())
    <div class="flex justify-center pt-4">
        {{ $facturas->links('vendor.pagination.custom') }}
    </div>
    @endif
</div>

{{-- Modal Enviar correo --}}
<div id="modalEnviarCorreo" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-hidden="true">
    <div class="flex min-h-full items-center justify-center p-6">
        <div class="fixed inset-0 bg-slate-900/50 transition-opacity" onclick="closeModalEnviarCorreo()"></div>
        <div class="relative w-full max-w-md rounded-xl bg-white p-8 shadow-xl">
            <div class="flex items-center gap-4 text-[#15537c]"><i class="fas fa-envelope text-2xl"></i><h3 class="text-xl font-semibold text-slate-800">Enviar factura por correo</h3></div>
            <p class="mt-4 text-base text-slate-600">¿Enviar la factura a <span id="correoClienteModal" class="font-semibold text-[#15537c]"></span>?</p>
            <div class="mt-8 flex justify-end gap-3">
                <button type="button" onclick="closeModalEnviarCorreo()" class="rounded-lg border border-slate-300 px-5 py-2.5 text-base font-medium text-slate-600 hover:bg-slate-50">Cancelar</button>
                <form id="formEnviarCorreo" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="rounded-lg bg-[#15537c] px-5 py-2.5 text-base font-medium text-white hover:bg-[#0f3d5c]">Enviar</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.btn-enviar-correo').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.getAttribute('data-factura-id');
        var correo = this.getAttribute('data-correo') || '';
        document.getElementById('correoClienteModal').textContent = correo;
        document.getElementById('formEnviarCorreo').action = '/facturacion/' + id + '/enviar-correo';
        document.getElementById('modalEnviarCorreo').classList.remove('hidden');
    });
});
function closeModalEnviarCorreo() {
    document.getElementById('modalEnviarCorreo').classList.add('hidden');
}
</script>
@endpush
@endsection
