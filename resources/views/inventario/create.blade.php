@extends('layouts.app-new')

@section('title', 'Nuevo Paquete - CH Logistics')
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

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-base text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-base text-red-800">
            <ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="grid gap-8 lg:grid-cols-[1fr_320px]">
        {{-- Formulario --}}
        <div class="min-w-0">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
                <h2 class="text-lg font-semibold text-slate-800 border-b border-slate-100 pb-3 mb-6">
                    <i class="fas fa-box text-[#15537c] mr-2"></i>Información del Paquete
                </h2>
                <form action="{{ route('inventario.store') }}" method="POST" id="inventarioForm">
                    @csrf
                    @if(($proveedores ?? collect())->isNotEmpty())
                    <div class="mb-6 rounded-xl border border-[#15537c]/20 bg-[#15537c]/5 p-4">
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-[#15537c]"><i class="fas fa-file-invoice mr-1"></i> Factura del proveedor</p>
                            <div class="flex items-center gap-3">
                                @if(auth()->user()?->tienePermiso('contabilidad'))
                                    <a href="{{ route('contabilidad.cxp.create') }}" class="text-xs font-medium text-[#15537c] hover:underline">Nueva factura CxP</a>
                                @endif
                                <a href="{{ route('inventario.create', ['cxp_reset' => 1]) }}" class="text-xs font-medium text-slate-500 hover:text-[#15537c]">Limpiar</a>
                            </div>
                        </div>
                        <p class="mb-3 text-xs text-slate-600">Elige una factura abierta. Puedes ir asignando paquetes en varios días; el total asignado se compara con el monto de la factura.</p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label for="proveedor_id" class="mb-1 block text-sm font-medium text-slate-600">Proveedor</label>
                                <select name="proveedor_id" id="proveedor_id" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-base">
                                    <option value="">Todos / sin proveedor</option>
                                    @foreach($proveedores as $p)
                                        <option value="{{ $p->id }}" @selected((string)($cxpPrefill['proveedor_id'] ?? '') === (string)$p->id)>{{ $p->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="cxp_id" class="mb-1 block text-sm font-medium text-slate-600">Número de factura</label>
                                <select name="cxp_id" id="cxp_id" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-base">
                                    <option value="">— Sin factura —</option>
                                    @foreach($facturasCxp ?? [] as $f)
                                        <option value="{{ $f->id }}" data-proveedor="{{ $f->proveedor_id }}" @selected((string)($cxpPrefill['cxp_id'] ?? '') === (string)$f->id)>
                                            {{ $f->etiquetaDocumento() }} · {{ $f->proveedor->nombre ?? 'Proveedor' }} · factura ${{ number_format((float)$f->monto_original, 2) }} · asignado ${{ number_format($f->montoPaquetesAsignados(), 2) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <p id="cxpFacturaResumen" class="mt-2 hidden text-sm text-slate-700"></p>
                        @if(($facturasCxp ?? collect())->isEmpty())
                            <p class="mt-2 text-sm text-amber-800">No hay facturas de proveedor abiertas. Regístrala en CxP con su monto y luego elige el número aquí.</p>
                        @endif
                    </div>
                    @endif
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
                                <input type="number" step="any" min="0" inputmode="decimal" name="peso_lb" id="peso_lb" value="{{ old('peso_lb') }}" required placeholder="0.00" class="min-w-0 flex-1 rounded-r-lg border-0 px-4 py-2.5 text-base">
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
                        <li>Si hay factura de proveedor, elige el número: se queda para los siguientes paquetes</li>
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
    var facturasCxp = @json($facturasCxpJs ?? []);
    var proveedorSelect = document.getElementById('proveedor_id');
    var cxpSelect = document.getElementById('cxp_id');
    var cxpResumen = document.getElementById('cxpFacturaResumen');

    function money(n) {
        return '$' + (Number(n || 0).toFixed(2));
    }
    function facturaPorId(id) {
        return facturasCxp.find(function(f) { return String(f.id) === String(id); });
    }
    function pintarResumen() {
        if (!cxpResumen || !cxpSelect) return;
        var f = facturaPorId(cxpSelect.value);
        if (!f) { cxpResumen.classList.add('hidden'); cxpResumen.textContent = ''; return; }
        var falta = Math.round((f.monto - f.asignado) * 100) / 100;
        var extra = falta > 0.009 ? (' Faltan ' + money(falta) + ' por asignar.') : (falta < -0.009 ? (' Asignado supera la factura por ' + money(Math.abs(falta)) + '.') : ' Completa.');
        cxpResumen.textContent = f.numero + ' · Factura ' + money(f.monto) + ' · Paquetes asignados ' + money(f.asignado) + '.' + extra;
        cxpResumen.classList.remove('hidden');
    }
    function filtrarFacturas() {
        if (!cxpSelect) return;
        var prov = proveedorSelect ? proveedorSelect.value : '';
        var actual = cxpSelect.value;
        Array.prototype.forEach.call(cxpSelect.options, function(opt) {
            if (!opt.value) { opt.hidden = false; return; }
            var pid = opt.getAttribute('data-proveedor') || '';
            opt.hidden = !!(prov && String(pid) !== String(prov));
        });
        var selected = cxpSelect.options[cxpSelect.selectedIndex];
        if (selected && selected.hidden) {
            cxpSelect.value = '';
        } else if (actual) {
            cxpSelect.value = actual;
        }
        pintarResumen();
    }
    if (proveedorSelect) proveedorSelect.addEventListener('change', filtrarFacturas);
    if (cxpSelect) cxpSelect.addEventListener('change', function() {
        var f = facturaPorId(this.value);
        if (f && proveedorSelect && !proveedorSelect.value) {
            proveedorSelect.value = f.proveedor_id;
            filtrarFacturas();
            cxpSelect.value = f.id;
        }
        pintarResumen();
    });
    filtrarFacturas();
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
        var rawRate = tarifaManualInput && tarifaManualInput.value !== '' ? parseFloat(tarifaManualInput.value) : NaN;
        var rate = Number.isFinite(rawRate) ? rawRate : 1;
        montoInput.value = (Math.round((peso * rate) * 100) / 100).toFixed(2);
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
