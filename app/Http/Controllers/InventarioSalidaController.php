<?php

namespace App\Http\Controllers;

use App\Exports\InventarioSalidaPaquetesExport;
use App\Models\InventarioSalida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class InventarioSalidaController extends Controller
{
    public function index()
    {
        $salidas = InventarioSalida::query()
            ->with('creador')
            ->withCount('paquetes')
            ->latest()
            ->paginate(15);

        return view('inventario.salidas.index', compact('salidas'));
    }

    public function show(InventarioSalida $salida)
    {
        $salida->load(['paquetes.cliente', 'paquetes.servicio', 'paquetes.factura', 'creador']);

        return view('inventario.salidas.show', compact('salida'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'descripcion' => 'required|string|max:5000',
            'sucursal_origen' => 'nullable|string|max:120',
            'sucursal_destino' => 'nullable|string|max:120',
            'paquetes' => 'required|array|min:1',
            'paquetes.*' => 'integer|exists:inventario,id',
        ], [
            'paquetes.required' => 'Seleccione al menos un paquete para esta salida.',
            'paquetes.min' => 'Seleccione al menos un paquete para esta salida.',
        ]);

        $ids = collect($data['paquetes'])->map(fn ($id) => (int) $id)->unique()->values()->all();

        $salida = DB::transaction(function () use ($data, $ids) {
            $s = InventarioSalida::create([
                'descripcion' => $data['descripcion'],
                'sucursal_origen' => $data['sucursal_origen'] ?? null,
                'sucursal_destino' => $data['sucursal_destino'] ?? null,
                'created_by' => Auth::id(),
            ]);
            $s->paquetes()->attach($ids);

            return $s;
        });

        return redirect()
            ->route('inventario.salidas.show', $salida)
            ->with('success', 'Salida entre sucursales registrada correctamente.');
    }

    public function export(InventarioSalida $salida)
    {
        $filename = 'salida_sucursal_'.$salida->id.'_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(new InventarioSalidaPaquetesExport($salida->id), $filename);
    }
}
