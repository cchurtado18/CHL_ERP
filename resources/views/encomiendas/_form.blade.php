@csrf
<div class="space-y-6">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="rounded-lg border border-slate-200 p-4">
            <div class="mb-1 flex items-center justify-between">
                <label class="block text-sm font-medium text-slate-600">Remitente *</label>
                <a href="{{ route('remitentes.index') }}" class="text-xs font-medium text-[#15537c] hover:underline">Ver lista</a>
            </div>
            <select name="remitente_id" id="remitente_id" class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
                <option value="">Seleccione remitente</option>
                @foreach($remitentes as $remitente)
                    <option value="{{ $remitente->id }}" {{ (string) old('remitente_id', isset($encomienda) ? $encomienda->remitente_id : '') === (string) $remitente->id ? 'selected' : '' }}>
                        {{ $remitente->nombre_completo }} - {{ $remitente->telefono }}
                    </option>
                @endforeach
            </select>
            <input type="hidden" name="nuevo_remitente" id="nuevo_remitente" value="{{ old('nuevo_remitente') ? '1' : '0' }}">
            <button type="button" id="btn_nuevo_remitente" class="mt-3 rounded-lg border border-[#15537c] px-3 py-2 text-sm font-medium text-[#15537c]">
                + Agregar remitente nuevo
            </button>
            <div id="nuevo_remitente_box" class="mt-3 grid grid-cols-1 gap-2 {{ old('nuevo_remitente') ? '' : 'hidden' }}">
                <input type="text" name="remitente[nombre_completo]" value="{{ old('remitente.nombre_completo') }}" placeholder="Nombre completo" class="rounded border border-slate-300 px-3 py-2">
                <input type="text" name="remitente[telefono]" value="{{ old('remitente.telefono') }}" placeholder="Telefono" class="rounded border border-slate-300 px-3 py-2">
                <input type="email" name="remitente[correo]" value="{{ old('remitente.correo') }}" placeholder="Correo" class="rounded border border-slate-300 px-3 py-2">
                <input type="text" name="remitente[direccion]" value="{{ old('remitente.direccion') }}" placeholder="Direccion" class="rounded border border-slate-300 px-3 py-2">
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" name="remitente[ciudad]" value="{{ old('remitente.ciudad') }}" placeholder="Ciudad" class="rounded border border-slate-300 px-3 py-2">
                    <input type="text" name="remitente[estado]" value="{{ old('remitente.estado') }}" placeholder="Estado" class="rounded border border-slate-300 px-3 py-2">
                </div>
                <input type="text" name="remitente[identificacion]" value="{{ old('remitente.identificacion') }}" placeholder="Identificacion opcional" class="rounded border border-slate-300 px-3 py-2">
            </div>
        </div>
        <div class="rounded-lg border border-slate-200 p-4">
            <div class="mb-1 flex items-center justify-between">
                <label class="block text-sm font-medium text-slate-600">Destinatario *</label>
                <a href="{{ route('destinatarios.index') }}" class="text-xs font-medium text-[#15537c] hover:underline">Ver lista</a>
            </div>
            <select name="destinatario_id" id="destinatario_id" class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
                <option value="">Seleccione destinatario</option>
                @foreach($destinatarios as $destinatario)
                    <option value="{{ $destinatario->id }}" {{ (string) old('destinatario_id', isset($encomienda) ? $encomienda->destinatario_id : '') === (string) $destinatario->id ? 'selected' : '' }}>
                        {{ $destinatario->nombre_completo }} - {{ $destinatario->telefono_1 }}
                    </option>
                @endforeach
            </select>
            <input type="hidden" name="nuevo_destinatario" id="nuevo_destinatario" value="{{ old('nuevo_destinatario') ? '1' : '0' }}">
            <button type="button" id="btn_nuevo_destinatario" class="mt-3 rounded-lg border border-[#15537c] px-3 py-2 text-sm font-medium text-[#15537c]">
                + Agregar destinatario nuevo
            </button>
            <div id="nuevo_destinatario_box" class="mt-3 grid grid-cols-1 gap-2 {{ old('nuevo_destinatario') ? '' : 'hidden' }}">
                <input type="text" name="destinatario[nombre_completo]" value="{{ old('destinatario.nombre_completo') }}" placeholder="Nombre completo" class="rounded border border-slate-300 px-3 py-2">
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" name="destinatario[telefono_1]" value="{{ old('destinatario.telefono_1') }}" placeholder="Telefono 1" class="rounded border border-slate-300 px-3 py-2">
                    <input type="text" name="destinatario[telefono_2]" value="{{ old('destinatario.telefono_2') }}" placeholder="Telefono 2" class="rounded border border-slate-300 px-3 py-2">
                </div>
                <input type="text" name="destinatario[direccion]" value="{{ old('destinatario.direccion') }}" placeholder="Direccion exacta de entrega" class="rounded border border-slate-300 px-3 py-2">
                <input type="text" name="destinatario[referencias]" value="{{ old('destinatario.referencias') }}" placeholder="Referencias" class="rounded border border-slate-300 px-3 py-2">
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" name="destinatario[ciudad]" value="{{ old('destinatario.ciudad') }}" placeholder="Ciudad" class="rounded border border-slate-300 px-3 py-2">
                    <input type="text" name="destinatario[departamento]" value="{{ old('destinatario.departamento') }}" placeholder="Departamento" class="rounded border border-slate-300 px-3 py-2">
                </div>
                <input type="text" name="destinatario[cedula]" value="{{ old('destinatario.cedula') }}" placeholder="Cedula opcional" class="rounded border border-slate-300 px-3 py-2">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="destinatario[autorizado_para_recibir]" value="1" {{ old('destinatario.autorizado_para_recibir', 1) ? 'checked' : '' }}>
                    Autorizado para recibir
                </label>
            </div>
        </div>
        <div class="md:col-span-2 rounded-lg border border-slate-200 bg-slate-50/80 p-4">
            <label class="mb-1 block text-sm font-medium text-slate-600">Tipo de servicio del envío *</label>
            <select name="tipo_servicio" id="tipo_servicio_encomienda" required class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5">
                <option value="maritimo" {{ old('tipo_servicio', isset($encomienda) ? ($encomienda->tipo_servicio ?? 'maritimo') : 'maritimo') === 'maritimo' ? 'selected' : '' }}>Marítimo — por ítem: peso (lb) o pie cúbico</option>
                <option value="aereo" {{ old('tipo_servicio', isset($encomienda) ? ($encomienda->tipo_servicio ?? 'maritimo') : 'maritimo') === 'aereo' ? 'selected' : '' }}>Aéreo — solo cobro por libra (peso) en cada ítem</option>
            </select>
            <p id="tipo_servicio_ayuda" class="mt-2 text-xs text-slate-600"></p>
        </div>
        <div class="md:col-span-2 rounded-lg border border-slate-200 bg-white p-4">
            <label class="mb-1 block text-sm font-medium text-slate-600">Descripción del envío <span class="font-normal text-slate-500">(referencia interna)</span></label>
            <p class="mb-2 text-xs text-slate-600">Describe qué envía el cliente (contenido, marcas, etc.). Se guarda y se muestra en el detalle de la encomienda. La cantidad de bultos se obtiene automáticamente del número de ítems que agregues abajo.</p>
            <textarea name="descripcion_general" rows="4" placeholder="Ej. 2 cajas con ropa, marca Nike; regalo para cumpleaños…" class="w-full rounded-lg border border-slate-300 px-4 py-2.5">{{ old('descripcion_general', isset($encomienda) ? ($encomienda->descripcion_general ?? '') : '') }}</textarea>
            @error('descripcion_general')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="rounded-xl border border-slate-200">
        <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3">
            <h3 class="font-semibold text-slate-800">Ítems de la encomienda</h3>
            <button type="button" id="add-item" class="rounded-lg bg-[#15537c] px-4 py-2 text-sm font-medium text-white">Agregar ítem</button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full" id="items-table">
                <thead class="bg-white text-slate-700">
                    <tr class="border-b border-slate-200 text-sm">
                        <th class="px-3 py-2 text-left">Tipo</th>
                        <th class="px-3 py-2 text-left th-metodo">Método</th>
                        <th class="px-3 py-2 text-left">Peso (lb)</th>
                        <th class="th-dim px-3 py-2 text-left">Largo (in)</th>
                        <th class="th-dim px-3 py-2 text-left">Ancho (in)</th>
                        <th class="th-dim px-3 py-2 text-left">Alto (in)</th>
                        <th class="th-dim px-3 py-2 text-left">Pie cúbico</th>
                        <th class="px-3 py-2 text-left"><span class="block">Tarifa ($/lb)</span><span class="block font-normal text-slate-500">o total línea (pie cúb.)</span></th>
                        <th class="px-3 py-2 text-left">Total</th>
                        <th class="px-3 py-2 text-left" style="min-width:12rem;">Fotos bulto</th>
                        <th class="px-3 py-2"></th>
                    </tr>
                </thead>
                <tbody id="items-body"></tbody>
            </table>
        </div>
        <p class="border-t border-slate-200 bg-slate-50 px-4 py-2 text-xs text-slate-600">Cada fila es <strong>un bulto</strong> (cantidad siempre 1). Para varios bultos, agrega más filas. Opcionalmente hasta <strong>3 fotos</strong> por bulto (selección múltiple en el mismo campo). <strong>Pie cúbico (marítimo):</strong> dimensiones en pulgadas y total de la línea para ese bulto; el sistema calcula la tarifa por pie cúbico con ese mismo total y volumen unitario. <strong>Peso:</strong> tarifa $/lb.</p>
    </div>

    <div class="flex items-center justify-end gap-6 text-base">
        <div>Subtotal: <span id="subtotal" class="font-bold">$0.00</span></div>
        <div>Total: <span id="total" class="font-bold">$0.00</span></div>
    </div>

    <div class="flex justify-end gap-2">
        <a href="{{ route('encomiendas.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5">Cancelar</a>
        <button type="submit" class="rounded-lg bg-[#15537c] px-5 py-2.5 text-white">{{ $buttonText }}</button>
    </div>
