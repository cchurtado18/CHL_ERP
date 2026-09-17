@extends('layouts.app-new')

@section('title', 'Gasto #'.$gasto->id.' - CH Logistics')
@section('navbar-title', 'Gasto #'.$gasto->id)

@section('content')
<div class="mx-auto w-full max-w-[1000px] space-y-6">
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <nav class="mb-2 flex items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('contabilidad.dashboard') }}" class="hover:text-[#15537c]">Contabilidad</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <a href="{{ route('contabilidad.gastos.index') }}" class="hover:text-[#15537c]">Gastos</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="font-semibold text-slate-700">Gasto #{{ $gasto->id }}</span>
        </nav>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="flex items-center gap-3 text-2xl font-bold text-slate-800">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-rose-500 to-pink-600 text-white shadow"><i class="fas fa-receipt text-xl"></i></span>
                Gasto registrado
            </h1>
            <a href="{{ route('contabilidad.gastos.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"><i class="fas fa-arrow-left"></i> Volver a la lista</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
        <div class="md:col-span-2 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-slate-800">Detalle</h2>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="font-semibold text-slate-500">Fecha</dt>
                    <dd class="text-base text-slate-800">{{ $gasto->fecha->format('d/m/Y') }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-slate-500">Categoría</dt>
                    <dd class="text-base text-slate-800">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-800">
                            @if($gasto->categoria?->icono)<i class="fas {{ $gasto->categoria->icono }}"></i>@endif
                            {{ $gasto->categoria?->nombre }}
                        </span>
                    </dd>
                </div>
                <div class="col-span-2">
                    <dt class="font-semibold text-slate-500">Descripción</dt>
                    <dd class="text-base text-slate-800">{{ $gasto->descripcion }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-slate-500">Pagado desde</dt>
                    <dd class="text-base text-slate-800">{{ $gasto->cuentaPago?->nombre }} <span class="text-xs text-slate-500">({{ $gasto->cuentaPago?->codigo }})</span></dd>
                </div>
                <div>
                    <dt class="font-semibold text-slate-500">Referencia</dt>
                    <dd class="text-base text-slate-800">{{ $gasto->referencia ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-slate-500">Registrado por</dt>
                    <dd class="text-base text-slate-800">{{ $gasto->creador?->nombre ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-slate-500">Registrado el</dt>
                    <dd class="text-base text-slate-800">{{ $gasto->created_at?->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border-2 border-rose-200 bg-gradient-to-br from-rose-50 to-white p-6 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-rose-700">Monto del gasto</p>
            <p class="mt-1 text-4xl font-bold tabular-nums text-rose-700">${{ number_format((float) $gasto->monto, 2) }}</p>
            <p class="text-sm text-slate-500">{{ $gasto->moneda }}</p>

            @if($gasto->asiento)
                <div class="mt-6 rounded-lg border border-slate-200 bg-white p-3 text-sm">
                    <p class="font-semibold text-slate-700"><i class="fas fa-check-circle text-emerald-600"></i> Asiento contable generado</p>
                    <a href="{{ route('contabilidad.asientos.show', $gasto->asiento_id) }}" class="mt-1 inline-flex items-center gap-1 text-[#15537c] hover:underline">
                        {{ $gasto->asiento->numero }} <i class="fas fa-external-link-alt text-xs"></i>
                    </a>
                </div>
            @endif

            <form method="POST" action="{{ route('contabilidad.gastos.destroy', $gasto->id) }}" onsubmit="return confirm('¿Anular este gasto? Se generará un asiento de reversión.')" class="mt-6">
                @csrf @method('DELETE')
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-lg border border-rose-300 bg-white px-4 py-2 text-sm font-medium text-rose-700 hover:bg-rose-50"><i class="fas fa-times-circle"></i> Anular gasto</button>
            </form>
        </div>
    </div>

    @if($gasto->asiento && $gasto->asiento->detalles->count() > 0)
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="flex items-center gap-2 text-lg font-semibold text-slate-800"><i class="fas fa-book text-[#15537c]"></i> Asiento contable</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-base">
                    <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-4 py-2 font-semibold">Cuenta</th>
                            <th class="px-4 py-2 font-semibold">Glosa</th>
                            <th class="px-4 py-2 font-semibold text-right">Débito</th>
                            <th class="px-4 py-2 font-semibold text-right">Crédito</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($gasto->asiento->detalles as $d)
                        <tr class="border-b border-slate-100">
                            <td class="px-4 py-2.5">
                                <div class="font-medium text-slate-800">{{ $d->cuenta?->nombre }}</div>
                                <div class="text-xs text-slate-500">{{ $d->cuenta?->codigo }}</div>
                            </td>
                            <td class="px-4 py-2.5 text-sm text-slate-600">{{ $d->glosa }}</td>
                            <td class="px-4 py-2.5 text-right font-semibold tabular-nums text-emerald-700">${{ number_format((float) $d->debito, 2) }}</td>
                            <td class="px-4 py-2.5 text-right font-semibold tabular-nums text-rose-700">${{ number_format((float) $d->credito, 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
