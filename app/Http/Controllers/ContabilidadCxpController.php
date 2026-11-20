<?php

namespace App\Http\Controllers;

use App\Models\ContaCuenta;
use App\Models\ContaCxp;
use App\Models\ContaPagoProveedor;
use App\Models\Proveedor;
use App\Services\Contabilidad\ContabilidadCxpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ContabilidadCxpController extends Controller
{
    public function __construct(private readonly ContabilidadCxpService $cxpService) {}

    public function index(Request $request)
    {
        if (! Schema::hasTable('conta_cxp')) {
            return view('contabilidad.cxp.index', [
                'items' => collect(),
                'totalSaldo' => 0,
                'setupPendiente' => true,
            ]);
        }

        $q = ContaCxp::query()->with(['proveedor', 'cuentaGasto']);
        if (Schema::hasColumn('inventario', 'cxp_id')) {
            $q->withCount('paquetes')->withSum('paquetes as monto_paquetes', 'monto_calculado');
        }
        if ($request->filled('estado')) {
            $q->where('estado_pago', $request->input('estado'));
        }
        if ($request->filled('proveedor')) {
            $needle = '%'.addcslashes(trim((string) $request->input('proveedor')), '%_\\').'%';
            $q->whereHas('proveedor', fn ($p) => $p->where('nombre', 'like', $needle));
        }

        $totalSaldo = (float) (clone $q)->sum('saldo_actual');
        $items = $q->orderByDesc('fecha_emision')->orderByDesc('id')->paginate(20)->appends($request->query());

        return view('contabilidad.cxp.index', [
            'items' => $items,
            'totalSaldo' => $totalSaldo,
            'setupPendiente' => false,
        ]);
    }

    public function create(Request $request)
    {
        if (! Schema::hasTable('proveedores') || ! Schema::hasTable('conta_cxp')) {
            return redirect()->route('contabilidad.cxp.index');
        }

        $proveedores = Proveedor::query()->where('activo', true)->orderBy('nombre')->get();
        $cuentasGasto = ContaCuenta::query()
            ->whereIn('tipo', ['gasto', 'costo'])
            ->where('activa', true)
            ->where('acepta_movimiento', true)
            ->orderBy('codigo')
            ->get();
        $proveedorId = $request->input('proveedor_id');
        $cuentaCostoFleteId = $this->cxpService->cuentaCostoFlete()?->id;

        return view('contabilidad.cxp.create', compact('proveedores', 'cuentasGasto', 'proveedorId', 'cuentaCostoFleteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'proveedor_nombre' => 'nullable|string|max:180',
            'numero_documento' => 'required|string|max:80',
            'descripcion' => 'required|string|max:500',
            'fecha_emision' => 'required|date',
            'dias_credito' => 'nullable|integer|min:0|max:365',
            'fecha_vencimiento' => 'nullable|date',
            'monto_original' => 'required|numeric|min:0.01',
            'moneda' => 'nullable|in:USD,NIO',
            'tasa_cambio' => 'nullable|numeric|min:0',
            'cuenta_gasto_id' => 'nullable|exists:conta_cuentas,id',
        ]);

        $data['moneda'] = $data['moneda'] ?? 'USD';
        $data['cuenta_gasto_id'] = $data['cuenta_gasto_id'] ?? $this->cxpService->cuentaCostoFlete()?->id;
        if (empty($data['cuenta_gasto_id'])) {
            return back()->withErrors(['cuenta_gasto_id' => 'No hay una cuenta de costo de flete. Créala en el catálogo (5.2.01).'])->withInput();
        }

        if (empty($data['proveedor_id']) && empty($data['proveedor_nombre'])) {
            return back()->withErrors(['proveedor_id' => 'Selecciona un proveedor o escribe uno nuevo.'])->withInput();
        }

        try {
            if (empty($data['proveedor_id'])) {
                $proveedor = Proveedor::create([
                    'nombre' => $data['proveedor_nombre'],
                    'activo' => true,
                    'created_by' => $request->user()?->id,
                ]);
                $data['proveedor_id'] = $proveedor->id;
            }
            $cxp = $this->cxpService->registrarFactura($data);
        } catch (RuntimeException $e) {
            return back()->withErrors(['monto_original' => $e->getMessage()])->withInput();
        }

        return redirect()->route('contabilidad.cxp.show', $cxp->id)->with('success', 'Factura de proveedor registrada y contabilizada (partida doble).');
    }

    public function show(int $id)
    {
        $cxp = ContaCxp::query()
            ->with(['proveedor', 'cuentaGasto', 'pagos.cuentaBancoCaja', 'pagos.voucher', 'creador', 'paquetes.cliente'])
            ->findOrFail($id);

        $cuentasBancoCaja = ContaCuenta::query()
            ->whereIn('subtipo', ['banco', 'caja'])
            ->where('activa', true)
            ->orderBy('subtipo')
            ->orderBy('codigo')
            ->get();

        $cobrado = round((float) $cxp->monto_original - (float) $cxp->saldo_actual, 2);

        return view('contabilidad.cxp.show', compact('cxp', 'cuentasBancoCaja', 'cobrado'));
    }

    public function pagar(Request $request, int $id)
    {
        $cxp = ContaCxp::query()->findOrFail($id);
        $data = $request->validate([
            'fecha_pago' => 'required|date',
            'monto' => 'required|numeric|min:0.01',
            'moneda' => 'nullable|in:USD,NIO',
            'tasa_cambio' => 'nullable|numeric|min:0',
            'metodo' => 'required|string|max:100',
            'cuenta_banco_caja_id' => 'required|exists:conta_cuentas,id',
            'referencia' => 'required|string|max:120',
            'comprobante' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ], [
            'referencia.required' => 'Ingresa el número de referencia del pago.',
            'comprobante.required' => 'Sube la foto de la referencia o comprobante.',
            'comprobante.image' => 'El comprobante debe ser una imagen.',
        ]);

        $data['moneda'] = $data['moneda'] ?? $cxp->moneda ?? 'USD';
        $data['comprobante_path'] = $request->file('comprobante')->store('cxp/comprobantes', 'public');

        try {
            $pago = $this->cxpService->registrarPago($cxp, $data);
        } catch (RuntimeException $e) {
            if (! empty($data['comprobante_path'])) {
                Storage::disk('public')->delete($data['comprobante_path']);
            }

            return back()->withErrors(['monto' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('contabilidad.cxp.show', $cxp->id)
            ->with('success', 'Pago registrado. Voucher '.$pago->voucher?->folio.'. Paquetes de esa factura quedan cancelados al proveedor si el saldo llegó a cero.');
    }

    public function comprobante(int $pagoId)
    {
        $pago = ContaPagoProveedor::query()->findOrFail($pagoId);
        if (! $pago->comprobante_path || ! Storage::disk('public')->exists($pago->comprobante_path)) {
            abort(404);
        }

        return Storage::disk('public')->response($pago->comprobante_path);
    }
}
