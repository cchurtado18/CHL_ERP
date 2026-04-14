@extends('layouts.app-new')

@section('title', 'Nuevo Paquete - CH LOGISTICS ERP')
@section('navbar-title', 'Nuevo Paquete')

@section('content')
<div class="mx-auto w-full max-w-7xl px-4 sm:px-6 space-y-8">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Registrar Nuevo Paquete</h1>
            <p class="mt-1 text-slate-600">Completa la información del paquete para agregarlo al inventario</p>
        </div>
        <a href="{{ route('inventario.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-5 py-2.5 text-base font-medium text-slate-600 hover:bg-slate-50">
            <i class="fas fa-arrow-left"></i> Volver al Inventario
        </a>
    </div>

    <div class="grid gap-8 lg:grid-cols-[1fr_320px]">
        {{-- Formulario --}}
        <div class="min-w-0">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
                <h2 class="text-lg font-semibold text-slate-800 border-b border-slate-100 pb-3 mb-6">
                    <i class="fas fa-box text-[#15537c] mr-2"></i>Información del Paquete
                </h2>
                <form action="{{ route('inventario.store') }}" method="POST" id="inventarioForm">
                    @csrf
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="clienteAutocomplete" class="mb-1.5 block text-sm font-medium text-slate-600">Buscar cliente <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="text" id="clienteAutocomplete" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" placeholder="Escribe el nombre del cliente..." autocomplete="off" required value="{{ old('cliente_nombre') }}">
                                <input type="hidden" name="cliente_id" id="cliente_id" value="{{ old('cliente_id') }}">
                                <ul id="autocompleteList" class="absolute left-0 right-0 top-full z-10 mt-1 max-h-48 overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-lg hidden"></ul>
                            </div>
                            @error('cliente_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="servicio_id" class="mb-1.5 block text-sm font-medium text-slate-600">Servicio <span class="text-red-500">*</span></label>
                            <select name="servicio_id" id="servicio_id" required class="w-full appearance-none rounded-lg border border-slate-300 bg-white bg-[length:1.25rem] bg-[right_0.75rem_center] bg-no-repeat px-4 py-2.5 pr-10 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%2364758b%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 9l-7 7-7-7%22/%3E%3C/svg%3E');">
                                <option value="">Seleccione un servicio</option>
                                @foreach($servicios as $servicio)
                                    <option value="{{ $servicio->id }}" {{ old('servicio_id') == $servicio->id ? 'selected' : '' }}>{{ $servicio->tipo_servicio }}</option>
                                @endforeach
                            </select>
                            @error('servicio_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="peso_lb" class="mb-1.5 block text-sm font-medium text-slate-600">Peso (lb) <span class="text-red-500">*</span></label>
                            <div class="flex rounded-lg border border-slate-300 focus-within:ring-1 focus-within:ring-[#15537c] focus-within:border-[#15537c]">
                                <span class="flex items-center rounded-l-lg border-r border-slate-300 bg-slate-50 px-3 text-slate-600">lb</span>
                                <input type="number" step="0.01" name="peso_lb" id="peso_lb" value="{{ old('peso_lb') }}" required placeholder="0.00" class="min-w-0 flex-1 rounded-r-lg border-0 px-4 py-2.5 text-base">
                            </div>
                            @error('peso_lb')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="tracking_codigo" class="mb-1.5 block text-sm font-medium text-slate-600">Código de Tracking</label>
                            <input type="text" name="tracking_codigo" value="{{ old('tracking_codigo') }}" placeholder="Código de tracking" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
                            @error('tracking_codigo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="numero_guia" class="mb-1.5 block text-sm font-medium text-slate-600">Número de Guía <span class="text-red-500">*</span></label>
                            <input type="text" name="numero_guia" id="numero_guia" value="{{ old('numero_guia') }}" required maxlength="9" minlength="6" placeholder="Ej: 1223113/1 o 12345678" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
                            <p id="guia-error" class="mt-1 text-sm text-red-600 hidden">El número de guía debe tener 6-9 caracteres o formato con barra (ej: 1223113/1)</p>
                            @error('numero_guia')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-600">Estado</label>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-600">Recibido (entrada)</div>
                            <input type="hidden" name="estado" value="recibido">
                        </div>

                        <div>
                            <label for="tarifa_manual" class="mb-1.5 block text-sm font-medium text-slate-600">Tarifa manual (opcional)</label>
                            <div class="flex rounded-lg border border-slate-300 focus-within:ring-1 focus-within:ring-[#15537c] focus-within:border-[#15537c]">
                                <span class="flex items-center rounded-l-lg border-r border-slate-300 bg-slate-50 px-3 text-slate-600">$</span>
                                <input type="number" step="0.01" name="tarifa_manual" id="tarifa_manual" value="{{ old('tarifa_manual') }}" placeholder="0.00" class="min-w-0 flex-1 rounded-r-lg border-0 px-4 py-2.5 text-base">
                                <span id="tarifa-loading" class="flex items-center pr-3 text-slate-400 hidden"><i class="fas fa-spinner fa-spin"></i></span>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">Dejar vacío para calcular automáticamente</p>
                            <p id="tarifa-info" class="mt-1 text-xs text-emerald-600 hidden"><i class="fas fa-check-circle mr-1"></i>Tarifa automática aplicada</p>
                            @error('tarifa_manual')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="monto_calculado" class="mb-1.5 block text-sm font-medium text-slate-600">Monto calculado ($) <span class="text-red-500">*</span></label>
                            <div class="flex rounded-lg border border-slate-300 focus-within:ring-1 focus-within:ring-[#15537c] focus-within:border-[#15537c]">
                                <span class="flex items-center rounded-l-lg border-r border-slate-300 bg-slate-50 px-3 text-slate-600">$</span>
                                <input type="number" step="0.01" name="monto_calculado" id="monto_calculado" value="{{ old('monto_calculado') }}" required placeholder="0.00" class="min-w-0 flex-1 rounded-r-lg border-0 px-4 py-2.5 text-base">
                            </div>
                            @error('monto_calculado')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="notas" class="mb-1.5 block text-sm font-medium text-slate-600">Notas adicionales</label>
                            <textarea name="notas" id="notas" rows="3" placeholder="Información adicional sobre el paquete..." class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">{{ old('notas') }}</textarea>
                            @error('notas')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-6">
                        <a href="{{ route('inventario.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-5 py-2.5 text-base font-medium text-slate-600 hover:bg-slate-50">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" id="btnSubmit" class="inline-flex items-center gap-2 rounded-xl bg-[#15537c] px-5 py-2.5 text-base font-semibold text-white shadow-sm hover:bg-[#0f3d5c] disabled:opacity-60 disabled:cursor-not-allowed">
                            <i class="fas fa-save"></i> Guardar Paquete
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-base font-semibold text-slate-800 border-b border-slate-100 pb-2 mb-3">
                    <i class="fas fa-info-circle text-[#15537c] mr-2"></i>Información útil
                </h3>
                <div class="space-y-3 text-sm text-slate-600">
                    <p class="font-semibold text-slate-700"><i class="fas fa-lightbulb text-amber-500 mr-1"></i>Consejos</p>
                    <ul class="list-disc list-inside space-y-1 text-slate-600">
                        <li>Verifica el peso y volumen</li>
                        <li>El número de guía es recomendado</li>
                        <li>Las notas ayudan a identificar el paquete</li>
                        <li>El estado se puede actualizar después</li>
                    </ul>
                    <p class="font-semibold text-slate-700 pt-2"><i class="fas fa-calculator text-[#15537c] mr-1"></i>Cálculo</p>
                    <p class="text-slate-600">La tarifa se calcula según peso, volumen y tipo de servicio.</p>
                </div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-base font-semibold text-slate-800 border-b border-slate-100 pb-2 mb-3">
                    <i class="fas fa-clock text-emerald-600 mr-2"></i>Estados
                </h3>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="rounded-full bg-amber-200 px-2 py-0.5 text-xs font-semibold text-amber-900">Recibido</span>
                        <span class="text-slate-600">Paquete en almacén</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="rounded-full bg-emerald-200 px-2 py-0.5 text-xs font-semibold text-emerald-900">Entregado</span>
                        <span class="text-slate-600">Completado</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var clientesList = @json($clientes ?? []);
    var input = document.getElementById('clienteAutocomplete');
    var list = document.getElementById('autocompleteList');
    var hiddenInput = document.getElementById('cliente_id');

    function showSuggestions(term) {
        if (!list) return;
        list.innerHTML = '';
        var filtered = clientesList.filter(function(c) { return (c.nombre_completo || '').toLowerCase().indexOf((term || '').toLowerCase()) !== -1; });
        if (filtered.length === 0) { list.classList.add('hidden'); return; }
        filtered.forEach(function(cliente) {
            var li = document.createElement('li');
            li.className = 'cursor-pointer px-4 py-2.5 hover:bg-slate-100 border-b border-slate-100 last:border-0 text-slate-800';
            li.textContent = cliente.nombre_completo;
            li.onclick = function() {
                input.value = cliente.nombre_completo;
                hiddenInput.value = cliente.id;
                list.classList.add('hidden');
                if (typeof obtenerTarifaCliente === 'function') setTimeout(obtenerTarifaCliente, 100);
                if (typeof calculateMonto === 'function') setTimeout(calculateMonto, 100);
            };
            list.appendChild(li);
        });
        list.classList.remove('hidden');
    }
    if (input) {
        input.addEventListener('input', function() {
            var term = this.value.trim();
            if (!term) { list.classList.add('hidden'); hiddenInput.value = ''; return; }
            showSuggestions(term);
        });
        input.addEventListener('focus', function() { if (this.value.trim()) showSuggestions(this.value.trim()); });
    }
    document.addEventListener('click', function(e) {
        if (list && !list.contains(e.target) && e.target !== input) list.classList.add('hidden');
    });

    var pesoInput = document.getElementById('peso_lb');
    var servicioSelect = document.getElementById('servicio_id');
    var montoInput = document.getElementById('monto_calculado');
    var tarifaManualInput = document.getElementById('tarifa_manual');

    window.calculateMonto = function() {
        if (!pesoInput || !montoInput) return;
        var peso = parseFloat(pesoInput.value) || 0;
        var rate = parseFloat(tarifaManualInput && tarifaManualInput.value ? tarifaManualInput.value : 0) || 1;
        montoInput.value = (peso * rate).toFixed(2);
    };

    window.obtenerTarifaCliente = function() {
        var clienteId = hiddenInput && hiddenInput.value;
        var servicioId = servicioSelect && servicioSelect.value;
        var tarifaLoading = document.getElementById('tarifa-loading');
        var tarifaInfo = document.getElementById('tarifa-info');
        if (tarifaInfo) tarifaInfo.classList.add('hidden');
        if (!clienteId || !servicioId) {
            if (tarifaManualInput) tarifaManualInput.value = '';
            calculateMonto();
            return;
        }
        if (tarifaLoading) tarifaLoading.classList.remove('hidden');
        fetch("{{ route('inventario.obtener-tarifa') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value },
            body: JSON.stringify({ cliente_id: clienteId, servicio_id: servicioId })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (tarifaLoading) tarifaLoading.classList.add('hidden');
            if (data.tarifa != null) {
                if (tarifaManualInput) tarifaManualInput.value = data.tarifa;
                if (tarifaInfo) tarifaInfo.classList.remove('hidden');
            } else if (tarifaManualInput) tarifaManualInput.value = '';
            calculateMonto();
        })
        .catch(function() {
            if (tarifaLoading) tarifaLoading.classList.add('hidden');
            if (tarifaManualInput) tarifaManualInput.value = '';
            calculateMonto();
        });
    };

    if (pesoInput) pesoInput.addEventListener('input', calculateMonto);
    if (servicioSelect) servicioSelect.addEventListener('change', function() { obtenerTarifaCliente(); calculateMonto(); });
    if (hiddenInput) hiddenInput.addEventListener('change', function() { obtenerTarifaCliente(); calculateMonto(); });
    if (tarifaManualInput) tarifaManualInput.addEventListener('input', calculateMonto);

    var form = document.getElementById('inventarioForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            var required = form.querySelectorAll('[required]');
            var valid = true;
            required.forEach(function(f) {
                if (!f.value.trim()) { valid = false; f.classList.add('border-red-500'); } else { f.classList.remove('border-red-500'); }
            });
            var numeroGuia = document.getElementById('numero_guia');
            var guiaError = document.getElementById('guia-error');
            if (numeroGuia && guiaError) {
                var val = numeroGuia.value.trim();
                var ok = /^(\d{6,9}|\d+\/\d+)$/.test(val);
                if (!ok && val) {
                    guiaError.textContent = 'El número de guía debe tener 6-9 caracteres o formato con barra (ej: 1223113/1)';
                    guiaError.classList.remove('hidden');
                    e.preventDefault();
                    return;
                }
                guiaError.classList.add('hidden');
            }
            if (!valid) { e.preventDefault(); alert('Completa todos los campos requeridos.'); }
        });
    }

    var numeroGuiaInput = document.getElementById('numero_guia');
    var guiaError = document.getElementById('guia-error');
    if (numeroGuiaInput && guiaError) {
        var btnSubmit = document.getElementById('btnSubmit');
        numeroGuiaInput.addEventListener('blur', function() {
            var valor = numeroGuiaInput.value.trim();
            if (!valor) { if (btnSubmit) btnSubmit.disabled = false; return; }
            fetch("{{ route('inventario.validar-numero-guia') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value },
                body: JSON.stringify({ numero_guia: valor })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.exists) {
                    guiaError.textContent = data.message || 'Esta guía ya está registrada.';
                    guiaError.classList.remove('hidden');
                    if (btnSubmit) btnSubmit.disabled = true;
                } else {
                    guiaError.classList.add('hidden');
                    if (btnSubmit) btnSubmit.disabled = false;
                }
            });
        });
        numeroGuiaInput.addEventListener('input', function() {
            guiaError.classList.add('hidden');
            if (btnSubmit) btnSubmit.disabled = false;
        });
    }
})();
</script>
@endpush
@endsection
