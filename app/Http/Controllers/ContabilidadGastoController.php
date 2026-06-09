<?php

namespace App\Http\Controllers;

use App\Models\ContaCuenta;
use App\Models\ContaGasto;
use App\Models\ContaGastoCategoria;
use App\Services\Contabilidad\ContabilidadGastoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContabilidadGastoController extends Controller
{
    public function __construct(private readonly ContabilidadGastoService $gastoService) {}

    public function index(Request $request)
    {
        $query = ContaGasto::query()
            ->with(['categoria', 'cuentaPago', 'creador:id,nombre'])
            ->when($request->filled('categoria_id'), fn ($q) => $q->where('categoria_id', $request->input('categoria_id')))
            ->when($request->filled('cuenta_pago_id'), fn ($q) => $q->where('cuenta_pago_id', $request->input('cuenta_pago_id')))
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('fecha', '>=', $request->input('desde')))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('fecha', '<=', $request->input('hasta')));

        $gastos = $query->orderByDesc('fecha')->orderByDesc('id')->paginate(20)->appends($request->query());

        // Resumen del rango filtrado (o del mes actual si no hay filtro)
        $desde = $request->input('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->input('hasta', now()->endOfMonth()->toDateString());

        $resumen = [
            'total' => (float) ContaGasto::query()
                ->whereBetween('fecha', [$desde, $hasta])
                ->sum('monto'),
            'cantidad' => (int) ContaGasto::query()
                ->whereBetween('fecha', [$desde, $hasta])
                ->count(),
            'por_categoria' => DB::table('conta_gastos')
                ->join('conta_gasto_categorias', 'conta_gasto_categorias.id', '=', 'conta_gastos.categoria_id')
                ->whereNull('conta_gastos.deleted_at')
                ->whereBetween('conta_gastos.fecha', [$desde, $hasta])
                ->select('conta_gasto_categorias.nombre', 'conta_gasto_categorias.icono', DB::raw('SUM(conta_gastos.monto) AS total'))
                ->groupBy('conta_gasto_categorias.id', 'conta_gasto_categorias.nombre', 'conta_gasto_categorias.icono')
                ->orderByDesc('total')
                ->get(),
        ];

        $categorias = ContaGastoCategoria::activas()->orderBy('orden')->get();
        $cuentasPago = ContaCuenta::query()
            ->whereIn('subtipo', ['caja', 'banco'])
            ->where('activa', true)
            ->orderBy('codigo')
            ->get();

        return view('contabilidad.gastos.index', compact('gastos', 'resumen', 'categorias', 'cuentasPago'));
    }

    public function create()
    {
        $categorias = ContaGastoCategoria::activas()->orderBy('orden')->get();
        $cuentasPago = ContaCuenta::query()
            ->whereIn('subtipo', ['caja', 'banco'])
            ->where('activa', true)
            ->orderBy('codigo')
            ->get();

        return view('contabilidad.gastos.create', compact('categorias', 'cuentasPago'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha' => 'required|date',
            'categoria_id' => 'required|exists:conta_gasto_categorias,id',
            'descripcion' => 'required|string|max:500',
            'monto' => 'required|numeric|min:0.01',
            'moneda' => 'required|in:USD,NIO',
            'tasa_cambio' => 'nullable|numeric|min:0',
            'cuenta_pago_id' => 'required|exists:conta_cuentas,id',
            'referencia' => 'nullable|string|max:120',
        ]);

        try {
            $gasto = $this->gastoService->registrarGasto($data);
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('contabilidad.gastos.show', $gasto->id)->with('success', 'Gasto registrado correctamente. Se generó el asiento contable automáticamente.');
    }

    public function show(int $id)
    {
        $gasto = ContaGasto::query()
            ->with(['categoria.cuentaContable', 'cuentaPago', 'asiento.detalles.cuenta', 'creador:id,nombre'])
            ->findOrFail($id);

        return view('contabilidad.gastos.show', compact('gasto'));
    }

    public function destroy(Request $request, int $id)
    {
        $gasto = ContaGasto::findOrFail($id);

        try {
            $this->gastoService->anularGasto($gasto, $request->input('motivo'));
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('contabilidad.gastos.index')->with('success', 'Gasto anulado. Se generó el asiento de reversión.');
    }
}
