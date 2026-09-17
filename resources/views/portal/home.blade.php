@extends('layouts.portal')

@section('title', 'Inicio')
@section('navbar-title', 'Inicio')

@section('content')
<div class="mx-auto w-full max-w-[1400px] space-y-6 sm:space-y-8">
    <div>
        <h1 class="text-xl font-bold text-slate-800 sm:text-2xl">Hola, {{ auth()->user()->nombre }}</h1>
        <p class="mt-1 text-sm text-slate-500">Resumen de tus envíos y facturas.</p>
    </div>

    <div class="grid grid-cols-2 gap-3 sm:gap-5 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#15537c]/10 text-[#15537c] text-xl sm:h-14 sm:w-14 sm:text-2xl"><i class="fas fa-boxes"></i></div>
                <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 sm:text-sm">Paquetes</p>
                    <p class="text-xl font-bold text-slate-800 sm:text-2xl">{{ $totalPaquetes }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600 text-xl sm:h-14 sm:w-14 sm:text-2xl"><i class="fas fa-check-circle"></i></div>
                <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 sm:text-sm">Recibidos</p>
                    <p class="text-xl font-bold text-slate-800 sm:text-2xl">{{ $recibidos }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 text-xl sm:h-14 sm:w-14 sm:text-2xl"><i class="fas fa-box-open"></i></div>
                <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 sm:text-sm">Entregados</p>
                    <p class="text-xl font-bold text-slate-800 sm:text-2xl">{{ $entregados }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700 text-xl sm:h-14 sm:w-14 sm:text-2xl"><i class="fas fa-file-invoice-dollar"></i></div>
                <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 sm:text-sm">Fact. pend.</p>
                    <p class="text-xl font-bold text-slate-800 sm:text-2xl">{{ $facturasPendientes }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 sm:px-5">
                <h2 class="font-bold text-slate-800"><i class="fas fa-box text-[#15537c] mr-2"></i>Últimos paquetes</h2>
                <a href="{{ route('portal.paquetes.index') }}" class="text-sm font-semibold text-[#15537c] hover:underline">Ver todos</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($ultimosPaquetes as $p)
                    @php
                        $badge = match($p->estado) {
                            'recibido' => 'bg-amber-200 text-amber-900',
                            'entregado' => 'bg-emerald-200 text-emerald-900',
                            default => 'bg-slate-200 text-slate-900',
                        };
                    @endphp
                    <a href="{{ route('portal.paquetes.show', $p->id) }}" class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-slate-50 sm:px-5">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-slate-800">{{ $p->tracking_codigo ?: $p->numero_guia ?: ('#'.$p->id) }}</p>
                            <p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($p->fecha_ingreso)->format('d/m/Y') }} · {{ $p->servicio->tipo_servicio ?? '—' }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold capitalize {{ $badge }}">{{ str_replace('_',' ', $p->estado) }}</span>
                    </a>
                @empty
                    <p class="px-4 py-8 text-center text-sm text-slate-400 sm:px-5">Aún no tienes paquetes registrados.</p>
                @endforelse
            </div>
        </section>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 sm:px-5">
                <h2 class="font-bold text-slate-800"><i class="fas fa-file-invoice text-[#15537c] mr-2"></i>Últimas facturas</h2>
                <a href="{{ route('portal.facturas.index') }}" class="text-sm font-semibold text-[#15537c] hover:underline">Ver todas</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($ultimasFacturas as $f)
                    <a href="{{ route('portal.facturas.show', $f->id) }}" class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-slate-50 sm:px-5">
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-800">Folio {{ $f->etiquetaFolio() }}</p>
                            <p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($f->fecha_factura)->format('d/m/Y') }} · ${{ number_format($f->monto_total, 2) }}</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold capitalize text-slate-700">{{ str_replace('_', ' ', $f->estado_pago) }}</span>
                    </a>
                @empty
                    <p class="px-4 py-8 text-center text-sm text-slate-400 sm:px-5">Aún no tienes facturas.</p>
                @endforelse
            </div>
        </section>
    </div>

    @if(isset($notificaciones) && $notificaciones->isNotEmpty())
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3 sm:px-5">
            <h2 class="font-bold text-slate-800"><i class="fas fa-bell text-[#15537c] mr-2"></i>Avisos recientes</h2>
        </div>
        <ul class="divide-y divide-slate-100">
            @foreach($notificaciones as $n)
                <li class="px-4 py-3 sm:px-5">
                    <p class="font-semibold text-slate-800">{{ $n->titulo }}</p>
                    <p class="text-sm text-slate-600">{{ $n->mensaje }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ \Carbon\Carbon::parse($n->fecha)->format('d/m/Y H:i') }}</p>
                </li>
            @endforeach
        </ul>
    </section>
    @endif
</div>
@endsection
