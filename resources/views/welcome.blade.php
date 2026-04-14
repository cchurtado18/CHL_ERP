@extends('layouts.app-new')

@section('title', 'Dashboard - CH LOGISTICS ERP')
@section('navbar-title', 'Dashboard')

@section('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">
@endsection

@section('content')
<div class="mx-auto w-full max-w-[1400px] space-y-8">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">CH LOGISTICS ERP</h1>
            <p class="mt-1 text-slate-600">Panel ejecutivo</p>
        </div>
        <p class="text-slate-500 font-medium"><i class="fas fa-calendar-alt mr-2"></i>{{ \Carbon\Carbon::now()->format('d M Y') }}</p>
    </div>

    @if(auth()->check() && auth()->user()->rol === 'admin')
    {{-- Módulos --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @php
            $modules = [
                ['title' => 'Clientes', 'icon' => 'fa-users', 'count' => $totalClientes ?? 0, 'route' => route('clientes.index')],
                ['title' => 'Usuarios', 'icon' => 'fa-user-shield', 'count' => $totalUsuarios ?? 0, 'route' => route('usuarios.index')],
                ['title' => 'Facturación', 'icon' => 'fa-file-invoice-dollar', 'count' => $totalFacturas ?? 0, 'route' => route('facturacion.index')],
                ['title' => 'Inventario', 'icon' => 'fa-boxes', 'count' => $totalPaquetes ?? 0, 'route' => route('inventario.index')],
                ['title' => 'Tracking', 'icon' => 'fa-route', 'count' => null, 'route' => route('tracking.dashboard')],
                ['title' => 'Notificaciones', 'icon' => 'fa-bell', 'count' => null, 'route' => route('notificaciones.index')],
            ];
        @endphp
        @foreach($modules as $mod)
        <a href="{{ $mod['route'] }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md hover:border-[#15537c]/30 flex items-center gap-4">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-[#15537c]/10 text-[#15537c] text-2xl">
                <i class="fas {{ $mod['icon'] }}"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-800">{{ $mod['count'] !== null ? $mod['count'] : '—' }}</p>
                <p class="text-base font-semibold text-[#15537c]">{{ $mod['title'] }}</p>
            </div>
        </a>
        @endforeach
    </div>

    <div class="grid gap-8 lg:grid-cols-3">
        {{-- Columna izquierda: 2/3 --}}
        <div class="lg:col-span-2 space-y-8">
            {{-- Estadísticas por cliente --}}
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-800 border-b border-slate-100 pb-3 mb-4"><i class="fas fa-user-friends mr-2 text-[#15537c]"></i>Estadísticas por Cliente (mes actual)</h2>
                <div class="flex flex-wrap items-end gap-4 mb-6">
                    <div class="min-w-[140px]">
                        <label for="servicioSelect" class="mb-1.5 block text-sm font-medium text-slate-600">Tipo de servicio</label>
                        <select id="servicioSelect" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
                            <option value="todos">Todos</option>
                            <option value="aereo">Aéreo</option>
                            <option value="maritimo">Marítimo</option>
                            <option value="pie_cubico">Pie cúbico</option>
                        </select>
                    </div>
                    <div class="min-w-[200px] flex-1 max-w-xs relative">
                        <label for="clienteAutocomplete" class="mb-1.5 block text-sm font-medium text-slate-600">Buscar cliente</label>
                        <div class="flex gap-2">
                            <input type="text" id="clienteAutocomplete" class="min-w-0 flex-1 rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" placeholder="Nombre del cliente..." autocomplete="off">
                            <button type="button" id="btnTodosClientes" class="shrink-0 rounded-lg border border-[#15537c] bg-[#15537c]/10 px-3 py-2.5 text-sm font-semibold text-[#15537c] hover:bg-[#15537c]/20" title="Ver estadísticas de todos los clientes en el rango">Todos</button>
                        </div>
                        <ul id="autocompleteList" class="absolute left-0 right-0 top-full z-10 mt-1 max-h-48 overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-lg hidden"></ul>
                    </div>
                    <div class="min-w-[200px]">
                        <label for="fechaRango" class="mb-1.5 block text-sm font-medium text-slate-600">Rango de fechas</label>
                        <input type="text" id="fechaRango" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" placeholder="Selecciona rango..." autocomplete="off">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 text-center">
                        <div class="flex justify-center mb-2"><div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#15537c]/10 text-[#15537c]"><i class="fas fa-box"></i></div></div>
                        <p class="text-xs font-semibold uppercase text-slate-500">Total Paquetes</p>
                        <p id="clienteTotalPaquetes" class="text-xl font-bold text-[#15537c]">-</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 text-center">
                        <div class="flex justify-center mb-2"><div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-600"><i class="fas fa-dollar-sign"></i></div></div>
                        <p class="text-xs font-semibold uppercase text-slate-500">Dinero</p>
                        <p id="clienteDinero" class="text-xl font-bold text-emerald-700">-</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 text-center">
                        <div class="flex justify-center mb-2"><div class="flex h-10 w-10 items-center justify-center rounded-full bg-sky-100 text-sky-600"><i class="fas fa-weight-hanging"></i></div></div>
                        <p class="text-xs font-semibold uppercase text-slate-500">Libras</p>
                        <p id="clienteLibras" class="text-xl font-bold text-sky-700">- lb</p>
                    </div>
                </div>
                <div id="noClienteDataMsg" class="mt-4 text-center text-slate-500 py-4 hidden">
                    <i class="fas fa-box-open text-3xl mb-2 block"></i>
                    No hay paquetes para este cliente en el rango seleccionado.
                </div>
                <div id="tablaPorTipoWrap" class="mt-6 hidden">
                    <h3 class="mb-3 text-sm font-semibold text-slate-700"><i class="fas fa-layer-group mr-2 text-[#15537c]"></i>Detalle por tipo (todos los clientes)</h3>
                    <div class="overflow-x-auto rounded-lg border border-slate-200">
                        <table class="w-full border-collapse text-left text-sm text-slate-800">
                            <thead class="bg-slate-100 text-xs font-semibold uppercase text-slate-600">
                                <tr>
                                    <th class="px-4 py-2">Tipo</th>
                                    <th class="px-4 py-2 text-right">Paquetes</th>
                                    <th class="px-4 py-2 text-right">Dinero</th>
                                    <th class="px-4 py-2 text-right">Libras</th>
                                </tr>
                            </thead>
                            <tbody id="tablaPorTipoBody"></tbody>
                        </table>
                    </div>
                    <p id="tablaPorTipoRango" class="mt-2 text-xs text-slate-500"></p>
                </div>
            </div>

            {{-- Últimos paquetes --}}
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-slate-200 bg-white px-5 py-3">
                    <h2 class="text-lg font-semibold text-slate-800"><i class="fas fa-boxes mr-2 text-amber-600"></i>Últimos paquetes registrados</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left text-base text-black">
                        <thead class="border-b border-slate-200 bg-[#15537c] text-white">
                            <tr>
                                <th class="px-4 py-2 font-semibold">Cliente</th>
                                <th class="px-4 py-2 font-semibold text-center">Servicio</th>
                                <th class="px-4 py-2 font-semibold text-center">Guía</th>
                                <th class="px-4 py-2 font-semibold text-center">Tracking</th>
                                <th class="px-4 py-2 font-semibold text-center">Fecha ingreso</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ultimosPaquetes ?? [] as $paq)
                            <tr class="border-b border-slate-100 {{ $loop->iteration % 2 === 0 ? 'bg-slate-50' : 'bg-white' }} hover:bg-slate-100">
                                <td class="px-4 py-1.5 font-medium text-black">{{ $paq->cliente->nombre_completo ?? '—' }}</td>
                                <td class="px-4 py-1.5 text-center">{{ $paq->servicio->tipo_servicio ?? '—' }}</td>
                                <td class="px-4 py-1.5 text-center font-medium">{{ $paq->numero_guia }}</td>
                                <td class="px-4 py-1.5 text-center">{{ $paq->tracking_codigo ?? '—' }}</td>
                                <td class="px-4 py-1.5 text-center whitespace-nowrap">{{ $paq->fecha_ingreso ? \Carbon\Carbon::parse($paq->fecha_ingreso)->format('d/m/Y') : '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-slate-600">No hay paquetes recientes.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Columna derecha: gráfico --}}
        <div class="lg:col-span-1">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-800 border-b border-slate-100 pb-3 mb-4"><i class="fas fa-chart-pie mr-2 text-[#15537c]"></i>Distribución por tipo (mes actual)</h2>
                <div class="flex justify-center">
                    <canvas id="graficoServiciosPie" height="220" class="max-w-full"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script>
(function() {
var clientesData = @json($clientesData ?? []);
var clientesList = @json($clientes ?? []);
var serviciosPieData = @json($serviciosPieData ?? []);
var skylinkPalette = ['#15537c','#2d6a9a','#1e5a82','#3B5998','#6A82FB','#A1C4FD','#27408B','#4F8EF7'];
var input = document.getElementById('clienteAutocomplete');
var list = document.getElementById('autocompleteList');
var servicioSelect = document.getElementById('servicioSelect');
var btnTodosClientes = document.getElementById('btnTodosClientes');
var selectedClienteId = null;
var fechaDesde = null, fechaHasta = null;

function formatLocalYMD(d) {
    var y = d.getFullYear();
    var m = String(d.getMonth() + 1).padStart(2, '0');
    var day = String(d.getDate()).padStart(2, '0');
    return y + '-' + m + '-' + day;
}
function getDateRangeOrDefaultMonth() {
    if (fechaDesde && fechaHasta) return { desde: fechaDesde, hasta: fechaHasta };
    var now = new Date();
    var first = new Date(now.getFullYear(), now.getMonth(), 1);
    var last = new Date(now.getFullYear(), now.getMonth() + 1, 0);
    return { desde: formatLocalYMD(first), hasta: formatLocalYMD(last) };
}
function setTodosModeActive(active) {
    if (!btnTodosClientes) return;
    if (active) {
        btnTodosClientes.classList.add('ring-2', 'ring-[#15537c]', 'bg-[#15537c]/25');
    } else {
        btnTodosClientes.classList.remove('ring-2', 'ring-[#15537c]', 'bg-[#15537c]/25');
    }
}
function hidePorTipoTable() {
    var wrap = document.getElementById('tablaPorTipoWrap');
    if (wrap) wrap.classList.add('hidden');
}
function renderPorTipoTable(porTipo, desde, hasta) {
    var wrap = document.getElementById('tablaPorTipoWrap');
    var body = document.getElementById('tablaPorTipoBody');
    var rangoEl = document.getElementById('tablaPorTipoRango');
    if (!wrap || !body) return;
    if (!porTipo) { wrap.classList.add('hidden'); return; }
    var rows = [
        { key: 'maritimo', label: 'Marítimo' },
        { key: 'aereo', label: 'Aéreo' },
        { key: 'pie_cubico', label: 'Pie cúbico' }
    ];
    body.innerHTML = '';
    rows.forEach(function(r) {
        var d = porTipo[r.key] || { paquetes: 0, dinero: 0, libras: 0 };
        var tr = document.createElement('tr');
        tr.className = 'border-b border-slate-100 last:border-0';
        tr.innerHTML = '<td class="px-4 py-2 font-medium">' + r.label + '</td>' +
            '<td class="px-4 py-2 text-right">' + (d.paquetes || 0) + '</td>' +
            '<td class="px-4 py-2 text-right">' + parseFloat(d.dinero || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
            '<td class="px-4 py-2 text-right">' + parseFloat(d.libras || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' lb</td>';
        body.appendChild(tr);
    });
    wrap.classList.remove('hidden');
    if (rangoEl && desde && hasta) {
        rangoEl.textContent = 'Período: ' + desde + ' — ' + hasta;
    }
}

var idsCliente = {
    total: document.getElementById('clienteTotalPaquetes'),
    dinero: document.getElementById('clienteDinero'),
    libras: document.getElementById('clienteLibras'),
};

function setClienteStats(data, servicio) {
    hidePorTipoTable();
    if (!data) {
        if (idsCliente.total) idsCliente.total.textContent = '-';
        if (idsCliente.dinero) idsCliente.dinero.textContent = '-';
        if (idsCliente.libras) idsCliente.libras.textContent = '-';
        var noMsg = document.getElementById('noClienteDataMsg');
        if (noMsg) noMsg.classList.remove('hidden');
        return;
    }
    var total = 0, dinero = 0, libras = 0;
    if (servicio === 'todos') {
        total = (data.paquetes_aereo||0)+(data.paquetes_maritimo||0)+(data.paquetes_pie_cubico||0);
        dinero = (data.ingresos_aereo||0)+(data.ingresos_maritimo||0)+(data.ingresos_pie_cubico||0);
        libras = (data.libras_aereo||0)+(data.libras_maritimo||0)+(data.libras_pie_cubico||0);
    } else if (servicio === 'aereo') { total = data.paquetes_aereo||0; dinero = data.ingresos_aereo||0; libras = data.libras_aereo||0; }
    else if (servicio === 'maritimo') { total = data.paquetes_maritimo||0; dinero = data.ingresos_maritimo||0; libras = data.libras_maritimo||0; }
    else if (servicio === 'pie_cubico') { total = data.paquetes_pie_cubico||0; dinero = data.ingresos_pie_cubico||0; libras = data.libras_pie_cubico||0; }
    if (idsCliente.total) idsCliente.total.textContent = total;
    if (idsCliente.dinero) idsCliente.dinero.textContent = typeof dinero === 'number' ? dinero.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) : '-';
    if (idsCliente.libras) idsCliente.libras.textContent = (typeof libras === 'number' ? libras.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) : '-') + ' lb';
    var noMsg = document.getElementById('noClienteDataMsg');
    if (noMsg) noMsg.classList.toggle('hidden', total > 0);
}

function showSuggestions(term) {
    if (!list) return;
    list.innerHTML = '';
    var liTodos = document.createElement('li');
    liTodos.className = 'cursor-pointer px-4 py-2 hover:bg-[#15537c]/10 border-b border-slate-200 font-semibold text-[#15537c]';
    liTodos.textContent = 'Todos los clientes';
    liTodos.onclick = function() {
        input.value = 'Todos los clientes';
        selectedClienteId = 'todos';
        list.classList.add('hidden');
        setTodosModeActive(true);
        fetchClienteStats();
    };
    list.appendChild(liTodos);
    var filtered = clientesList.filter(function(c){ return (c.nombre_completo||'').toLowerCase().indexOf((term||'').toLowerCase()) !== -1; });
    if (filtered.length === 0) { list.classList.remove('hidden'); return; }
    filtered.forEach(function(cliente) {
        var li = document.createElement('li');
        li.className = 'cursor-pointer px-4 py-2 hover:bg-slate-100 border-b border-slate-100 last:border-0';
        li.textContent = cliente.nombre_completo;
        li.onclick = function() {
            input.value = cliente.nombre_completo;
            selectedClienteId = cliente.id;
            setTodosModeActive(false);
            list.classList.add('hidden');
            renderClienteStats(cliente.id);
        };
        list.appendChild(li);
    });
    list.classList.remove('hidden');
}

if (btnTodosClientes) {
    btnTodosClientes.addEventListener('click', function() {
        if (input) input.value = 'Todos los clientes';
        selectedClienteId = 'todos';
        setTodosModeActive(true);
        if (list) list.classList.add('hidden');
        fetchClienteStats();
    });
}
if (input) {
    input.addEventListener('input', function() {
        var term = this.value.trim();
        if (!term) {
            list && list.classList.add('hidden');
            selectedClienteId = null;
            setTodosModeActive(false);
            hidePorTipoTable();
            setClienteStats(null, servicioSelect ? servicioSelect.value : 'todos');
            return;
        }
        if (term.toLowerCase() !== 'todos los clientes') {
            setTodosModeActive(false);
            if (selectedClienteId === 'todos') {
                selectedClienteId = null;
                hidePorTipoTable();
                setClienteStats(null, servicioSelect ? servicioSelect.value : 'todos');
            }
        }
        showSuggestions(term);
    });
    input.addEventListener('focus', function() { if (this.value.trim()) showSuggestions(this.value.trim()); });
}
document.addEventListener('click', function(e) {
    if (list && !list.contains(e.target) && e.target !== input) list.classList.add('hidden');
});

function renderClienteStats(clienteId) {
    var data = clientesData[clienteId] || null;
    setClienteStats(data, servicioSelect ? servicioSelect.value : 'todos');
}

if (servicioSelect) servicioSelect.addEventListener('change', function() {
    if (selectedClienteId === 'todos') {
        fetchClienteStats();
        return;
    }
    if (selectedClienteId) {
        if (!fechaDesde && !fechaHasta) renderClienteStats(selectedClienteId);
        else fetchClienteStats();
    }
});

function setClienteStatsAjax(data) {
    if (!data) {
        hidePorTipoTable();
        if (idsCliente.total) idsCliente.total.textContent = '-';
        if (idsCliente.dinero) idsCliente.dinero.textContent = '-';
        if (idsCliente.libras) idsCliente.libras.textContent = '-';
        var noMsg = document.getElementById('noClienteDataMsg');
        if (noMsg) { noMsg.classList.remove('hidden'); }
        return;
    }
    if (idsCliente.total) idsCliente.total.textContent = data.total ?? '-';
    if (idsCliente.dinero) idsCliente.dinero.textContent = data.dinero !== undefined ? parseFloat(data.dinero).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) : '-';
    if (idsCliente.libras) idsCliente.libras.textContent = (data.libras !== undefined ? parseFloat(data.libras).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) : '-') + ' lb';
    var noMsg = document.getElementById('noClienteDataMsg');
    if (noMsg) noMsg.classList.toggle('hidden', (data.total||0) > 0);
    if (data.todos_clientes && data.por_tipo) {
        renderPorTipoTable(data.por_tipo, data.desde, data.hasta);
    } else {
        hidePorTipoTable();
    }
}

function fetchClienteStats() {
    if (!selectedClienteId) { setClienteStatsAjax(null); return; }
    if (idsCliente.total) idsCliente.total.textContent = '...';
    if (idsCliente.dinero) idsCliente.dinero.textContent = '...';
    if (idsCliente.libras) idsCliente.libras.textContent = '...';
    var tipo = servicioSelect ? servicioSelect.value : 'todos';
    var url = '/dashboard/estadisticas-paquetes-cliente?tipo_servicio=' + encodeURIComponent(tipo);
    if (selectedClienteId === 'todos') {
        var dr = getDateRangeOrDefaultMonth();
        url += '&cliente_id=todos&desde=' + encodeURIComponent(dr.desde) + '&hasta=' + encodeURIComponent(dr.hasta);
    } else {
        url += '&cliente_id=' + encodeURIComponent(selectedClienteId);
        if (fechaDesde) url += '&desde=' + encodeURIComponent(fechaDesde);
        if (fechaHasta) url += '&hasta=' + encodeURIComponent(fechaHasta);
    }
    fetch(url, { headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}, credentials: 'same-origin' })
        .then(function(r){ return r.ok ? r.json() : Promise.reject(); })
        .then(setClienteStatsAjax)
        .catch(function(){ setClienteStatsAjax(null); });
}

if (typeof flatpickr !== 'undefined') {
    flatpickr('#fechaRango', {
        mode: 'range',
        dateFormat: 'Y-m-d',
        locale: 'es',
        onChange: function(selectedDates) {
            if (selectedDates.length === 2) {
                fechaDesde = selectedDates[0].toISOString().slice(0,10);
                fechaHasta = selectedDates[1].toISOString().slice(0,10);
            } else { fechaDesde = null; fechaHasta = null; }
            fetchClienteStats();
        }
    });
}

var ctxPie = document.getElementById('graficoServiciosPie');
if (ctxPie && typeof Chart !== 'undefined') {
    var pieLabels = Object.keys(serviciosPieData);
    var pieData = Object.values(serviciosPieData);
    var pieColors = skylinkPalette.slice(0, Math.max(pieLabels.length, 1));
    new Chart(ctxPie.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: pieLabels,
            datasets: [{ data: pieData, backgroundColor: pieColors, borderColor: '#fff', borderWidth: 2 }]
        },
        options: {
            cutout: '60%',
            plugins: {
                legend: { display: true, position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            var total = ctx.dataset.data.reduce(function(a,b){ return a+b; }, 0);
                            var pct = total ? ((ctx.parsed/total)*100).toFixed(1) : 0;
                            return ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                        }
                    }
                }
            }
        }
    });
}
})();
</script>
@endpush
@endsection
