@csrf
<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Nombre completo *</label>
        <input type="text" name="nombre_completo" value="{{ old('nombre_completo', $remitente->nombre_completo ?? '') }}" required class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Teléfono *</label>
        <input type="text" name="telefono" value="{{ old('telefono', $remitente->telefono ?? '') }}" required class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Correo</label>
        <input type="email" name="correo" value="{{ old('correo', $remitente->correo ?? '') }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Identificación</label>
        <input type="text" name="identificacion" value="{{ old('identificacion', $remitente->identificacion ?? '') }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>
    <div class="md:col-span-2">
        <label class="mb-1 block text-sm font-medium text-slate-600">Dirección</label>
        <input type="text" name="direccion" value="{{ old('direccion', $remitente->direccion ?? '') }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Ciudad</label>
        <input type="text" name="ciudad" value="{{ old('ciudad', $remitente->ciudad ?? '') }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Estado</label>
        <input type="text" name="estado" value="{{ old('estado', $remitente->estado ?? '') }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>
</div>
<div class="mt-6 flex justify-end gap-2">
    <a href="{{ route('remitentes.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5">Cancelar</a>
    <button type="submit" class="rounded-lg bg-[#15537c] px-5 py-2.5 text-white">{{ $buttonText }}</button>
</div>
