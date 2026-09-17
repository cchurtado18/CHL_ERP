@extends('layouts.app-new')

@section('title', 'Parámetros de Rentabilidad - CH Logistics')
@section('navbar-title', 'Parámetros de Rentabilidad')

@section('content')
<div class="mx-auto w-full max-w-[1200px] space-y-8">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-base text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <nav class="mb-2 flex items-center gap-2 text-sm text-slate-500">
                    <a href="{{ route('contabilidad.dashboard') }}" class="hover:text-[#15537c]">Contabilidad</a>
                    <i class="fas fa-chevron-right text-xs"></i>
                    <span class="font-semibold text-slate-700">Parámetros de Rentabilidad</span>
                </nav>
                <h1 class="flex items-center gap-3 text-2xl font-bold text-slate-800">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 text-white shadow"><i class="fas fa-sliders text-xl"></i></span>
                    Parámetros de Rentabilidad
                </h1>
                <p class="mt-1 text-base text-slate-600">Costo fijo <strong>por servicio</strong>: Aéreo / Marítimo se cobran por libra; Pie Cúbico se cobra por pie³. Se usa para calcular la ganancia operativa de paquetería y encomiendas familiares.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('contabilidad.dashboard') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"><i class="fas fa-arrow-left text-[#15537c]"></i> Volver al panel</a>
                <a href="{{ route('contabilidad.rentabilidad.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-100"><i class="fas fa-chart-line"></i> Ver rentabilidad</a>
            </div>
        </div>
    </div>

    {{-- Costos vigentes por servicio --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        @foreach($vigentePorServicio as $info)
            @php
                $servicio = $info['servicio'];
                $param = $info['parametro'];
                $esEspecifico = $info['es_especifico'];
                $nombreNorm = strtolower($servicio->tipo_servicio);
                $esPieCubico = in_array($nombreNorm, ['pie cúbico', 'pie cubico']);
                $unidad = $esPieCubico ? 'pie³' : 'lb';
                $unidadLarga = $esPieCubico ? 'pie cúbico' : 'libra';
                $icono = match($nombreNorm) {
                    'aéreo', 'aereo' => 'fa-plane',
                    'marítimo', 'maritimo' => 'fa-ship',
                    'pie cúbico', 'pie cubico' => 'fa-cube',
                    default => 'fa-box',
                };
                $color = match($nombreNorm) {
                    'aéreo', 'aereo' => ['bg' => 'bg-sky-500', 'border' => 'border-sky-200', 'gradient' => 'from-sky-50 to-white'],
                    'marítimo', 'maritimo' => ['bg' => 'bg-cyan-600', 'border' => 'border-cyan-200', 'gradient' => 'from-cyan-50 to-white'],
                    'pie cúbico', 'pie cubico' => ['bg' => 'bg-indigo-600', 'border' => 'border-indigo-200', 'gradient' => 'from-indigo-50 to-white'],
                    default => ['bg' => 'bg-slate-500', 'border' => 'border-slate-200', 'gradient' => 'from-slate-50 to-white'],
                };
            @endphp
            <div class="rounded-xl border-2 {{ $color['border'] }} bg-gradient-to-br {{ $color['gradient'] }} p-5 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $color['bg'] }} text-xl text-white shadow"><i class="fas {{ $icono }}"></i></div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Costo por {{ $unidadLarga }}</p>
                        <h3 class="text-lg font-bold text-slate-800">{{ $servicio->tipo_servicio }}</h3>
                    </div>
                </div>
                <div class="mt-4">
                    @if($param)
                        <p class="text-3xl font-bold text-slate-900 tabular-nums">${{ number_format((float) $param->costo_fijo_por_libra, 4) }} <span class="text-sm font-medium text-slate-500">/ {{ $unidad }} {{ $param->moneda }}</span></p>
                        @if($esEspecifico)
                            <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700"><i class="fas fa-check-circle"></i> Específico para {{ $servicio->tipo_servicio }}</span>
                        @else
                            <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-700"><i class="fas fa-globe"></i> Usando valor global</span>
                        @endif
                        <p class="mt-2 text-xs text-slate-600">Vigente desde {{ $param->vigente_desde->format('d/m/Y') }}</p>
                    @else
                        <p class="text-xl font-bold text-rose-700">No configurado</p>
                        <p class="mt-1 text-xs text-slate-600">Configurá un costo abajo. Sin valor, el reporte asumirá $0/{{ $unidad }}.</p>
                    @endif
                </div>
                <button type="button"
                        class="js-prefill-form mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:border-slate-400 hover:bg-slate-50"
                        data-servicio-id="{{ $servicio->id }}"
                        data-servicio-nombre="{{ $servicio->tipo_servicio }}"
                        data-costo="{{ $param?->costo_fijo_por_libra }}">
                    <i class="fas fa-pen"></i> Actualizar costo
                </button>
            </div>
        @endforeach
    </div>

    {{-- Global fallback --}}
    <div class="rounded-xl border border-slate-200 bg-slate-50 p-5 shadow-sm">
        <div class="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-500 text-white"><i class="fas fa-globe"></i></div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Costo global (fallback)</p>
                    @if($vigenteGlobal)
                        <p class="text-xl font-bold text-slate-800 tabular-nums">${{ number_format((float) $vigenteGlobal->costo_fijo_por_libra, 4) }} <span class="text-xs font-medium text-slate-500">/ lb</span></p>
                        <p class="mt-0.5 text-xs text-slate-600">Se usa cuando un servicio no tiene costo específico. Vigente desde {{ $vigenteGlobal->vigente_desde->format('d/m/Y') }}.</p>
                    @else
                        <p class="text-base font-semibold text-slate-600">Sin valor global</p>
                        <p class="mt-0.5 text-xs text-slate-500">Opcional. Si lo dejás sin configurar, los servicios sin costo específico usarán $0/lb.</p>
                    @endif
                </div>
            </div>
            <button type="button"
                    class="js-prefill-form inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100"
                    data-servicio-id=""
                    data-servicio-nombre="Global (todos)"
                    data-costo="{{ $vigenteGlobal?->costo_fijo_por_libra }}">
                <i class="fas fa-pen"></i> Editar global
            </button>
        </div>
    </div>

    {{-- Form para nuevo valor --}}
    <div id="form-nuevo" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 flex items-center gap-2 border-b border-slate-100 pb-3 text-lg font-semibold text-slate-800">
            <i class="fas fa-plus-circle text-[#15537c]"></i> Registrar nuevo costo por libra
        </h2>

        @if($errors->any())
            <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form id="frmParam" method="POST" action="{{ route('contabilidad.parametros.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Servicio <span class="text-rose-500">*</span></label>
                    <select name="servicio_id" id="servicio_id" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
                        <option value="">Global (fallback para todos)</option>
                        @foreach($servicios as $s)
                            <option value="{{ $s->id }}" {{ (string) old('servicio_id') === (string) $s->id ? 'selected' : '' }}>{{ $s->tipo_servicio }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Costo fijo <span class="text-rose-500">*</span></label>
                    <div class="mt-1 flex">
                        <span class="inline-flex items-center rounded-l-lg border border-r-0 border-slate-300 bg-slate-100 px-3 text-slate-600">$</span>
                        <input type="number" id="costo" step="0.0001" min="0" name="costo_fijo_por_libra" required value="{{ old('costo_fijo_por_libra') }}" class="block w-full rounded-r-lg border border-slate-300 px-3 py-2 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
                    </div>
                    <p class="mt-1 text-xs text-slate-500">Aéreo/Marítimo: por libra. Pie Cúbico: por pie³.</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Moneda</label>
                    <select name="moneda" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
                        <option value="USD" {{ old('moneda', 'USD') === 'USD' ? 'selected' : '' }}>USD</option>
                        <option value="NIO" {{ old('moneda') === 'NIO' ? 'selected' : '' }}>NIO</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Vigente desde <span class="text-rose-500">*</span></label>
                    <input type="date" name="vigente_desde" required value="{{ old('vigente_desde', now()->toDateString()) }}" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">Nota (opcional)</label>
                <textarea name="nota" rows="2" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" placeholder="Ej. Ajuste por incremento de combustible">{{ old('nota') }}</textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('contabilidad.dashboard') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancelar</a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-[#15537c] px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#0f3d5c]"><i class="fas fa-save"></i> Guardar costo</button>
            </div>
        </form>
    </div>

    {{-- Histórico --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="flex items-center gap-2 text-lg font-semibold text-slate-800"><i class="fas fa-history text-[#15537c]"></i> Histórico de cambios</h2>
            <p class="mt-1 text-sm text-slate-500">Cada cambio queda registrado. Los reportes históricos usan el valor que estaba vigente en cada fecha. Si cargaste un valor por error, podés <strong>anularlo</strong>: queda tachado y el motor vuelve a usar el anterior. La fila no se borra (auditoría).</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-base">
                <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-2 font-semibold">Vigente desde</th>
                        <th class="px-4 py-2 font-semibold">Servicio</th>
                        <th class="px-4 py-2 font-semibold text-right">Costo</th>
                        <th class="px-4 py-2 font-semibold">Moneda</th>
                        <th class="px-4 py-2 font-semibold">Nota</th>
                        <th class="px-4 py-2 font-semibold">Registrado por</th>
                        <th class="px-4 py-2 font-semibold text-center">Estado</th>
                        <th class="px-4 py-2 font-semibold text-right">Acción</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($historico as $h)
                    @php
                        $nomServ = $h->servicio ? strtolower($h->servicio->tipo_servicio) : null;
                        $unidadH = in_array($nomServ, ['pie cúbico', 'pie cubico']) ? 'pie³' : 'lb';
                        $anulado = ! is_null($h->deleted_at);
                    @endphp
                    <tr class="border-b border-slate-100 {{ $anulado ? 'bg-rose-50/40 text-slate-400' : '' }}">
                        <td class="px-4 py-2.5 font-medium {{ $anulado ? 'line-through text-slate-400' : 'text-slate-800' }}">{{ $h->vigente_desde->format('d/m/Y') }}</td>
                        <td class="px-4 py-2.5">
                            @if($h->servicio)
                                <span class="inline-flex items-center gap-1 rounded-full {{ $anulado ? 'bg-slate-100 text-slate-500' : 'bg-sky-50 text-sky-700' }} px-2 py-0.5 text-xs font-semibold {{ $anulado ? 'line-through' : '' }}">{{ $h->servicio->tipo_servicio }}</span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600 {{ $anulado ? 'line-through' : '' }}">Global</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-right font-bold tabular-nums {{ $anulado ? 'line-through text-slate-400' : 'text-amber-700' }}">${{ number_format((float) $h->costo_fijo_por_libra, 4) }} <span class="text-xs font-normal text-slate-500">/{{ $unidadH }}</span></td>
                        <td class="px-4 py-2.5 {{ $anulado ? 'text-slate-400 line-through' : 'text-slate-600' }}">{{ $h->moneda }}</td>
                        <td class="px-4 py-2.5 text-sm {{ $anulado ? 'text-slate-400' : 'text-slate-600' }}">{{ $h->nota ?: '—' }}</td>
                        <td class="px-4 py-2.5 text-sm {{ $anulado ? 'text-slate-400' : 'text-slate-600' }}">{{ $h->creador?->nombre ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-center">
                            @if($anulado)
                                <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-700" title="Anulado el {{ $h->deleted_at->format('d/m/Y H:i') }}">
                                    <i class="fas fa-ban text-[10px]"></i> Anulado
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                                    <i class="fas fa-check text-[10px]"></i> Activo
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-right">
                            @if($anulado)
                                <form method="POST" action="{{ route('contabilidad.parametros.restore', $h->id) }}" class="inline" onsubmit="return confirm('¿Restaurar este parámetro? Volverá a estar activo desde {{ $h->vigente_desde->format('d/m/Y') }}.');">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-emerald-300 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">
                                        <i class="fas fa-rotate-left"></i> Restaurar
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('contabilidad.parametros.destroy', $h->id) }}" class="inline" onsubmit="return confirm('¿Anular este parámetro?\n\n{{ $h->servicio?->tipo_servicio ?? 'Global' }}: ${{ number_format((float) $h->costo_fijo_por_libra, 4) }}/{{ $unidadH }}\nVigente desde {{ $h->vigente_desde->format('d/m/Y') }}\n\nSe puede restaurar después. El motor de rentabilidad volverá a usar el valor anterior vigente.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-rose-300 bg-white px-2.5 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50">
                                        <i class="fas fa-ban"></i> Anular
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-10 text-center text-slate-500">Aún no hay valores registrados.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($historico->hasPages())
            <div class="border-t border-slate-100 p-4">{{ $historico->links() }}</div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.js-prefill-form').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var servicioId = btn.dataset.servicioId || '';
        var costo = btn.dataset.costo || '';
        var servicioSelect = document.getElementById('servicio_id');
        var costoInput = document.getElementById('costo');

        if (servicioSelect) servicioSelect.value = servicioId;
        if (costoInput && costo) costoInput.value = costo;

        var form = document.getElementById('form-nuevo');
        if (form) {
            form.scrollIntoView({behavior: 'smooth', block: 'start'});
            if (costoInput) setTimeout(function(){ costoInput.focus(); costoInput.select(); }, 400);
        }
    });
});
</script>
@endpush
