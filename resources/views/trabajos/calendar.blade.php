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
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('leads.trabajos.calendar', array_merge($queryBase, ['mes' => $prevMonth])) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">← Mes ant.</a>
                <a href="{{ route('leads.trabajos.calendar', array_merge($queryBase, ['mes' => now()->format('Y-m')])) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">Hoy</a>
                <a href="{{ route('leads.trabajos.calendar', array_merge($queryBase, ['mes' => $nextMonth])) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">Mes sig. →</a>
            </div>
        </div>

        <form method="GET" action="{{ route('leads.trabajos.calendar') }}" class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-5">
            <input type="month" name="mes" value="{{ request('mes', $baseMonth->format('Y-m')) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <select name="estado" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Todos los estados</option>
                @foreach($estados as $st)
                    <option value="{{ $st }}" @selected(request('estado') === $st)>{{ \App\Models\Trabajo::ESTADO_LABELS[$st] }}</option>
                @endforeach
            </select>
            <select name="prioridad" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Todas las prioridades</option>
                @foreach($prioridades as $prio)
                    <option value="{{ $prio }}" @selected(request('prioridad') === $prio)>{{ \App\Models\Trabajo::PRIORIDAD_LABELS[$prio] }}</option>
                @endforeach
            </select>
            @if($esAdmin)
            <select name="asignado_a" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="" @selected(! request()->filled('asignado_a'))>Mis trabajos</option>
                <option value="todos" @selected(request('asignado_a') === 'todos')>Todo el equipo</option>
                @foreach($equipo as $u)
                    <option value="{{ $u->id }}" @selected((string) request('asignado_a') === (string) $u->id)>{{ $u->nombre ?? $u->email }}</option>
                @endforeach
            </select>
            @endif
            <div class="flex gap-2">
                <button class="rounded-lg bg-[#15537c] px-4 py-2 text-sm font-semibold text-white">Filtrar</button>
                <a href="{{ route('leads.trabajos.calendar', ['mes' => $baseMonth->format('Y-m')]) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm">Limpiar</a>
            </div>
        </form>

        <div class="mb-3 text-center text-lg font-semibold text-slate-800">{{ $baseMonth->translatedFormat('F Y') }}</div>

        <div class="grid grid-cols-7 gap-px overflow-hidden rounded-xl border border-slate-200 bg-slate-200">
            @foreach(['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'] as $dow)
                <div class="bg-slate-100 px-2 py-2 text-center text-xs font-semibold uppercase text-slate-600">{{ $dow }}</div>
            @endforeach
            @foreach($days as $day)
                @php
                    $key = $day->format('Y-m-d');
                    $inMonth = $day->month === $baseMonth->month;
                    $isToday = $day->isToday();
                    $lista = $porDia[$key] ?? [];
                @endphp
                <div class="min-h-[110px] bg-white p-1.5 {{ $inMonth ? '' : 'bg-slate-50 opacity-60' }} {{ $isToday ? 'ring-2 ring-inset ring-[#15537c]/40' : '' }}">
                    <div class="mb-1 text-right text-xs font-semibold {{ $isToday ? 'text-[#15537c]' : 'text-slate-500' }}">{{ $day->day }}</div>
                    <div class="space-y-1">
                        @foreach($lista as $t)
                            <a href="{{ route('leads.trabajos.show', $t->id) }}" class="block rounded border border-slate-200 px-1.5 py-1 text-[11px] hover:border-[#15537c]/50 {{ $t->colorEstado() }}">
                                <div class="truncate font-semibold">{{ $t->titulo }}</div>
                                <div class="mt-0.5 flex flex-wrap gap-1">
                                    <span class="rounded px-1 py-0.5 text-[10px] font-semibold {{ $t->colorPrioridad() }}">{{ $t->labelPrioridad() }}</span>
                                    <span class="rounded px-1 py-0.5 text-[10px] font-semibold">{{ $t->labelEstado() }}</span>
                                </div>
                                @if($t->cliente)
                                    <div class="truncate opacity-80">{{ $t->cliente->nombre_completo }}</div>
                                @endif
                                @if($esAdmin)
                                    <div class="truncate text-[10px] opacity-70">{{ $t->asignado->nombre ?? $t->asignado->email ?? '—' }}</div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 flex flex-wrap gap-3 text-xs text-slate-600">
            @foreach(\App\Models\Trabajo::ESTADO_LABELS as $key => $label)
                <span class="inline-flex items-center gap-1.5"><span class="rounded px-2 py-0.5 {{ \App\Models\Trabajo::ESTADO_COLORS[$key] }}">{{ $label }}</span></span>
            @endforeach
        </div>
    </div>
</div>
@endsection
