<?php

namespace App\Http\Controllers;

use App\Models\ParametroRentabilidad;
use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContabilidadParametroController extends Controller
{
    public function index()
    {
        $servicios = Servicio::query()->orderBy('tipo_servicio')->get(['id', 'tipo_servicio', 'descripcion']);

        // Costo vigente por cada servicio + global (fallback)
        $vigentePorServicio = [];
        foreach ($servicios as $s) {
            $param = ParametroRentabilidad::vigentePorServicio($s->id);
            $tieneEspecifico = $param && (int) $param->servicio_id === (int) $s->id;
            $vigentePorServicio[$s->id] = [
                'servicio' => $s,
                'parametro' => $param,
                'es_especifico' => $tieneEspecifico,
            ];
        }

        $vigenteGlobal = ParametroRentabilidad::vigenteGlobal();

        $historico = ParametroRentabilidad::query()
            ->withTrashed()
            ->with(['creador:id,nombre', 'servicio:id,tipo_servicio'])
            ->orderByDesc('vigente_desde')
            ->orderByDesc('id')
            ->paginate(20);

        return view('contabilidad.parametros.index', compact(
            'servicios',
            'vigentePorServicio',
            'vigenteGlobal',
            'historico'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'costo_fijo_por_libra' => 'required|numeric|min:0',
            'servicio_id' => 'nullable|integer|exists:servicios,id',
            'moneda' => 'required|in:USD,NIO',
            'vigente_desde' => 'required|date',
            'nota' => 'nullable|string|max:500',
        ]);

        ParametroRentabilidad::create([
            'costo_fijo_por_libra' => $data['costo_fijo_por_libra'],
            'servicio_id' => $data['servicio_id'] ?? null,
            'moneda' => $data['moneda'],
            'vigente_desde' => $data['vigente_desde'],
            'nota' => $data['nota'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('contabilidad.parametros.index')
            ->with('success', 'Costo por libra registrado correctamente.');
    }

    /**
     * Anula (soft delete) un parámetro mal ingresado.
     * El registro queda en la BD para auditoría pero el motor de rentabilidad lo ignora.
     */
    public function destroy(int $id)
    {
        $param = ParametroRentabilidad::query()->findOrFail($id);
        $nombre = $param->servicio?->tipo_servicio ?? 'Global';
        $valor = number_format((float) $param->costo_fijo_por_libra, 4);

        $param->delete();

        return redirect()
            ->route('contabilidad.parametros.index')
            ->with('success', "Parámetro anulado: {$nombre} – \${$valor}. El sistema vuelve a usar el valor anterior vigente.");
    }

    /**
     * Restaura un parámetro previamente anulado.
     */
    public function restore(int $id)
    {
        $param = ParametroRentabilidad::query()->onlyTrashed()->findOrFail($id);
        $nombre = $param->servicio?->tipo_servicio ?? 'Global';

        $param->restore();

        return redirect()
            ->route('contabilidad.parametros.index')
            ->with('success', "Parámetro restaurado: {$nombre}. Vuelve a estar activo desde {$param->vigente_desde->format('d/m/Y')}.");
    }
}