</div>

@php
    $initialItems = old('items');
    if (!$initialItems && isset($encomienda)) {
        $initialItems = $encomienda->items->map(function ($item) use ($encomienda) {
            return [
                'preserve_item_id' => $item->id,
                'tipo_item' => $item->tipo_item,
                'descripcion' => $item->descripcion,
                'metodo_cobro' => $item->metodo_cobro,
                'peso_lb' => $item->peso_lb,
                'largo_in' => $item->largo_in,
                'ancho_in' => $item->ancho_in,
                'alto_in' => $item->alto_in,
                'tarifa_manual' => $item->metodo_cobro === 'peso' ? $item->tarifa_manual : null,
                'total_linea_pie_cubico' => $item->metodo_cobro === 'pie_cubico' ? $item->monto_total_item : null,
                'monto_total_item' => $item->monto_total_item,
                'foto_keeps' => collect($item->fotoPathsList())->map(function ($path, $i) use ($encomienda, $item) {
                    return [
                        'path' => $path,
                        'url' => route('encomiendas.item-foto', ['encomienda' => $encomienda->id, 'item' => $item->id, 'index' => $i]),
                    ];
                })->values()->all(),
            ];
        })->toArray();
    }
@endphp

@push('scripts')
<script>
(function () {
    const body = document.getElementById('items-body');
    const addBtn = document.getElementById('add-item');
    const subtotalEl = document.getElementById('subtotal');
    const totalEl = document.getElementById('total');
    const initialItems = @json($initialItems ?: []);
    const tipoServicioInicial = @json(old('tipo_servicio', isset($encomienda) ? ($encomienda->tipo_servicio ?? 'maritimo') : 'maritimo'));
    const nuevoRemitente = document.getElementById('nuevo_remitente');
    const nuevoDestinatario = document.getElementById('nuevo_destinatario');
    const btnNuevoRemitente = document.getElementById('btn_nuevo_remitente');
    const btnNuevoDestinatario = document.getElementById('btn_nuevo_destinatario');
    const remitenteSelect = document.getElementById('remitente_id');
    const destinatarioSelect = document.getElementById('destinatario_id');
    const remitenteBox = document.getElementById('nuevo_remitente_box');
    const destinatarioBox = document.getElementById('nuevo_destinatario_box');

    function togglePersonBlocks() {
        const useNuevoRemitente = nuevoRemitente && nuevoRemitente.value === '1';
        const useNuevoDestinatario = nuevoDestinatario && nuevoDestinatario.value === '1';
        remitenteBox.classList.toggle('hidden', !useNuevoRemitente);
        destinatarioBox.classList.toggle('hidden', !useNuevoDestinatario);
        remitenteSelect.disabled = useNuevoRemitente;
        destinatarioSelect.disabled = useNuevoDestinatario;
        if (btnNuevoRemitente) {
            btnNuevoRemitente.textContent = useNuevoRemitente ? 'Usar remitente existente' : '+ Agregar remitente nuevo';
        }
        if (btnNuevoDestinatario) {
            btnNuevoDestinatario.textContent = useNuevoDestinatario ? 'Usar destinatario existente' : '+ Agregar destinatario nuevo';
        }
    }

    const tipoServicioSelect = document.getElementById('tipo_servicio_encomienda');
    const tipoServicioAyuda = document.getElementById('tipo_servicio_ayuda');

    function isAereo() {
        return tipoServicioSelect && tipoServicioSelect.value === 'aereo';
    }

    function escapeAttr(s) {
        return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;');
    }

    function buildFotoKeepsHtml(index, fotoKeeps) {
        if (!fotoKeeps || !fotoKeeps.length) return '';
        return fotoKeeps.map((k) => {
            const path = escapeAttr(k.path);
            const url = escapeAttr(k.url);
            return `<div class="item-foto-keep-wrap mb-1 flex items-center gap-1">
                <img src="${url}" alt="" class="item-foto-preview h-12 w-12 shrink-0 rounded border border-slate-200 object-cover">
                <input type="hidden" name="items[${index}][keep_foto_paths][]" value="${path}" class="keep-foto-path">
                <button type="button" class="item-foto-keep-remove rounded px-1 text-red-600 hover:bg-red-50" title="Quitar esta foto">×</button>
            </div>`;
        }).join('');
    }

    function rowTemplate(index, data = {}) {
        const aereo = isAereo();
        const metodo = aereo ? 'peso' : (data.metodo_cobro || 'peso');
        const tarifaVal = (data.tarifa_manual != null && data.tarifa_manual !== '') ? data.tarifa_manual : '';
        const totalPieVal = (data.total_linea_pie_cubico != null && data.total_linea_pie_cubico !== '')
            ? data.total_linea_pie_cubico
            : ((data.monto_total_item != null && data.monto_total_item !== '') ? data.monto_total_item : '');

        const metodoCell = aereo
            ? `<input type="hidden" name="items[${index}][metodo_cobro]" value="peso"><span class="text-sm text-slate-700">Peso (lb)</span>`
            : `<select name="items[${index}][metodo_cobro]" class="metodo rounded border border-slate-300 px-2 py-1">
                        <option value="peso" ${metodo === 'peso' ? 'selected' : ''}>Peso</option>
                        <option value="pie_cubico" ${metodo === 'pie_cubico' ? 'selected' : ''}>Pie cúbico</option>
                    </select>`;
        const dimHidden = aereo ? 'hidden' : '';
        const pie = metodo === 'pie_cubico';

        let precioTd;
        if (aereo) {
            precioTd = `<td class="px-3 py-2">
                <span class="mb-0.5 block text-xs text-slate-500">Tarifa ($/lb)</span>
                <input type="number" step="0.01" min="0.01" name="items[${index}][tarifa_manual]" value="${tarifaVal}" required class="tarifa w-28 rounded border border-slate-300 px-2 py-1">
            </td>`;
        } else {
            precioTd = `<td class="px-3 py-2 td-precio">
                <div class="wrap-peso precio-wrap ${pie ? 'hidden' : ''}">
                    <span class="mb-0.5 block text-xs text-slate-500">Tarifa ($/lb)</span>
                    <input type="number" step="0.01" min="0.01" name="items[${index}][tarifa_manual]" value="${!pie ? tarifaVal : ''}" class="tarifa w-28 rounded border border-slate-300 px-2 py-1" ${pie ? 'disabled' : ''}>
                </div>
                <div class="wrap-pie precio-wrap ${pie ? '' : 'hidden'}">
                    <span class="mb-0.5 block text-xs text-slate-500">Total línea ($) (1 unidad)</span>
                    <input type="number" step="0.01" min="0.01" name="items[${index}][total_linea_pie_cubico]" value="${pie ? totalPieVal : ''}" class="total-linea-pie w-28 rounded border border-slate-300 px-2 py-1" ${pie ? '' : 'disabled'}>
                    <div class="mt-1 text-xs text-slate-500">Tarifa sistema: <span class="tarifa-calc-val font-medium text-slate-700">—</span></div>
                </div>
            </td>`;
        }

        const preserveHidden = data.preserve_item_id
            ? `<input type="hidden" name="items[${index}][preserve_item_id]" value="${data.preserve_item_id}" class="preserve-item-id">`
            : '';
        const fotoKeeps = data.foto_keeps || [];
        const fotoKeepsHtml = buildFotoKeepsHtml(index, fotoKeeps);

        return `
            <tr class="border-b border-slate-100 item-row">
                <td class="px-3 py-2"><input name="items[${index}][tipo_item]" value="${data.tipo_item || ''}" required class="w-32 rounded border border-slate-300 px-2 py-1"></td>
                <td class="px-3 py-2 td-metodo">${metodoCell}</td>
                <input type="hidden" name="items[${index}][cantidad]" value="1" class="cantidad">
                <td class="px-3 py-2"><input type="number" step="0.01" min="0" name="items[${index}][peso_lb]" value="${data.peso_lb || ''}" class="peso w-24 rounded border border-slate-300 px-2 py-1"></td>
                <td class="td-dim px-3 py-2 ${dimHidden}"><input type="number" step="0.01" min="0" name="items[${index}][largo_in]" value="${aereo ? '' : (data.largo_in || '')}" class="largo w-20 rounded border border-slate-300 px-2 py-1" ${aereo ? 'disabled' : ''}></td>
                <td class="td-dim px-3 py-2 ${dimHidden}"><input type="number" step="0.01" min="0" name="items[${index}][ancho_in]" value="${aereo ? '' : (data.ancho_in || '')}" class="ancho w-20 rounded border border-slate-300 px-2 py-1" ${aereo ? 'disabled' : ''}></td>
                <td class="td-dim px-3 py-2 ${dimHidden}"><input type="number" step="0.01" min="0" name="items[${index}][alto_in]" value="${aereo ? '' : (data.alto_in || '')}" class="alto w-20 rounded border border-slate-300 px-2 py-1" ${aereo ? 'disabled' : ''}></td>
                <td class="td-dim px-3 py-2 ${dimHidden}"><span class="ft3 text-slate-700">0.0000</span></td>
                ${precioTd}
                <td class="px-3 py-2"><span class="line-total font-semibold">$0.00</span></td>
                <td class="px-3 py-2 align-top">${preserveHidden}
                    <div class="item-fotos-wrap space-y-1">${fotoKeepsHtml}
                    <input type="file" name="items[${index}][fotos][]" multiple accept="image/jpeg,image/png,image/webp,image/gif" class="item-fotos-input max-w-[12rem] text-xs">
                    <span class="mt-0.5 block text-[10px] text-slate-500">Hasta 3 (varias en un solo campo)</span>
                    </div>
                </td>
                <td class="px-3 py-2"><button type="button" class="remove text-red-700"><i class="fas fa-trash"></i></button></td>
            </tr>
        `;
    }

    function reindexRows() {
        Array.from(body.querySelectorAll('.item-row')).forEach((row, index) => {
            row.querySelectorAll('input, select').forEach((input) => {
                input.name = input.name.replace(/items\[\d+\]/, `items[${index}]`);
            });
        });
    }

    function updatePrecioMode(row) {
        if (isAereo()) return;
        const wrapPie = row.querySelector('.wrap-pie');
        if (!wrapPie) return;
        const metodo = row.querySelector('.metodo') ? row.querySelector('.metodo').value : 'peso';
        const pie = metodo === 'pie_cubico';
        const wrapPeso = row.querySelector('.wrap-peso');
        if (wrapPeso) wrapPeso.classList.toggle('hidden', pie);
        wrapPie.classList.toggle('hidden', !pie);
        const tarifaIn = row.querySelector('.tarifa');
        const totalPieIn = row.querySelector('.total-linea-pie');
        if (tarifaIn) {
            tarifaIn.disabled = pie;
            if (pie) tarifaIn.value = '';
        }
        if (totalPieIn) {
            totalPieIn.disabled = !pie;
            if (!pie) totalPieIn.value = '';
        }
    }

    function calcRow(row) {
        const metodoSelect = row.querySelector('.metodo');
        const metodo = isAereo() ? 'peso' : (metodoSelect ? metodoSelect.value : 'peso');
        const cantidad = parseFloat(row.querySelector('.cantidad').value || '0');
        const peso = parseFloat(row.querySelector('.peso').value || '0');
        const largo = parseFloat((row.querySelector('.largo') && row.querySelector('.largo').value) || '0');
        const ancho = parseFloat((row.querySelector('.ancho') && row.querySelector('.ancho').value) || '0');
        const alto = parseFloat((row.querySelector('.alto') && row.querySelector('.alto').value) || '0');
        const tarifaIn = row.querySelector('.tarifa');
        const tarifa = tarifaIn && !tarifaIn.disabled ? parseFloat(tarifaIn.value || '0') : 0;
        const ft3 = (largo * ancho * alto) / 1728;
        const ft3El = row.querySelector('.ft3');
        if (ft3El) ft3El.textContent = isFinite(ft3) ? ft3.toFixed(4) : '0.0000';

        let total = 0;
        const calcVal = row.querySelector('.tarifa-calc-val');
        if (metodo === 'pie_cubico' && !isAereo()) {
            const volUnit = isFinite(ft3) ? ft3 : 0; // ft³ de 1 unidad (por las dimensiones)
            const volLinea = volUnit * (cantidad || 0); // ft³ total por cantidad
            const totalUnitEl = row.querySelector('.total-linea-pie');
            const totalUnit = totalUnitEl ? parseFloat(totalUnitEl.value || '0') : 0; // $ de 1 unidad
            total = (isFinite(totalUnit) ? totalUnit : 0) * (cantidad || 0); // $ total de la línea
            if (calcVal) {
                calcVal.textContent = (volLinea > 0 && total > 0)
                    ? ('$' + (total / volLinea).toFixed(4) + '/ft³')
                    : '—';
            }
        } else {
            total = peso * tarifa * (cantidad || 0);
            if (calcVal) calcVal.textContent = '—';
        }
        row.querySelector('.line-total').textContent = '$' + (isFinite(total) ? total : 0).toFixed(2);
        return isFinite(total) ? total : 0;
    }

    function calcTotals() {
        const total = Array.from(body.querySelectorAll('.item-row')).reduce((sum, row) => sum + calcRow(row), 0);
        subtotalEl.textContent = `$${total.toFixed(2)}`;
        totalEl.textContent = `$${total.toFixed(2)}`;
    }

    function bindRow(row) {
        row.querySelectorAll('input:not([disabled]), select').forEach((el) => el.addEventListener('input', calcTotals));
        row.querySelectorAll('input:not([disabled]), select').forEach((el) => el.addEventListener('change', calcTotals));
        const metSel = row.querySelector('.metodo');
        if (metSel) {
            metSel.addEventListener('change', () => {
                updatePrecioMode(row);
                calcTotals();
            });
        }
        row.querySelector('.remove').addEventListener('click', () => {
            row.remove();
            reindexRows();
            calcTotals();
        });
        row.querySelectorAll('.item-foto-keep-remove').forEach((btn) => {
            btn.addEventListener('click', () => {
                const wrap = btn.closest('.item-foto-keep-wrap');
                if (wrap) wrap.remove();
            });
        });
        updatePrecioMode(row);
    }

    function addRow(data = {}) {
        const index = body.querySelectorAll('.item-row').length;
        body.insertAdjacentHTML('beforeend', rowTemplate(index, data));
        bindRow(body.lastElementChild);
        calcTotals();
    }

    function refreshItemsMode() {
        const aereo = isAereo();
        document.querySelectorAll('.th-dim').forEach((el) => el.classList.toggle('hidden', aereo));
        if (tipoServicioAyuda) {
            tipoServicioAyuda.textContent = aereo
                ? 'Aéreo: el cobro de cada ítem es únicamente por libra (peso). No aplica pie cúbico.'
                : 'Marítimo: por peso ingresas tarifa $/lb. Por pie cúbico ingresas dimensiones y el total de la línea; la tarifa $/ft³ la calcula el sistema.';
        }
        const rows = Array.from(body.querySelectorAll('.item-row'));
        const dataSnapshot = rows.map((row) => {
            const metodoEl = row.querySelector('.metodo');
            const metodoHidden = row.querySelector('input[type="hidden"][name*="[metodo_cobro]"]');
            const metodoVal = metodoEl ? metodoEl.value : (metodoHidden ? metodoHidden.value : 'peso');
            const tPie = row.querySelector('.total-linea-pie');
            const tLb = row.querySelector('.tarifa');
            const fotoKeeps = [];
            row.querySelectorAll('.item-foto-keep-wrap').forEach((wrap) => {
                const hid = wrap.querySelector('.keep-foto-path');
                const img = wrap.querySelector('.item-foto-preview');
                if (hid && img) fotoKeeps.push({ path: hid.value, url: img.src });
            });
            const pres = row.querySelector('.preserve-item-id');
            return {
                preserve_item_id: pres ? pres.value : '',
                foto_keeps: fotoKeeps,
                tipo_item: row.querySelector('input[name*="[tipo_item]"]')?.value || '',
                metodo_cobro: metodoVal,
                peso_lb: row.querySelector('.peso')?.value || '',
                largo_in: row.querySelector('.largo')?.value || '',
                ancho_in: row.querySelector('.ancho')?.value || '',
                alto_in: row.querySelector('.alto')?.value || '',
                tarifa_manual: tLb && !tLb.disabled ? (tLb.value || '') : '',
                total_linea_pie_cubico: tPie && !tPie.disabled ? (tPie.value || '') : '',
            };
        });
        body.innerHTML = '';
        dataSnapshot.forEach((d) => addRow(d));
        if (!dataSnapshot.length) addRow();
    }

    addBtn.addEventListener('click', () => addRow());
    if (tipoServicioSelect) {
        tipoServicioSelect.addEventListener('change', refreshItemsMode);
    }
    if (btnNuevoRemitente) {
        btnNuevoRemitente.addEventListener('click', () => {
            nuevoRemitente.value = nuevoRemitente.value === '1' ? '0' : '1';
            togglePersonBlocks();
        });
    }
    if (btnNuevoDestinatario) {
        btnNuevoDestinatario.addEventListener('click', () => {
            nuevoDestinatario.value = nuevoDestinatario.value === '1' ? '0' : '1';
            togglePersonBlocks();
        });
    }

    if (tipoServicioSelect && tipoServicioInicial) {
        tipoServicioSelect.value = tipoServicioInicial;
    }
    if (initialItems.length) {
        initialItems.forEach((item) => addRow(item));
    } else {
        addRow();
    }
    refreshItemsMode();
    togglePersonBlocks();
})();
</script>
@endpush
