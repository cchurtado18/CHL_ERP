@extends('layouts.app-new')

@section('title', 'Asiento #' . $asiento->numero . ' - Contabilidad')
@section('navbar-title', 'Contabilidad')

@section('content')
<div class="mx-auto w-full max-w-[1400px] space-y-8 pb-10">
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-[#15537c] to-[#0f3d5c] p-6 text-white shadow-md sm:p-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm font-medium text-white/80">Libro diario</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight sm:text-3xl">Asiento {{ $asiento->numero }}</h1>
                <p class="mt-2 max-w-2xl text-sm text-white/85">Detalle completo del asiento contable.</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-sm font-medium backdrop-blur-sm"><i class="fas fa-calendar-alt text-xs"></i>{{ $asiento->fecha?->format('d/m/Y') }}</span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-sm font-medium backdrop-blur-sm">{{ ucfirst($asiento->estado) }}</span>
                </div>
            </div>
            <a href="{{ route('contabilidad.asientos.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-medium text-white backdrop-blur-sm transition hover:bg-white/20"><i class="fas fa-arrow-left"></i> Volver a lista</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Fecha</div>
            <div class="mt-1 text-lg font-bold text-slate-900">{{ $asiento->fecha?->format('d/m/Y') }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Estado</div>
            <div class="mt-1 text-lg font-bold text-slate-900">{{ ucfirst($asiento->estado) }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Referencia</div>
            <div class="mt-1 text-lg font-bold text-slate-900">{{ $asiento->referencia_tipo ?: '—' }} {{ $asiento->referencia_id ?: '' }}</div>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="mb-3 flex items-center gap-2 border-b border-slate-100 pb-4 text-lg font-semibold text-slate-800"><i class="fas fa-align-left text-[#15537c]"></i> Descripción</h2>
        <p class="text-base text-slate-800">{{ $asiento->descripcion ?: '—' }}</p>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="flex items-center gap-2 text-lg font-semibold text-slate-800"><i class="fas fa-list-ul text-[#15537c]"></i> Líneas del asiento</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] border-collapse text-left text-base text-black">
                <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                    <tr>
                        <th class="px-4 py-2.5 font-semibold">Cuenta</th>
                        <th class="px-4 py-2.5 font-semibold">Glosa</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Débito</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Crédito</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($asiento->detalles as $i => $d)
                        <tr class="border-b border-slate-100 {{ $i % 2 === 1 ? 'bg-slate-50' : 'bg-white' }}">
                            <td class="px-4 py-2 font-medium text-slate-900">{{ $d->cuenta->codigo ?? '' }} — {{ $d->cuenta->nombre ?? '—' }}</td>
                            <td class="px-4 py-2 text-slate-700">{{ $d->glosa ?: '—' }}</td>
                            <td class="px-4 py-2 text-right font-medium">${{ number_format((float)$d->debito, 2) }}</td>
                            <td class="px-4 py-2 text-right font-medium">${{ number_format((float)$d->credito, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-200 bg-slate-100 font-bold text-slate-900">
                        <td class="px-4 py-3 text-right" colspan="2">Totales</td>
                        <td class="px-4 py-3 text-right">${{ number_format((float)$asiento->total_debito, 2) }}</td>
                        <td class="px-4 py-3 text-right">${{ number_format((float)$asiento->total_credito, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
