@extends('layouts.app-new')

@section('title', 'Registrar Cliente - CH LOGISTICS ERP')
@section('navbar-title', 'Nuevo Cliente')

@section('content')
<div class="mx-auto w-full max-w-7xl px-4 sm:px-6 space-y-8">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Registrar Cliente</h1>
            <p class="mt-1 text-slate-600">Completa los datos para registrar un nuevo cliente</p>
        </div>
        <a href="{{ route('clientes.index') }}" class="inline-flex items-center gap-2 rounded-lg border-2 border-[#15537c] bg-white px-5 py-2.5 text-base font-semibold text-[#15537c] shadow-sm hover:bg-[#15537c]/5">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    {{-- Formulario --}}
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
        <form id="cliente-form" method="POST" action="{{ route('clientes.store') }}" class="flex flex-wrap gap-0">
            @csrf
            {{-- Columna datos del cliente --}}
            <div class="w-full lg:w-1/2 lg:pr-8 lg:border-r border-slate-200 space-y-4 min-w-0">
                <div>
                    <label for="nombre_completo" class="mb-1.5 block text-sm font-medium text-slate-600">Nombre completo <span class="text-red-500">*</span></label>
                    <input type="text" id="nombre_completo" name="nombre_completo" required class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" placeholder="Nombre completo">
                </div>
                <div>
                    <label for="correo" class="mb-1.5 block text-sm font-medium text-slate-600">Correo</label>
                    <input type="email" id="correo" name="correo" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" placeholder="correo@ejemplo.com">
                </div>
                <div>
                    <label for="telefono" class="mb-1.5 block text-sm font-medium text-slate-600">Teléfono</label>
                    <input type="text" id="telefono" name="telefono" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" placeholder="Teléfono">
                </div>
                <div>
                    <label for="direccion" class="mb-1.5 block text-sm font-medium text-slate-600">Dirección</label>
                    <textarea id="direccion" name="direccion" rows="2" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" placeholder="Dirección"></textarea>
                </div>
                <div>
                    <label for="tipo_cliente" class="mb-1.5 block text-sm font-medium text-slate-600">Tipo de cliente</label>
                    <select id="tipo_cliente" name="tipo_cliente" class="w-full appearance-none rounded-lg border border-slate-300 bg-white bg-[length:1.25rem] bg-[right_0.75rem_center] bg-no-repeat px-4 py-2.5 pr-10 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]" style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%2364758b%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 9l-7 7-7-7%22/%3E%3C/svg%3E');">
                        <option value="Normal">Normal</option>
                        <option value="Subagencia">Subagencia</option>
                    </select>
                </div>
            </div>
            {{-- Columna tarifas --}}
            <div class="w-full lg:w-1/2 lg:pl-8 pt-6 lg:pt-0 space-y-4 min-w-0">
                <h2 class="text-lg font-semibold text-slate-800 border-b border-slate-100 pb-2">
                    <i class="fas fa-dollar-sign text-[#15537c] mr-2"></i>Tarifas por servicio
                </h2>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#15537c]/10 text-[#15537c]">
                        <i class="fas fa-plane"></i>
                    </div>
                    <label for="tarifa_aereo" class="flex-1 text-sm font-medium text-slate-600">Aéreo <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0" id="tarifa_aereo" name="tarifa_aereo" required placeholder="0.00" class="w-28 rounded-lg border border-slate-300 px-3 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#15537c]/10 text-[#15537c]">
                        <i class="fas fa-ship"></i>
                    </div>
                    <label for="tarifa_maritimo" class="flex-1 text-sm font-medium text-slate-600">Marítimo <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0" id="tarifa_maritimo" name="tarifa_maritimo" required placeholder="0.00" class="w-28 rounded-lg border border-slate-300 px-3 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#15537c]/10 text-[#15537c]">
                        <i class="fas fa-cube"></i>
                    </div>
                    <label for="tarifa_pie_cubico" class="flex-1 text-sm font-medium text-slate-600">Pie cúbico</label>
                    <input type="number" step="0.01" min="0" id="tarifa_pie_cubico" name="tarifa_pie_cubico" placeholder="0.00" class="w-28 rounded-lg border border-slate-300 px-3 py-2.5 text-base focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]">
                </div>
                <div class="pt-4">
                    <button type="button" id="btn_finalizar_registro" disabled class="w-full rounded-xl bg-[#15537c] px-5 py-3 text-base font-semibold text-white shadow-sm hover:bg-[#0f3d5c] disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-check mr-2"></i>Registrar Cliente
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal éxito / error --}}
<div id="modalMensaje" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-hidden="true">
    <div class="flex min-h-full items-center justify-center p-6">
        <div class="fixed inset-0 bg-slate-900/50" onclick="cerrarModalMensaje()"></div>
        <div class="relative w-full max-w-sm rounded-xl bg-white p-8 shadow-xl text-center">
            <div id="modalMensajeIcon" class="mb-3 text-4xl text-[#15537c]"><i class="fas fa-check-circle"></i></div>
            <p id="modalMensajeTexto" class="text-base font-semibold text-slate-800"></p>
            <button type="button" onclick="cerrarModalMensaje()" class="mt-6 rounded-lg bg-[#15537c] px-5 py-2.5 text-base font-medium text-white hover:bg-[#0f3d5c]">Cerrar</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    function checkCamposObligatorios() {
        var form = document.getElementById('cliente-form');
        var valid = true;
        form.querySelectorAll('[required]').forEach(function(input) {
            if (!input.value.trim()) valid = false;
        });
        var btn = document.getElementById('btn_finalizar_registro');
        if (btn) btn.disabled = !valid;
    }

    document.querySelectorAll('#cliente-form [required]').forEach(function(inp) {
        inp.addEventListener('input', checkCamposObligatorios);
    });
    checkCamposObligatorios();

    function mostrarModalMensaje(mensaje, tipo) {
        var modal = document.getElementById('modalMensaje');
        var icon = document.getElementById('modalMensajeIcon');
        var texto = document.getElementById('modalMensajeTexto');
        if (!modal || !texto) return;
        texto.textContent = mensaje;
        if (icon) {
            icon.className = 'mb-3 text-4xl ' + (tipo === 'success' ? 'text-emerald-600' : 'text-red-600');
            icon.innerHTML = tipo === 'success' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-times-circle"></i>';
        }
        modal.classList.remove('hidden');
    }

    window.cerrarModalMensaje = function() {
        var modal = document.getElementById('modalMensaje');
        if (modal) modal.classList.add('hidden');
    };

    document.getElementById('btn_finalizar_registro').addEventListener('click', function() {
        var form = document.getElementById('cliente-form');
        var formData = new FormData(form);
        fetch(form.action || '/clientes', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.id) {
                mostrarModalMensaje('Cliente y tarifas registrados correctamente.', 'success');
                setTimeout(function() { window.location.href = '/clientes'; }, 1800);
            } else {
                mostrarModalMensaje(data.message || 'Error al guardar el cliente.', 'error');
            }
        })
        .catch(function() {
            mostrarModalMensaje('Error al guardar el cliente.', 'error');
        });
    });
})();
</script>
@endpush
@endsection
