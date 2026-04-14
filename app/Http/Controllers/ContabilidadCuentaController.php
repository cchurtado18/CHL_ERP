<?php

namespace App\Http\Controllers;

use App\Models\ContaCuenta;
use Illuminate\Http\Request;

class ContabilidadCuentaController extends Controller
{
    public function index(Request $request)
    {
        $q = ContaCuenta::query();
        if ($request->filled('tipo')) {
            $q->where('tipo', $request->input('tipo'));
        }
        $cuentas = $q->orderBy('codigo')->paginate(25)->appends($request->query());

        return view('contabilidad.cuentas.index', compact('cuentas'));
    }

    public function create()
    {
        $padres = ContaCuenta::query()->where('acepta_movimiento', false)->orWhereNull('cuenta_padre_id')->orderBy('codigo')->get();

        return view('contabilidad.cuentas.create', compact('padres'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo' => 'required|string|max:30|unique:conta_cuentas,codigo',
            'nombre' => 'required|string|max:180',
            'tipo' => 'required|in:activo,pasivo,patrimonio,ingreso,gasto,costo',
            'subtipo' => 'nullable|string|max:100',
            'cuenta_padre_id' => 'nullable|exists:conta_cuentas,id',
            'acepta_movimiento' => 'nullable|boolean',
            'activa' => 'nullable|boolean',
        ]);
        $data['acepta_movimiento'] = (bool) ($data['acepta_movimiento'] ?? false);
        $data['activa'] = (bool) ($data['activa'] ?? false);
        ContaCuenta::create($data);

        return redirect()->route('contabilidad.cuentas.index')->with('success', 'Cuenta creada.');
    }
}
