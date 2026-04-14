<?php

namespace App\Http\Controllers;

use App\Models\ContaAsiento;
use App\Models\ContaCuenta;
use App\Services\Contabilidad\ContabilidadAsientoService;
use Illuminate\Http\Request;

class ContabilidadAsientoController extends Controller
{
    public function __construct(private readonly ContabilidadAsientoService $asientoService) {}

    public function index(Request $request)
    {
        $query = ContaAsiento::query()->with(['detalles.cuenta', 'creador']);
        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }
        if ($request->filled('desde')) {
            $query->whereDate('fecha', '>=', $request->input('desde'));
        }
        if ($request->filled('hasta')) {
            $query->whereDate('fecha', '<=', $request->input('hasta'));
        }
        $asientos = $query->orderByDesc('fecha')->orderByDesc('id')->paginate(20)->appends($request->query());

        return view('contabilidad.asientos.index', compact('asientos'));
    }

    public function create()
    {
        $cuentas = ContaCuenta::query()->where('activa', true)->where('acepta_movimiento', true)->orderBy('codigo')->get();

        return view('contabilidad.asientos.create', compact('cuentas'));
    }

    public function show(int $id)
    {
        $asiento = ContaAsiento::query()
            ->with(['detalles.cuenta', 'creador', 'aprobador'])
            ->findOrFail($id);

        return view('contabilidad.asientos.show', compact('asiento'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha' => 'required|date',
            'descripcion' => 'nullable|string|max:1000',
            'moneda' => 'required|in:USD,NIO',
            'tasa_cambio' => 'nullable|numeric|min:0',
            'lineas' => 'required|array|min:2',
            'lineas.*.cuenta_id' => 'required|exists:conta_cuentas,id',
            'lineas.*.debito' => 'nullable|numeric|min:0',
            'lineas.*.credito' => 'nullable|numeric|min:0',
            'lineas.*.glosa' => 'nullable|string|max:500',
        ]);

        $detalles = collect($data['lineas'])->map(function ($l) {
            return [
                'cuenta_id' => (int) $l['cuenta_id'],
                'debito' => (float) ($l['debito'] ?? 0),
                'credito' => (float) ($l['credito'] ?? 0),
                'glosa' => $l['glosa'] ?? null,
            ];
        })->all();

        $this->asientoService->crearAsiento([
            'fecha' => $data['fecha'],
            'descripcion' => $data['descripcion'] ?? null,
            'moneda' => $data['moneda'],
            'tasa_cambio' => $data['tasa_cambio'] ?? null,
            'referencia_tipo' => 'manual',
        ], $detalles);

        return redirect()->route('contabilidad.asientos.index')->with('success', 'Asiento registrado correctamente.');
    }
}
