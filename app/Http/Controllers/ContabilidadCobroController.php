<?php

namespace App\Http\Controllers;

use App\Models\ContaCobro;
use App\Models\ContaCuenta;
use App\Models\ContaCxc;
use App\Models\Facturacion;
use App\Services\Contabilidad\ContabilidadAsientoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class ContabilidadCobroController extends Controller
{
    public function __construct(private readonly ContabilidadAsientoService $asientoService) {}

    public function index()
    {
        $cobros = ContaCobro::query()
            ->with(['factura.cliente', 'factura.encomienda.remitente', 'cuentaBancoCaja'])
            ->orderByDesc('fecha_pago')
            ->paginate(20);

        return view('contabilidad.cobros.index', compact('cobros'));
    }

    public function create(Request $request)
    {
        $facturas = Facturacion::query()
            ->with(['cliente', 'encomienda.remitente', 'contaCxc'])
            ->whereExists(function ($q) {
                $q->selectRaw(1)->from('conta_cxc')->whereColumn('conta_cxc.factura_id', 'facturacion.id')->where('conta_cxc.saldo_actual', '>', 0);
            })
            ->orderByDesc('fecha_factura')
            ->limit(200)
            ->get();
        $cuentasBancoCaja = ContaCuenta::query()
            ->whereIn('subtipo', ['banco', 'caja'])
            ->where('activa', true)
            ->orderBy('subtipo')
            ->orderBy('codigo')
            ->get();
        $facturaIdPrecarga = $request->filled('factura_id') ? (int) $request->input('factura_id') : null;

        return view('contabilidad.cobros.create', compact('facturas', 'cuentasBancoCaja', 'facturaIdPrecarga'));
    }

    public function show(int $id)
    {
        $cobro = ContaCobro::query()
            ->with(['factura.cliente', 'factura.encomienda.remitente', 'cuentaBancoCaja', 'creador'])
            ->findOrFail($id);

        return view('contabilidad.cobros.show', compact('cobro'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'factura_id' => 'required|exists:facturacion,id',
            'fecha_pago' => 'required|date',
            'monto' => 'required|numeric|min:0.01',
            'moneda' => 'required|in:USD,NIO',
            'tasa_cambio' => 'nullable|numeric|min:0',
            'metodo' => 'required|string|max:100',
            'cuenta_banco_caja_id' => 'required|exists:conta_cuentas,id',
            'referencia' => 'nullable|string|max:120',
            'comision' => 'nullable|numeric|min:0',
        ]);

        $factura = Facturacion::findOrFail($data['factura_id']);
        $cxc = ContaCxc::query()->where('factura_id', $factura->id)->first();
        if (! $cxc) {
            throw new RuntimeException('La factura no tiene registro en CxC.');
        }
        $monto = (float) $data['monto'];
        $saldo = (float) $cxc->saldo_actual;
        if ($monto - $saldo > 0.009) {
            return back()->withErrors([
                'monto' => 'El cobro no puede superar el saldo pendiente en CxC (máximo permitido: $'.number_format($saldo, 2).').',
            ])->withInput();
        }

        $cobro = ContaCobro::create([
            'factura_id' => $factura->id,
            'fecha_pago' => Carbon::parse($data['fecha_pago'])->toDateString(),
            'monto' => $monto,
            'moneda' => $data['moneda'],
            'tasa_cambio' => $data['tasa_cambio'] ?? null,
            'metodo' => $data['metodo'],
            'cuenta_banco_caja_id' => (int) $data['cuenta_banco_caja_id'],
            'referencia' => $data['referencia'] ?? null,
            'comision' => $data['comision'] ?? null,
            'created_by' => Auth::id(),
        ]);

        $cuentaCxc = $this->asientoService->buscarCuentaPorSubtipo('cxc');
        if (! $cuentaCxc) {
            throw new RuntimeException('No existe cuenta contable CxC configurada.');
        }

        $this->asientoService->crearAsiento(
            [
                'fecha' => $cobro->fecha_pago,
                'referencia_tipo' => 'cobro',
                'referencia_id' => $cobro->id,
                'descripcion' => 'Cobro factura #'.$factura->id,
                'moneda' => $cobro->moneda,
                'tasa_cambio' => $cobro->tasa_cambio,
            ],
            [
                [
                    'cuenta_id' => $cobro->cuenta_banco_caja_id,
                    'tercero_tipo' => 'cliente',
                    'tercero_id' => $factura->cliente_id,
                    'debito' => $monto,
                    'credito' => 0,
                    'monto_origen' => $monto,
                    'monto_funcional' => $monto,
                    'glosa' => 'Ingreso de cobro factura #'.$factura->id,
                ],
                [
                    'cuenta_id' => $cuentaCxc->id,
                    'tercero_tipo' => 'cliente',
                    'tercero_id' => $factura->cliente_id,
                    'debito' => 0,
                    'credito' => $monto,
                    'monto_origen' => $monto,
                    'monto_funcional' => $monto,
                    'glosa' => 'Disminución CxC factura #'.$factura->id,
                ],
            ]
        );

        $nuevoSaldo = round($saldo - $monto, 2);
        $diasMora = $cxc->fecha_vencimiento ? max(0, now()->diffInDays($cxc->fecha_vencimiento, false) * -1) : 0;
        $estado = $nuevoSaldo <= 0 ? 'pagada' : ($diasMora > 0 ? 'vencida' : 'al_dia');
        $cxc->update([
            'saldo_actual' => $nuevoSaldo,
            'estado_cobro' => $estado,
            'dias_mora' => $diasMora,
        ]);

        if (Schema::hasColumn('facturacion', 'contabilidad_pendiente') && $nuevoSaldo <= 0.00001) {
            Facturacion::query()->whereKey($factura->id)->update(['contabilidad_pendiente' => false]);
        }

        return redirect()->route('contabilidad.cobros.index')->with('success', 'Cobro registrado y contabilizado correctamente.');
    }
}
