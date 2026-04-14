@extends('layouts.app-new')

@section('title', 'Leads - Calendario')
@section('navbar-title', 'Leads')

@php
    $inicio = $baseMonth->copy()->startOfMonth();
    $fin = $baseMonth->copy()->endOfMonth();
    $startGrid = $inicio->copy()->startOfWeek(Carbon\Carbon::SUNDAY);
    $endGrid = $fin->copy()->endOfWeek(Carbon\Carbon::SATURDAY);
    $days = [];
    $cursor = $startGrid->copy();
    while ($cursor->lte($endGrid)) {
        $days[] = $cursor->copy();
        $cursor->addDay();
    }
    $etapaColors = [
        'nuevo' => 'bg-slate-100 text-slate-700',
        'contactado' => 'bg-blue-100 text-blue-700',
        'interesado' => 'bg-violet-100 text-violet-700',
        'negociacion' => 'bg-amber-100 text-amber-700',
        'seguimiento' => 'bg-cyan-100 text-cyan-700',
        'convertido' => 'bg-emerald-100 text-emerald-700',
        'perdido' => 'bg-rose-100 text-rose-700',
    ];
@endphp

@section('content')
<div class="mx-auto w-full max-w-[1400px] space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-red-800">
            <ul class="list-disc space-y-1 pl-5 text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Leads y agenda — Calendario mensual</h1>
                <p class="text-sm text-slate-600">Próximos contactos de leads y eventos generales del equipo (reuniones, recordatorios, etc.).</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('leads.index', request()->query()) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm">Ver lista</a>
                <button
                    type="button"
                    id="toggle-evento-form"
                    class="rounded-lg bg-violet-700 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-800"
                    aria-expanded="{{ $errors->any() ? 'true' : 'false' }}"
                    aria-controls="evento-form-container"
                >
                    + Nuevo evento
                </button>
                <a href="{{ route('leads.create') }}" class="rounded-lg bg-[#15537c] px-4 py-2 text-sm font-semibold text-white">+ Nuevo lead</a>
            </div>
        </div>

        <form method="GET" class="grid grid-cols-1 gap-3 md:grid-cols-6">
            <input type="month" name="mes" value="{{ request('mes', $baseMonth->format('Y-m')) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <select name="etapa" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Todas etapas</option>
                @foreach($etapas as $et)
                    <option value="{{ $et }}" @selected(request('etapa') === $et)>{{ ucwords(str_replace('_', ' ', $et)) }}</option>
                @endforeach
            </select>
            <select name="origen" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Todos orígenes</option>
                @foreach($origenes as $origen)
                    <option value="{{ $origen }}" @selected(request('origen') === $origen)>{{ $origen }}</option>
                @endforeach
            </select>
            <select name="campana" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Todas campañas</option>
                @foreach($campanas as $campana)
                    <option value="{{ $campana }}" @selected(request('campana') === $campana)>{{ $campana }}</option>
                @endforeach
            </select>
            <select name="owner_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Todos responsables</option>
                @foreach($owners as $owner)
                    <option value="{{ $owner->id }}" @selected((string) request('owner_id') === (string) $owner->id)>{{ $owner->nombre ?? $owner->email }}</option>
                @endforeach
            </select>
            <div class="flex gap-2 md:col-span-2">
                <button class="rounded-lg bg-[#15537c] px-4 py-2 text-sm font-semibold text-white">Aplicar</button>
                <a href="{{ route('leads.calendar', ['mes' => $baseMonth->format('Y-m')]) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm">Limpiar</a>
            </div>
        </form>

        <div id="evento-form-container" class="{{ $errors->any() ? 'mt-4' : 'mt-4 hidden' }} rounded-xl border border-violet-200 bg-violet-50/40 p-4">
            <form method="POST" action="{{ route('leads.agenda-eventos.store') }}" class="grid grid-cols-1 gap-3 md:grid-cols-2">
                @csrf
                <input type="hidden" name="mes" value="{{ $baseMonth->format('Y-m') }}">
                @if(request()->filled('etapa'))<input type="hidden" name="etapa" value="{{ request('etapa') }}">@endif
                @if(request()->filled('origen'))<input type="hidden" name="origen" value="{{ request('origen') }}">@endif
                @if(request()->filled('campana'))<input type="hidden" name="campana" value="{{ request('campana') }}">@endif
                @if(request()->filled('owner_id'))<input type="hidden" name="filter_owner_id" value="{{ request('owner_id') }}">@endif
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-medium text-slate-600">Nombre del evento</label>
                    <input type="text" name="titulo" value="{{ old('titulo') }}" required maxlength="200" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Ej. Revisión de tarifas con proveedor">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-medium text-slate-600">Descripción</label>
                    <textarea name="descripcion" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Detalle, enlaces, notas internas…">{{ old('descripcion') }}</textarea>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Fecha</label>
                    <input type="date" name="fecha" value="{{ old('fecha', now()->format('Y-m-d')) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Ubicación (opcional)</label>
                    <input type="text" name="ubicacion" value="{{ old('ubicacion') }}" maxlength="255" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Oficina, Meet, dirección…">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Hora inicio</label>
                    <input type="time" id="agenda_hora_inicio" name="hora_inicio" value="{{ old('hora_inicio', '09:00') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Hora fin (opcional)</label>
                    <input type="time" id="agenda_hora_fin" name="hora_fin" value="{{ old('hora_fin') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-2 flex flex-wrap items-center gap-4">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" id="agenda_todo_dia" name="todo_el_dia" value="1" class="rounded border-slate-300" @checked(old('todo_el_dia'))>
                        Todo el día
                    </label>
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-medium text-slate-600">Responsable (opcional)</label>
                    <select name="owner_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">— Sin asignar (visible para todos con filtro de calendario) —</option>
                        @foreach($owners as $ow)
                            <option value="{{ $ow->id }}" @selected((string) old('owner_id') === (string) $ow->id)>{{ $ow->nombre ?? $ow->email }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2 flex justify-end">
                    <button type="submit" class="rounded-lg bg-violet-700 px-5 py-2 text-sm font-semibold text-white hover:bg-violet-800">Guardar evento</button>
                </div>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-xs text-slate-500">Nuevos del mes</div><div class="text-2xl font-bold">{{ $kpis['nuevos_mes'] }}</div></div>
        <div class="rounded-xl border border-red-200 bg-white p-4 shadow-sm"><div class="text-xs text-slate-500">Seguimientos vencidos</div><div class="text-2xl font-bold text-red-700">{{ $kpis['vencidos'] }}</div></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-xs text-slate-500">Tasa conversión</div><div class="text-2xl font-bold">{{ $kpis['tasa_conversion'] }}%</div></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-xs text-slate-500">Campañas activas</div><div class="text-2xl font-bold">{{ $kpis['por_campana']->count() }}</div></div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold text-slate-900">{{ $baseMonth->translatedFormat('F Y') }}</h2>
            <div class="flex gap-2">
                <form method="GET" action="{{ route('leads.calendar') }}" class="inline">
                    @if(!empty(request('etapa')))
                        <input type="hidden" name="etapa" value="{{ request('etapa') }}">
                    @endif
                    @if(!empty(request('origen')))
                        <input type="hidden" name="origen" value="{{ request('origen') }}">
                    @endif
                    @if(!empty(request('campana')))
                        <input type="hidden" name="campana" value="{{ request('campana') }}">
                    @endif
                    @if(!empty(request('owner_id')))
                        <input type="hidden" name="owner_id" value="{{ request('owner_id') }}">
                    @endif
                    <input type="hidden" name="mes" value="{{ $prevMonth }}">
                    <button type="submit" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">Anterior</button>
                </form>
                <form method="GET" action="{{ route('leads.calendar') }}" class="inline">
                    @if(!empty(request('etapa')))
                        <input type="hidden" name="etapa" value="{{ request('etapa') }}">
                    @endif
                    @if(!empty(request('origen')))
                        <input type="hidden" name="origen" value="{{ request('origen') }}">
                    @endif
                    @if(!empty(request('campana')))
                        <input type="hidden" name="campana" value="{{ request('campana') }}">
                    @endif
                    @if(!empty(request('owner_id')))
                        <input type="hidden" name="owner_id" value="{{ request('owner_id') }}">
                    @endif
                    <input type="hidden" name="mes" value="{{ $nextMonth }}">
                    <button type="submit" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">Siguiente</button>
                </form>
            </div>
        </div>
        <div class="mb-3 flex flex-wrap items-center gap-4 text-xs text-slate-600">
            <span class="inline-flex items-center gap-2"><span class="h-3 w-6 rounded border border-sky-300 bg-sky-100"></span> Lead (próximo contacto)</span>
            <span class="inline-flex items-center gap-2"><span class="h-3 w-6 rounded border border-violet-300 bg-violet-100"></span> Evento de agenda</span>
        </div>

        <div class="grid grid-cols-7 gap-2 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">
            <div>Dom</div><div>Lun</div><div>Mar</div><div>Mié</div><div>Jue</div><div>Vie</div><div>Sáb</div>
        </div>
        <div class="mt-2 grid grid-cols-7 gap-2">
            @foreach($days as $day)
                @php
                    $key = $day->format('Y-m-d');
                    $items = $itemsPorDia[$key] ?? [];
                    $isCurrent = $day->month === $baseMonth->month;
                    $isToday = $day->isToday();
                @endphp
                <div class="min-h-[130px] rounded-lg border {{ $isCurrent ? 'border-slate-200 bg-white' : 'border-slate-100 bg-slate-50' }} p-2">
                    <div class="mb-1 flex items-center justify-between">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full text-xs {{ $isToday ? 'bg-[#15537c] text-white' : 'text-slate-600' }}">{{ $day->day }}</span>
                        @if(count($items) > 0)
                            <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-semibold text-slate-800">{{ count($items) }}</span>
                        @endif
                    </div>
                    <div class="space-y-1">
                        @foreach(array_slice($items, 0, 3) as $entry)
                            @if($entry['type'] === 'lead')
                                @php $lead = $entry['lead']; @endphp
                                <a href="{{ route('leads.show', $lead->id) }}" class="block rounded border border-sky-200 bg-sky-50/90 px-2 py-1 text-[11px] shadow-[inset_0_0_0_1px_rgba(125,211,252,0.2)] hover:border-sky-400">
                                    <div class="truncate font-semibold text-slate-800">{{ $lead->nombre_completo }}</div>
                                    <div class="truncate text-slate-500">{{ $lead->campana ?: 'Sin campaña' }}</div>
                                    @if($lead->owner)
                                        <div class="truncate text-[10px] text-slate-500">Resp: {{ $lead->owner->nombre ?? $lead->owner->email }}</div>
                                    @endif
                                    <div class="mt-1 flex flex-wrap items-center gap-1">
                                        <span class="inline-flex rounded bg-sky-200/80 px-1.5 py-0.5 text-[10px] font-semibold text-sky-900">Lead</span>
                                        <span class="inline-flex rounded px-1.5 py-0.5 text-[10px] {{ $etapaColors[$lead->etapa] ?? 'bg-slate-100 text-slate-700' }}">{{ $lead->etapa }}</span>
                                    </div>
                                </a>
                                <details class="rounded border border-slate-200 bg-white px-2 py-1">
                                    <summary class="cursor-pointer text-[10px] font-semibold text-[#15537c]">Ya contacté</summary>
                                    <form method="POST" action="{{ route('leads.contactado-rapido', $lead->id) }}" class="mt-2 space-y-1">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="tipo" value="llamada">
                                        <textarea name="resumen" rows="2" required class="w-full rounded border border-slate-300 px-2 py-1 text-[10px]" placeholder="Resumen del contacto..."></textarea>
                                        <input type="datetime-local" name="proximo_contacto_at" class="w-full rounded border border-slate-300 px-2 py-1 text-[10px]">
                                        <select name="etapa_siguiente" class="w-full rounded border border-slate-300 px-2 py-1 text-[10px]">
                                            <option value="">Mantener etapa</option>
                                            @foreach($etapas as $etOpt)
                                                <option value="{{ $etOpt }}">{{ ucwords(str_replace('_',' ', $etOpt)) }}</option>
                                            @endforeach
                                        </select>
                                        <button class="w-full rounded bg-[#15537c] px-2 py-1 text-[10px] font-semibold text-white">Guardar</button>
                                    </form>
                                </details>
                            @else
                                @php $ev = $entry['evento']; @endphp
                                <div class="rounded border border-violet-200 bg-violet-50/90 px-2 py-1 text-[11px] shadow-[inset_0_0_0_1px_rgba(196,181,253,0.25)]">
                                    <div class="truncate font-semibold text-slate-900">{{ $ev->titulo }}</div>
                                    <div class="text-[10px] text-violet-900/80">
                                        @if($ev->todo_el_dia)
                                            Todo el día
                                        @else
                                            {{ $ev->starts_at->timezone(config('app.timezone'))->format('H:i') }}
                                            @if($ev->ends_at)
                                                – {{ $ev->ends_at->timezone(config('app.timezone'))->format('H:i') }}
                                            @endif
                                        @endif
                                    </div>
                                    @if($ev->ubicacion)
                                        <div class="truncate text-[10px] text-slate-600"><i class="fas fa-map-marker-alt mr-0.5 opacity-70"></i>{{ $ev->ubicacion }}</div>
                                    @endif
                                    @if($ev->descripcion)
                                        <div class="mt-0.5 line-clamp-2 text-[10px] text-slate-600">{{ $ev->descripcion }}</div>
                                    @endif
                                    <div class="mt-1 flex flex-wrap items-center justify-between gap-1">
                                        <span class="inline-flex rounded bg-violet-200/90 px-1.5 py-0.5 text-[10px] font-semibold text-violet-900">Evento</span>
                                        @if(auth()->check() && (auth()->user()->rol === 'admin' || (int) $ev->created_by === (int) auth()->id()))
                                            <form method="POST" action="{{ route('leads.agenda-eventos.destroy', $ev) }}" class="inline" onsubmit="return confirm('¿Eliminar este evento de la agenda?');">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="mes" value="{{ $baseMonth->format('Y-m') }}">
                                                @if(request()->filled('etapa'))<input type="hidden" name="etapa" value="{{ request('etapa') }}">@endif
                                                @if(request()->filled('origen'))<input type="hidden" name="origen" value="{{ request('origen') }}">@endif
                                                @if(request()->filled('campana'))<input type="hidden" name="campana" value="{{ request('campana') }}">@endif
                                                @if(request()->filled('owner_id'))<input type="hidden" name="filter_owner_id" value="{{ request('owner_id') }}">@endif
                                                <button type="submit" class="text-[10px] font-semibold text-red-700 hover:underline">Eliminar</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                        @if(count($items) > 3)
                            <div class="text-[10px] text-slate-500">+{{ count($items) - 3 }} más…</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-xl border border-amber-200 bg-amber-50/40 p-5 shadow-sm">
        <h3 class="mb-3 text-base font-semibold text-amber-900">Leads sin fecha de seguimiento programada</h3>
        @if(($sinFecha ?? collect())->isEmpty())
            <p class="text-sm text-amber-800/80">Excelente, no hay leads abiertos sin fecha.</p>
        @else
            <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                @foreach($sinFecha as $lead)
                    <a href="{{ route('leads.show', $lead->id) }}" class="rounded-lg border border-amber-200 bg-white px-3 py-2 text-sm hover:border-[#15537c]/40">
                        <div class="font-semibold text-slate-900">{{ $lead->codigo }} · {{ $lead->nombre_completo }}</div>
                        <div class="text-xs text-slate-500">{{ $lead->campana ?: 'Sin campaña' }} · {{ ucfirst(str_replace('_',' ', $lead->etapa)) }}</div>
                    </a>
                @endforeach
            </div>
            <p class="mt-2 text-xs text-amber-900/70">Tip: al registrar interacción, define “Próximo contacto” para que aparezca en el calendario mensual.</p>
        @endif
    </div>
</div>

<script>
(function () {
    const cb = document.getElementById('agenda_todo_dia');
    const hi = document.getElementById('agenda_hora_inicio');
    const hf = document.getElementById('agenda_hora_fin');
    const toggleEventoBtn = document.getElementById('toggle-evento-form');
    const eventoFormContainer = document.getElementById('evento-form-container');

    toggleEventoBtn?.addEventListener('click', function () {
        if (!eventoFormContainer) return;
        const hidden = eventoFormContainer.classList.toggle('hidden');
        this.setAttribute('aria-expanded', hidden ? 'false' : 'true');
    });

    function sync() {
        if (!cb || !hi || !hf) return;
        const on = cb.checked;
        hi.disabled = on;
        hf.disabled = on;
        if (on) { hi.value = ''; hf.value = ''; }
        else if (!hi.value) { hi.value = '09:00'; }
    }
    cb?.addEventListener('change', sync);
    sync();
})();
</script>
@endsection
