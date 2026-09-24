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

    @include('leads._nav')

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

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#15537c]/10 text-[#15537c]"><i class="fas fa-user-plus"></i></div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Nuevos del mes</p>
                    <p class="text-xl font-bold text-slate-900">{{ $kpis['nuevos_mes'] }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-red-200 bg-white p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 text-red-600"><i class="fas fa-exclamation-circle"></i></div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Seg. vencidos</p>
                    <p class="text-xl font-bold text-red-700">{{ $kpis['vencidos'] }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600"><i class="fas fa-chart-line"></i></div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Tasa conversión</p>
                    <p class="text-xl font-bold text-slate-900">{{ $kpis['tasa_conversion'] }}%</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-100 text-violet-600"><i class="fas fa-bullhorn"></i></div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Campañas activas</p>
                    <p class="text-xl font-bold text-slate-900">{{ $kpis['por_campana']->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        {{-- Cabecera del calendario --}}
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50/60 px-5 py-3">
            <div class="flex items-center gap-2">
                <a href="{{ route('leads.calendar', array_merge($queryBase ?? [], ['mes' => $prevMonth])) }}" class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-600 transition hover:border-[#15537c] hover:text-[#15537c]" title="Mes anterior"><i class="fas fa-chevron-left text-xs"></i></a>
                <a href="{{ route('leads.calendar', array_merge($queryBase ?? [], ['mes' => now()->format('Y-m')])) }}" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-[#15537c] hover:text-[#15537c]">Hoy</a>
                <a href="{{ route('leads.calendar', array_merge($queryBase ?? [], ['mes' => $nextMonth])) }}" class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-600 transition hover:border-[#15537c] hover:text-[#15537c]" title="Mes siguiente"><i class="fas fa-chevron-right text-xs"></i></a>
            </div>
            <h2 class="text-lg font-bold capitalize text-[#15537c]">{{ $baseMonth->translatedFormat('F Y') }}</h2>
            <div class="flex flex-wrap items-center gap-3 text-[11px] text-slate-600">
                <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-sky-400"></span> Lead</span>
                <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-violet-400"></span> Evento</span>
            </div>
        </div>

        {{-- Grid --}}
        <div class="grid grid-cols-7 gap-px bg-slate-200">
            @foreach(['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'] as $dow)
                <div class="bg-slate-50 px-2 py-1.5 text-center text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ $dow }}</div>
            @endforeach
            @foreach($days as $day)
                @php
                    $key = $day->format('Y-m-d');
                    $items = $itemsPorDia[$key] ?? [];
                    $isCurrent = $day->month === $baseMonth->month;
                    $isToday = $day->isToday();
                @endphp
                <div class="group min-h-[86px] p-1 transition {{ $isCurrent ? 'bg-white hover:bg-slate-50/70' : 'bg-slate-50/70' }}">
                    <div class="mb-0.5 flex items-center justify-between px-0.5">
                        <span class="inline-flex h-5 w-5 items-center justify-center rounded-full text-[11px] font-semibold {{ $isToday ? 'bg-[#15537c] text-white shadow-sm' : ($isCurrent ? 'text-slate-700' : 'text-slate-400') }}">{{ $day->day }}</span>
                        @if(count($items) > 2)
                            <span class="rounded-full bg-[#15537c]/10 px-1.5 text-[9px] font-bold text-[#15537c]">{{ count($items) }}</span>
                        @endif
                    </div>
                    <div class="space-y-0.5">
                        @foreach(array_slice($items, 0, 2) as $entry)
                            @if($entry['type'] === 'lead')
                                @php $lead = $entry['lead']; @endphp
                                <div class="overflow-hidden rounded-md border-l-2 border-sky-400 bg-sky-50 transition hover:bg-sky-100">
                                    <a href="{{ route('leads.show', $lead->id) }}" class="block px-1.5 pt-1 text-[10px] leading-tight" title="{{ $lead->nombre_completo }} · {{ $lead->campana ?: 'Sin campaña' }}{{ $lead->owner ? ' · Resp: '.($lead->owner->nombre ?? $lead->owner->email) : '' }}">
                                        <span class="block truncate font-semibold text-slate-800">{{ $lead->proximo_contacto_at?->format('H:i') }} {{ $lead->nombre_completo }}</span>
                                        <span class="mt-0.5 mb-1 inline-flex rounded px-1 py-px text-[9px] font-semibold capitalize {{ $etapaColors[$lead->etapa] ?? 'bg-slate-100 text-slate-700' }}">{{ str_replace('_', ' ', $lead->etapa) }}</span>
                                    </a>
                                    <details class="border-t border-sky-200/60 px-1.5 py-0.5">
                                        <summary class="cursor-pointer list-none text-[9px] font-semibold text-[#15537c] hover:underline"><i class="fas fa-phone-alt mr-0.5 text-[8px]"></i>Ya contacté</summary>
                                        <form method="POST" action="{{ route('leads.contactado-rapido', $lead->id) }}" class="mt-1 space-y-1 pb-1">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="tipo" value="llamada">
                                            <textarea name="resumen" rows="2" required class="w-full rounded border border-slate-300 px-1.5 py-1 text-[10px]" placeholder="Resumen del contacto..."></textarea>
                                            <input type="datetime-local" name="proximo_contacto_at" class="w-full rounded border border-slate-300 px-1.5 py-1 text-[10px]">
                                            <select name="etapa_siguiente" class="w-full rounded border border-slate-300 px-1.5 py-1 text-[10px]">
                                                <option value="">Mantener etapa</option>
                                                @foreach($etapas as $etOpt)
                                                    <option value="{{ $etOpt }}">{{ ucwords(str_replace('_',' ', $etOpt)) }}</option>
                                                @endforeach
                                            </select>
                                            <button class="w-full rounded bg-[#15537c] px-2 py-1 text-[10px] font-semibold text-white hover:bg-[#0f3d5c]">Guardar</button>
                                        </form>
                                    </details>
                                </div>
                            @else
                                @php $ev = $entry['evento']; @endphp
                                <details class="overflow-hidden rounded-md border-l-2 border-violet-400 bg-violet-50 transition hover:bg-violet-100">
                                    <summary class="cursor-pointer list-none px-1.5 py-1 text-[10px] leading-tight" title="{{ $ev->titulo }}{{ $ev->ubicacion ? ' · '.$ev->ubicacion : '' }}">
                                        <span class="block truncate font-semibold text-slate-800">
                                            @unless($ev->todo_el_dia){{ $ev->starts_at->timezone(config('app.timezone'))->format('H:i') }}@endunless
                                            {{ $ev->titulo }}
                                        </span>
                                    </summary>
                                    <div class="space-y-0.5 border-t border-violet-200/60 px-1.5 py-1 text-[9px] text-slate-600">
                                        <div>
                                            @if($ev->todo_el_dia)
                                                Todo el día
                                            @else
                                                {{ $ev->starts_at->timezone(config('app.timezone'))->format('H:i') }}@if($ev->ends_at) – {{ $ev->ends_at->timezone(config('app.timezone'))->format('H:i') }}@endif
                                            @endif
                                        </div>
                                        @if($ev->ubicacion)
                                            <div class="truncate"><i class="fas fa-map-marker-alt mr-0.5 opacity-70"></i>{{ $ev->ubicacion }}</div>
                                        @endif
                                        @if($ev->descripcion)
                                            <div class="line-clamp-2">{{ $ev->descripcion }}</div>
                                        @endif
                                        @if(auth()->check() && (auth()->user()->rol === 'admin' || (int) $ev->created_by === (int) auth()->id()))
                                            <form method="POST" action="{{ route('leads.agenda-eventos.destroy', $ev) }}" onsubmit="return confirm('¿Eliminar este evento de la agenda?');">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="mes" value="{{ $baseMonth->format('Y-m') }}">
                                                @if(request()->filled('etapa'))<input type="hidden" name="etapa" value="{{ request('etapa') }}">@endif
                                                @if(request()->filled('origen'))<input type="hidden" name="origen" value="{{ request('origen') }}">@endif
                                                @if(request()->filled('campana'))<input type="hidden" name="campana" value="{{ request('campana') }}">@endif
                                                @if(request()->filled('owner_id'))<input type="hidden" name="filter_owner_id" value="{{ request('owner_id') }}">@endif
                                                <button type="submit" class="font-semibold text-red-700 hover:underline">Eliminar</button>
                                            </form>
                                        @endif
                                    </div>
                                </details>
                            @endif
                        @endforeach
                        @if(count($items) > 2)
                            <div class="px-1 text-[9px] font-medium text-slate-400">+{{ count($items) - 2 }} más…</div>
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
