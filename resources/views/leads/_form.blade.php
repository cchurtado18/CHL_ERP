@csrf
@php
    $etapas = \App\Models\Lead::ETAPAS;
    $motivosPerdida = \App\Models\Lead::MOTIVOS_PERDIDA;
    $input = 'w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-[#15537c] focus:ring-1 focus:ring-[#15537c]';
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Nombre completo *</label>
        <input type="text" name="nombre_completo" value="{{ old('nombre_completo', $lead->nombre_completo ?? '') }}" required class="{{ $input }}">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Teléfono</label>
        <input type="text" name="telefono" value="{{ old('telefono', $lead->telefono ?? '') }}" class="{{ $input }}">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Correo</label>
        <input type="email" name="email" value="{{ old('email', $lead->email ?? '') }}" class="{{ $input }}">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Dirección del cliente</label>
        <input type="text" name="direccion_cliente" value="{{ old('direccion_cliente', $lead->direccion_cliente ?? '') }}" placeholder="Dirección en USA o dirección local de contacto" class="{{ $input }}">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Campaña</label>
        <input type="text" name="campana" value="{{ old('campana', $lead->campana ?? '') }}" class="{{ $input }}">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Origen</label>
        <input type="text" name="origen" value="{{ old('origen', $lead->origen ?? '') }}" placeholder="Meta Ads, Referido, WhatsApp..." class="{{ $input }}">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Etapa *</label>
        <select name="etapa" class="{{ $input }}" required>
            @foreach($etapas as $et)
                <option value="{{ $et }}" @selected(old('etapa', $lead->etapa ?? 'nuevo') === $et)>{{ ucwords(str_replace('_', ' ', $et)) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Responsable del lead</label>
        <select name="owner_id" class="{{ $input }}">
            <option value="">Sin asignar</option>
            @foreach(($owners ?? collect()) as $u)
                <option value="{{ $u->id }}" @selected((string) old('owner_id', $lead->owner_id ?? auth()->id()) === (string) $u->id)>
                    {{ $u->nombre ?? $u->email }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Estado (USA) de origen</label>
        <input type="text" name="estado_usa_origen" value="{{ old('estado_usa_origen', $lead->estado_usa_origen ?? '') }}" placeholder="Florida, Texas, New Jersey..." class="{{ $input }}">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Departamento destino (Nicaragua)</label>
        <input type="text" name="departamento_destino" value="{{ old('departamento_destino', $lead->departamento_destino ?? '') }}" placeholder="Managua, León, Chinandega..." class="{{ $input }}">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Municipio destino</label>
        <input type="text" name="municipio_destino" value="{{ old('municipio_destino', $lead->municipio_destino ?? '') }}" placeholder="Ciudad Sandino, Tipitapa..." class="{{ $input }}">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Interés de servicio</label>
        <input type="text" name="interes_servicio" value="{{ old('interes_servicio', $lead->interes_servicio ?? '') }}" class="{{ $input }}">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Presupuesto estimado (USD)</label>
        <input type="number" step="0.01" min="0" name="presupuesto_estimado" value="{{ old('presupuesto_estimado', $lead->presupuesto_estimado ?? '') }}" class="{{ $input }}">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Próximo contacto</label>
        <input type="datetime-local" name="proximo_contacto_at" value="{{ old('proximo_contacto_at', isset($lead->proximo_contacto_at) && $lead->proximo_contacto_at ? $lead->proximo_contacto_at->format('Y-m-d\TH:i') : '') }}" class="{{ $input }}">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Último contacto</label>
        <input type="datetime-local" name="ultimo_contacto_at" value="{{ old('ultimo_contacto_at', isset($lead->ultimo_contacto_at) && $lead->ultimo_contacto_at ? $lead->ultimo_contacto_at->format('Y-m-d\TH:i') : '') }}" class="{{ $input }}">
    </div>
    <div class="md:col-span-2">
        <label class="mb-1 block text-sm font-medium text-slate-600">Motivo de pérdida (si aplica)</label>
        <input type="text" name="motivo_perdida" value="{{ old('motivo_perdida', $lead->motivo_perdida ?? '') }}" class="{{ $input }}">
    </div>
    <div class="md:col-span-2">
        <label class="mb-1 block text-sm font-medium text-slate-600">Categoría motivo de pérdida</label>
        <select name="motivo_perdida_clave" class="{{ $input }}">
            <option value="">Sin categoría</option>
            @foreach($motivosPerdida as $motivo)
                <option value="{{ $motivo }}" @selected(old('motivo_perdida_clave', $lead->motivo_perdida_clave ?? '') === $motivo)>
                    {{ ucwords(str_replace('_', ' ', $motivo)) }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="mb-1 block text-sm font-medium text-slate-600">Notas</label>
        <textarea name="notas" rows="4" class="{{ $input }}">{{ old('notas', $lead->notas ?? '') }}</textarea>
    </div>
</div>

<div class="mt-6 flex justify-end gap-2">
    <a href="{{ route('leads.calendar') }}" class="rounded-lg border border-slate-300 px-5 py-2.5">Cancelar</a>
    <button type="submit" class="rounded-lg bg-[#15537c] px-5 py-2.5 text-white">{{ $buttonText }}</button>
</div>
