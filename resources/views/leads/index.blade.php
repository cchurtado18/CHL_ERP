@extends('layouts.app-new')

@section('title', 'Leads - Lista')
@section('navbar-title', 'Leads')

@section('content')
<div class="mx-auto w-full max-w-[1300px] space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Leads - Lista</h1>
                <p class="text-sm text-slate-600">Vista tabular del pipeline comercial con filtros.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('leads.calendar') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm">Ver calendario</a>
                <a href="{{ route('leads.create') }}" class="rounded-lg bg-[#15537c] px-4 py-2 text-sm font-semibold text-white">+ Nuevo lead</a>
            </div>
        </div>

        <form method="GET" class="grid grid-cols-1 gap-3 md:grid-cols-5">
            <input type="text" name="busqueda" value="{{ request('busqueda') }}" placeholder="Buscar nombre, campaña, teléfono..." class="rounded-lg border border-slate-300 px-3 py-2 text-sm md:col-span-2">
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
            <select name="owner_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Todos responsables</option>
                @foreach($owners as $owner)
                    <option value="{{ $owner->id }}" @selected((string) request('owner_id') === (string) $owner->id)>{{ $owner->nombre ?? $owner->email }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button class="rounded-lg bg-[#15537c] px-4 py-2 text-sm font-semibold text-white">Filtrar</button>
                <a href="{{ route('leads.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm">Limpiar</a>
            </div>
            <select name="campana" class="rounded-lg border border-slate-300 px-3 py-2 text-sm md:col-span-2">
                <option value="">Todas campañas</option>
                @foreach($campanas as $campana)
                    <option value="{{ $campana }}" @selected(request('campana') === $campana)>{{ $campana }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-xs text-slate-500">Nuevos del mes</div><div class="text-2xl font-bold">{{ $kpis['nuevos_mes'] }}</div></div>
        <div class="rounded-xl border border-red-200 bg-white p-4 shadow-sm"><div class="text-xs text-slate-500">Seguimientos vencidos</div><div class="text-2xl font-bold text-red-700">{{ $kpis['vencidos'] }}</div></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-xs text-slate-500">Tasa conversión mes</div><div class="text-2xl font-bold">{{ $kpis['tasa_conversion'] }}%</div></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-xs text-slate-500">Top campaña</div><div class="text-sm font-semibold">{{ optional($kpis['por_campana']->first())->campana ?? '—' }}</div></div>
    </div>
    @if(($kpis['motivos_perdida'] ?? collect())->isNotEmpty())
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Motivos de pérdida más frecuentes</div>
            <div class="flex flex-wrap gap-2">
                @foreach($kpis['motivos_perdida'] as $m)
                    <span class="rounded-full bg-rose-50 px-3 py-1 text-xs text-rose-700">{{ ucwords(str_replace('_',' ', (string) $m->motivo_perdida_clave)) }} ({{ $m->total }})</span>
                @endforeach
            </div>
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px] text-left text-sm">
                <thead class="bg-slate-50 text-slate-700">
                    <tr>
                        <th class="px-3 py-2">Código</th>
                        <th class="px-3 py-2">Lead</th>
                        <th class="px-3 py-2">Campaña</th>
                        <th class="px-3 py-2">Etapa</th>
                        <th class="px-3 py-2">Responsable</th>
                        <th class="px-3 py-2">Próximo contacto</th>
                        <th class="px-3 py-2">Resultado</th>
                        <th class="px-3 py-2 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leads as $lead)
                        <tr class="border-t border-slate-100">
                            <td class="px-3 py-2 font-semibold text-[#15537c]">{{ $lead->codigo }}</td>
                            <td class="px-3 py-2">
                                <div class="font-medium text-slate-900">{{ $lead->nombre_completo }}</div>
                                <div class="text-xs text-slate-500">{{ $lead->telefono ?: 'Sin teléfono' }}{{ $lead->email ? ' · '.$lead->email : '' }}</div>
                            </td>
                            <td class="px-3 py-2">{{ $lead->campana ?: '—' }}</td>
                            <td class="px-3 py-2">{{ ucwords(str_replace('_', ' ', $lead->etapa)) }}</td>
                            <td class="px-3 py-2">{{ $lead->owner->nombre ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $lead->proximo_contacto_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="px-3 py-2">{{ ucfirst($lead->resultado) }}</td>
                            <td class="px-3 py-2 text-right">
                                <a href="{{ route('leads.show', $lead->id) }}" class="rounded border border-slate-300 px-2 py-1 text-xs">Ver</a>
                                <a href="{{ route('leads.edit', $lead->id) }}" class="rounded border border-slate-300 px-2 py-1 text-xs">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-3 py-8 text-center text-slate-500">No hay leads para mostrar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-4 py-3">
            {{ $leads->links() }}
        </div>
    </div>
</div>
@endsection
