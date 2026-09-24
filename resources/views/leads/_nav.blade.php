{{-- Navegación interna del módulo Leads / Trabajos --}}
@php
    $esAdminNav = auth()->user()?->esAdmin();
@endphp
<div class="flex flex-wrap items-center justify-between gap-3">
    <div class="inline-flex flex-wrap gap-1 rounded-xl border border-slate-200 bg-white p-1 shadow-sm">
        <a href="{{ route('leads.calendar') }}" class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-sm font-medium transition {{ request()->routeIs('leads.calendar') ? 'bg-[#15537c] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}"><i class="far fa-calendar-alt text-xs"></i> Calendario leads</a>
        <a href="{{ route('leads.index') }}" class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-sm font-medium transition {{ request()->routeIs('leads.index') ? 'bg-[#15537c] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}"><i class="fas fa-list text-xs"></i> Lista leads</a>
        <a href="{{ route('leads.trabajos.calendar') }}" class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-sm font-medium transition {{ request()->routeIs('leads.trabajos.calendar') ? 'bg-[#15537c] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}"><i class="fas fa-calendar-check text-xs"></i> Calendario trabajos</a>
        <a href="{{ route('leads.trabajos.index') }}" class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-sm font-medium transition {{ request()->routeIs('leads.trabajos.index') ? 'bg-[#15537c] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}"><i class="fas fa-tasks text-xs"></i> Lista trabajos</a>
    </div>
    @if($esAdminNav)
        <a href="{{ route('leads.trabajos.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-700"><i class="fas fa-plus text-xs"></i> Asignar trabajo</a>
    @endif
</div>
