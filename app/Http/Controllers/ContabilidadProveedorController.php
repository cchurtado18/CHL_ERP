<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ContabilidadProveedorController extends Controller
{
    public function index(Request $request)
    {
        if (! Schema::hasTable('proveedores')) {
            return view('contabilidad.proveedores.index', [
                'proveedores' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20),
            ]);
        }

        $q = Proveedor::query()->withSum(['cuentasPorPagar as saldo_pendiente' => function ($sub) {
            $sub->where('saldo_actual', '>', 0);
        }], 'saldo_actual');

        if ($request->filled('q')) {
            $needle = '%'.addcslashes(trim((string) $request->input('q')), '%_\\').'%';
            $q->where(function ($sub) use ($needle) {
                $sub->where('nombre', 'like', $needle)
                    ->orWhere('ruc', 'like', $needle)
                    ->orWhere('telefono', 'like', $needle);
            });
        }

        $proveedores = $q->orderBy('nombre')->paginate(20)->appends($request->query());

        return view('contabilidad.proveedores.index', compact('proveedores'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:180',
            'ruc' => 'nullable|string|max:40',
            'telefono' => 'nullable|string|max:40',
            'correo' => 'nullable|email|max:120',
            'direccion' => 'nullable|string|max:255',
            'notas' => 'nullable|string',
        ]);
        $data['activo'] = true;
        $data['created_by'] = Auth::id();

        $proveedor = Proveedor::create($data);

        if ($request->boolean('volver_cxp')) {
            return redirect()
                ->route('contabilidad.cxp.create', ['proveedor_id' => $proveedor->id])
                ->with('success', 'Proveedor registrado. Ahora carga su factura.');
        }

        return redirect()->route('contabilidad.proveedores.index')->with('success', 'Proveedor registrado.');
    }
}
