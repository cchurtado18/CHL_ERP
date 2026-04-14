@csrf
<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Nombre completo *</label>
        <input type="text" name="nombre_completo" value="{{ old('nombre_completo', $destinatario->nombre_completo ?? '') }}" required class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Teléfono 1 *</label>
        <input type="text" name="telefono_1" value="{{ old('telefono_1', $destinatario->telefono_1 ?? '') }}" required class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Teléfono 2</label>
        <input type="text" name="telefono_2" value="{{ old('telefono_2', $destinatario->telefono_2 ?? '') }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Cédula</label>
        <input type="text" name="cedula" value="{{ old('cedula', $destinatario->cedula ?? '') }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>
    <div class="md:col-span-2">
        <label class="mb-1 block text-sm font-medium text-slate-600">Dirección *</label>
        <input type="text" name="direccion" value="{{ old('direccion', $destinatario->direccion ?? '') }}" required class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>
    <div class="md:col-span-2">
        <label class="mb-1 block text-sm font-medium text-slate-600">Referencias</label>
        <input type="text" name="referencias" value="{{ old('referencias', $destinatario->referencias ?? '') }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Ciudad</label>
        <input type="text" name="ciudad" value="{{ old('ciudad', $destinatario->ciudad ?? '') }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Departamento</label>
        <input type="text" name="departamento" value="{{ old('departamento', $destinatario->departamento ?? '') }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>
    <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
            <input type="checkbox" name="autorizado_para_recibir" value="1" {{ old('autorizado_para_recibir', $destinatario->autorizado_para_recibir ?? true) ? 'checked' : '' }}>
            Autorizado para recibir
        </label>
    </div>
</div>
<div class="mt-6 flex justify-end gap-2">
    <a href="{{ route('destinatarios.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5">Cancelar</a>
    <button type="submit" class="rounded-lg bg-[#15537c] px-5 py-2.5 text-white">{{ $buttonText }}</button>
</div>
