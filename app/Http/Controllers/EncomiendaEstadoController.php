<?php

namespace App\Http\Controllers;

use App\Models\Encomienda;
use App\Models\EncomiendaHistorialEstado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EncomiendaEstadoController extends Controller
{
    public function store(Request $request, $id)
    {
        $encomienda = Encomienda::findOrFail($id);
        $validated = $request->validate([
            'estado' => 'required|string|max:80',
            'comentario' => 'nullable|string|max:1000',
        ]);

        $encomienda->update([
            'estado_actual' => $validated['estado'],
            'updated_by' => Auth::id(),
        ]);

        EncomiendaHistorialEstado::create([
            'encomienda_id' => $encomienda->id,
            'estado' => $validated['estado'],
            'comentario' => $validated['comentario'] ?? null,
            'user_id' => Auth::id(),
            'fecha_cambio' => now(),
        ]);

        return redirect()->route('encomiendas.show', $encomienda->id)
            ->with('success', 'Estado actualizado correctamente.');
    }
}
