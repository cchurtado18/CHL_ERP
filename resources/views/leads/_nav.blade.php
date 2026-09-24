{{-- Navegación interna del módulo Leads / Trabajos --}}
@php
    $esAdminNav = auth()->user()?->esAdmin();
@endphp
<div class="mb-4 flex flex-wrap gap-2">
    <a href="{{ route('leads.calendar') }}" class="rounded-lg px-4 py-2 text-sm font-medium {{ request()->routeIs('leads.calendar') ? 'bg-[#15537c] text-white' : 'border border-slate-300 text-slate-700 hover:bg-slate-50' }}">Calendario leads</a>
    <a href="{{ route('leads.index') }}" class="rounded-lg px-4 py-2 text-sm font-medium {{ request()->routeIs('leads.index') ? 'bg-[#15537c] text-white' : 'border border-slate-300 text-slate-700 hover:bg-slate-50' }}">Lista leads</a>
    <a href="{{ route('leads.trabajos.calendar') }}" class="rounded-lg px-4 py-2 text-sm font-medium {{ request()->routeIs('leads.trabajos.calendar') ? 'bg-[#15537c] text-white' : 'border border-slate-300 text-slate-700 hover:bg-slate-50' }}">Calendario trabajos</a>
    <a href="{{ route('leads.trabajos.index') }}" class="rounded-lg px-4 py-2 text-sm font-medium {{ request()->routeIs('leads.trabajos.index') ? 'bg-[#15537c] text-white' : 'border border-slate-300 text-slate-700 hover:bg-slate-50' }}">Lista trabajos</a>
    @if($esAdminNav)
        <a href="{{ route('leads.trabajos.create') }}" class="rounded-lg bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700">+ Asignar trabajo</a>
    @endif
</div>
