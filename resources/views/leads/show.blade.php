@extends('layouts.app-new')

@section('title', 'Lead ' . $lead->codigo)
@section('navbar-title', 'Leads')

@php
    $etapas = \App\Models\Lead::ETAPAS;
    $motivosPerdida = \App\Models\Lead::MOTIVOS_PERDIDA;
@endphp

@section('content')
<div class="mx-auto w-full max-w-[1300px] space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-[#15537c] to-[#0f3d5c] p-6 text-white shadow-md">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm text-white/80">Lead</p>
                <h1 class="text-2xl font-bold">{{ $lead->codigo }} · {{ $lead->nombre_completo }}</h1>
                <p class="mt-2 text-sm text-white/85">{{ $lead->campana ?: 'Sin campaña' }} · {{ $lead->origen ?: 'Sin origen' }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('leads.edit', $lead->id) }}" class="rounded-xl border border-white/30 bg-white/10 px-4 py-2 text-sm">Editar</a>
                <a href="{{ route('leads.calendar') }}" class="rounded-xl bg-white px-4 py-2 text-sm font-semibold text-[#15537c]">Volver</a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
            <h2 class="mb-4 text-lg font-semibold text-slate-900">Ficha del lead</h2>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3"><dt class="text-xs text-slate-500">Teléfono</dt><dd class="font-medium">{{ $lead->telefono ?: '—' }}</dd></div>
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3"><dt class="text-xs text-slate-500">Correo</dt><dd class="font-medium">{{ $lead->email ?: '—' }}</dd></div>
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3 sm:col-span-2"><dt class="text-xs text-slate-500">Dirección del cliente</dt><dd class="font-medium">{{ $lead->direccion_cliente ?: '—' }}</dd></div>
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3"><dt class="text-xs text-slate-500">Etapa</dt><dd class="font-medium">{{ ucwords(str_replace('_',' ', $lead->etapa)) }}</dd></div>
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3"><dt class="text-xs text-slate-500">Resultado</dt><dd class="font-medium">{{ ucfirst($lead->resultado) }}</dd></div>
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3"><dt class="text-xs text-slate-500">Responsable</dt><dd class="font-medium">{{ $lead->owner->nombre ?? '—' }}</dd></div>
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3"><dt class="text-xs text-slate-500">Estado USA de origen</dt><dd class="font-medium">{{ $lead->estado_usa_origen ?: '—' }}</dd></div>
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3"><dt class="text-xs text-slate-500">Departamento destino</dt><dd class="font-medium">{{ $lead->departamento_destino ?: '—' }}</dd></div>
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3"><dt class="text-xs text-slate-500">Municipio destino</dt><dd class="font-medium">{{ $lead->municipio_destino ?: '—' }}</dd></div>
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3"><dt class="text-xs text-slate-500">Próximo contacto</dt><dd class="font-medium">{{ $lead->proximo_contacto_at?->format('d/m/Y H:i') ?? '—' }}</dd></div>
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3"><dt class="text-xs text-slate-500">Último contacto</dt><dd class="font-medium">{{ $lead->ultimo_contacto_at?->format('d/m/Y H:i') ?? '—' }}</dd></div>
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3"><dt class="text-xs text-slate-500">Interés de servicio</dt><dd class="font-medium">{{ $lead->interes_servicio ?: '—' }}</dd></div>
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3"><dt class="text-xs text-slate-500">Presupuesto</dt><dd class="font-medium">{{ $lead->presupuesto_estimado ? '$'.number_format((float)$lead->presupuesto_estimado,2) : '—' }}</dd></div>
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3 sm:col-span-2"><dt class="text-xs text-slate-500">Notas</dt><dd class="whitespace-pre-wrap font-medium">{{ $lead->notas ?: 'Sin notas.' }}</dd></div>
                @if($lead->motivo_perdida)
                    <div class="rounded-lg border border-rose-100 bg-rose-50 p-3 sm:col-span-2"><dt class="text-xs text-rose-500">Motivo de pérdida</dt><dd class="font-medium text-rose-700">{{ $lead->motivo_perdida }}</dd></div>
                @endif
            </dl>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-3 text-lg font-semibold text-slate-900">Cambio rápido de etapa</h2>
                <form method="POST" action="{{ route('leads.cambiar-etapa', $lead->id) }}" class="space-y-3">
                    @csrf
                    @method('PATCH')
                    <select name="etapa" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @foreach($etapas as $et)
                            <option value="{{ $et }}" @selected($lead->etapa === $et)>{{ ucwords(str_replace('_', ' ', $et)) }}</option>
                        @endforeach
                    </select>
                    <select name="motivo_perdida_clave" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Categoría motivo pérdida (opcional)</option>
                        @foreach($motivosPerdida as $motivo)
                            <option value="{{ $motivo }}">{{ ucwords(str_replace('_', ' ', $motivo)) }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="motivo_perdida" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Motivo de pérdida (opcional)">
                    <button class="w-full rounded-lg bg-[#15537c] px-4 py-2 text-sm font-semibold text-white">Actualizar etapa</button>
                </form>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-3 text-lg font-semibold text-slate-900">Nueva interacción</h2>
                <form method="POST" action="{{ route('leads.interacciones.store', $lead->id) }}" class="space-y-3">
                    @csrf
                    <select name="tipo" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="llamada">Llamada</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="correo">Correo</option>
                        <option value="reunion">Reunión</option>
                        <option value="nota">Nota</option>
                    </select>
                    <textarea name="detalle" rows="4" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Detalle de la interacción..."></textarea>
                    <input type="datetime-local" name="fecha_interaccion" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <input type="datetime-local" name="proximo_contacto_at" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Próximo contacto">
                    <button class="w-full rounded-lg bg-[#15537c] px-4 py-2 text-sm font-semibold text-white">Guardar interacción</button>
                </form>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">Timeline de interacciones</h2>
        <div class="space-y-3">
            @forelse($lead->interacciones as $it)
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="font-semibold text-slate-900">{{ ucfirst($it->tipo) }}</div>
                        <div class="text-xs text-slate-500">{{ $it->fecha_interaccion?->format('d/m/Y H:i') ?? '—' }} · {{ $it->creador->nombre ?? 'Sistema' }}</div>
                    </div>
                    <div class="mt-2 whitespace-pre-wrap text-sm text-slate-700">{{ $it->detalle }}</div>
                </div>
            @empty
                <p class="text-sm text-slate-500">Aún no hay interacciones registradas.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
