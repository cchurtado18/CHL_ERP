@extends('layouts.app-new')

@section('title', 'Trabajos - Calendario')
@section('navbar-title', 'Trabajos del equipo')

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
@endphp

@section('content')
<div class="mx-auto w-full max-w-[1400px] space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800">{{ session('success') }}</div>
    @endif

    @include('leads._nav')

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">
                    @if($esAdmin)
                        Calendario de trabajos
                    @else
                        Mi calendario de trabajos
                    @endif
                </h1>
                <p class="text-sm text-slate-600">
                    Cada persona ve solo sus trabajos. Recorrido: Asignado → Visto → Trabajando → Casi termino → Finalizado.
                    @unless($esAdmin)
                        Tienes <strong>{{ $misPendientes }}</strong> trabajo(s) pendiente(s).
                    @else
                        Como admin puedes filtrar por persona o ver todo el equipo.
                    @endunless
                </p>
            </div>
        </div>

        <form method="GET" action="{{ route('leads.trabajos.calendar') }}" class="grid grid-cols-1 gap-3 md:grid-cols-5">
            <input type="month" name="mes" value="{{ request('mes', $baseMonth->format('Y-m')) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
            <select name="estado" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
                <option value="">Todos los estados</option>
                @foreach($estados as $st)
                    <option value="{{ $st }}" @selected(request('estado') === $st)>{{ \App\Models\Trabajo::ESTADO_LABELS[$st] }}</option>
                @endforeach
            </select>
            <select name="prioridad" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
                <option value="">Todas las prioridades</option>
                @foreach($prioridades as $prio)
                    <option value="{{ $prio }}" @selected(request('prioridad') === $prio)>{{ \App\Models\Trabajo::PRIORIDAD_LABELS[$prio] }}</option>
                @endforeach
            </select>
            @if($esAdmin)
            <select name="asignado_a" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
                <option value="" @selected(! request()->filled('asignado_a'))>Mis trabajos</option>
                <option value="todos" @selected(request('asignado_a') === 'todos')>Todo el equipo</option>
                @foreach($equipo as $u)
                    <option value="{{ $u->id }}" @selected((string) request('asignado_a') === (string) $u->id)>{{ $u->nombre ?? $u->email }}</option>
                @endforeach
            </select>
            @endif
            <div class="flex gap-2">
                <button class="rounded-lg bg-[#15537c] px-4 py-2 text-sm font-semibold text-white hover:bg-[#0f3d5c]">Filtrar</button>
                <a href="{{ route('leads.trabajos.calendar', ['mes' => $baseMonth->format('Y-m')]) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        {{-- Cabecera del calendario --}}
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50/60 px-5 py-3">
            <div class="flex items-center gap-2">
                <a href="{{ route('leads.trabajos.calendar', array_merge($queryBase, ['mes' => $prevMonth])) }}" class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-600 transition hover:border-[#15537c] hover:text-[#15537c]" title="Mes anterior"><i class="fas fa-chevron-left text-xs"></i></a>
                <a href="{{ route('leads.trabajos.calendar', array_merge($queryBase, ['mes' => now()->format('Y-m')])) }}" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-[#15537c] hover:text-[#15537c]">Hoy</a>
                <a href="{{ route('leads.trabajos.calendar', array_merge($queryBase, ['mes' => $nextMonth])) }}" class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-600 transition hover:border-[#15537c] hover:text-[#15537c]" title="Mes siguiente"><i class="fas fa-chevron-right text-xs"></i></a>
            </div>
            <h2 class="text-lg font-bold capitalize text-[#15537c]">{{ $baseMonth->translatedFormat('F Y') }}</h2>
            <div class="flex flex-wrap items-center gap-2.5 text-[11px] text-slate-600">
                @foreach(\App\Models\Trabajo::ESTADO_LABELS as $stKey => $stLabel)
                    <span class="inline-flex items-center gap-1"><span class="h-2.5 w-2.5 rounded-full {{ explode(' ', \App\Models\Trabajo::ESTADO_COLORS[$stKey])[0] }} ring-1 ring-black/10"></span> {{ $stLabel }}</span>
                @endforeach
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
                    $inMonth = $day->month === $baseMonth->month;
                    $isToday = $day->isToday();
                    $lista = $porDia[$key] ?? [];
                @endphp
                <div class="group min-h-[86px] p-1 transition {{ $inMonth ? 'bg-white hover:bg-slate-50/70' : 'bg-slate-50/70' }}">
                    <div class="mb-0.5 flex items-center justify-between px-0.5">
                        <span class="inline-flex h-5 w-5 items-center justify-center rounded-full text-[11px] font-semibold {{ $isToday ? 'bg-[#15537c] text-white shadow-sm' : ($inMonth ? 'text-slate-700' : 'text-slate-400') }}">{{ $day->day }}</span>
                        @if(count($lista) > 3)
                            <span class="rounded-full bg-[#15537c]/10 px-1.5 text-[9px] font-bold text-[#15537c]">{{ count($lista) }}</span>
                        @endif
                    </div>
                    <div class="space-y-0.5">
                        @foreach(array_slice($lista, 0, 3) as $t)
                            @php
                                $prioBorder = match($t->prioridad) {
                                    'urgente' => 'border-red-400',
                                    'no_urgente' => 'border-slate-300',
                                    default => 'border-amber-400',
                                };
                            @endphp
                            <a href="{{ route('leads.trabajos.show', $t->id) }}"
                               class="block overflow-hidden rounded-md border-l-2 {{ $prioBorder }} px-1.5 py-1 text-[10px] leading-tight transition hover:brightness-95 {{ $t->colorEstado() }}"
                               title="{{ $t->titulo }} · {{ $t->labelPrioridad() }} · {{ $t->labelEstado() }}{{ $t->cliente ? ' · '.$t->cliente->nombre_completo : '' }}{{ $esAdmin ? ' · '.($t->asignado->nombre ?? $t->asignado->email ?? '') : '' }}">
                                <span class="block truncate font-semibold">{{ $t->fecha_programada->format('H:i') }} {{ $t->titulo }}</span>
                                <span class="block truncate text-[9px] opacity-75">
                                    {{ $t->labelPrioridad() }}@if($esAdmin && $t->asignado) · {{ $t->asignado->nombre ?? $t->asignado->email }}@elseif($t->cliente) · {{ $t->cliente->nombre_completo }}@endif
                                </span>
                            </a>
                        @endforeach
                        @if(count($lista) > 3)
                            <div class="px-1 text-[9px] font-medium text-slate-400">+{{ count($lista) - 3 }} más…</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Leyenda de prioridades --}}
        <div class="flex flex-wrap items-center gap-3 border-t border-slate-200 bg-slate-50/60 px-5 py-2.5 text-[11px] text-slate-600">
            <span class="font-semibold text-slate-500">Prioridad:</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-3 w-1 rounded-full bg-red-400"></span> Urgente</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-3 w-1 rounded-full bg-amber-400"></span> Promedio</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-3 w-1 rounded-full bg-slate-300"></span> No urgente</span>
        </div>
    </div>
</div>
@endsection
