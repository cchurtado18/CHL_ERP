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
use Illuminate\Support\Facades\DB;
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
        $this->syncFacturasToCxc();

        $facturas = Facturacion::query()
            ->with(['cliente', 'encomienda.remitente', 'contaCxc'])
            ->when(Schema::hasColumn('facturacion', 'anulada'), fn ($q) => $q->noAnulada())
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
        if (Schema::hasColumn('facturacion', 'anulada') && $factura->anulada) {
            return back()->withErrors(['factura_id' => 'La factura seleccionada está anulada.'])->withInput();
        }
        $fechaPago = Carbon::parse($data['fecha_pago'])->toDateString();
        $monto = (float) $data['monto'];
        $referencia = isset($data['referencia']) ? trim((string) $data['referencia']) : null;
        if ($referencia === '') {
            $referencia = null;
        }
        $comision = isset($data['comision']) ? (float) $data['comision'] : null;

        try {
            DB::transaction(function () use ($factura, $data, $fechaPago, $monto, $referencia, $comision) {
                $cxc = ContaCxc::query()->where('factura_id', $factura->id)->lockForUpdate()->first();
                if (! $cxc) {
                    throw new RuntimeException('La factura no tiene registro en CxC.');
                }

                $saldo = (float) $cxc->saldo_actual;
                if ($monto - $saldo > 0.009) {
                    throw new RuntimeException('El cobro no puede superar el saldo pendiente en CxC.');
                }

                $existeDuplicado = ContaCobro::query()
                    ->where('factura_id', $factura->id)
                    ->whereDate('fecha_pago', $fechaPago)
                    ->where('monto', $monto)
                    ->where('moneda', $data['moneda'])
                    ->where('metodo', $data['metodo'])
                    ->where('cuenta_banco_caja_id', (int) $data['cuenta_banco_caja_id'])
                    ->when($referencia === null, fn ($q) => $q->whereNull('referencia'))
                    ->when($referencia !== null, fn ($q) => $q->where('referencia', $referencia))
                    ->whereBetween('created_at', [now()->subMinutes(2), now()->addSeconds(5)])
                    ->exists();

                if ($existeDuplicado) {
                    throw new RuntimeException('Se detectó un intento duplicado del mismo cobro. Verifica el listado antes de reenviar.');
                }

                $cobro = ContaCobro::create([
                    'factura_id' => $factura->id,
                    'fecha_pago' => $fechaPago,
                    'monto' => $monto,
                    'moneda' => $data['moneda'],
                    'tasa_cambio' => $data['tasa_cambio'] ?? null,
                    'metodo' => $data['metodo'],
                    'cuenta_banco_caja_id' => (int) $data['cuenta_banco_caja_id'],
                    'referencia' => $referencia,
                    'comision' => $comision,
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
            });
        } catch (RuntimeException $e) {
            return back()->withErrors(['monto' => $e->getMessage()])->withInput();
        }

        return redirect()->route('contabilidad.cobros.index')->with('success', 'Cobro registrado y contabilizado correctamente.');
    }

    private function syncFacturasToCxc(): void
    {
        if (! Schema::hasTable('facturacion') || ! Schema::hasTable('conta_cxc')) {
            return;
        }

        $hasPagosTable = Schema::hasTable('pagos');
        $query = Facturacion::query()
            ->when(Schema::hasColumn('facturacion', 'anulada'), fn ($q) => $q->noAnulada())
            ->where('monto_total', '>', 0)
            ->whereNotExists(function ($q) {
                $q->selectRaw(1)
                    ->from('conta_cxc')
                    ->whereColumn('conta_cxc.factura_id', 'facturacion.id');
            });

        if ($hasPagosTable) {
            $query->with('pagos');
        }

        $query->chunkById(200, function ($facturas) use ($hasPagosTable) {
                foreach ($facturas as $factura) {
                    $montoOriginal = (float) $factura->monto_total;
                    $pagadoHistorico = $hasPagosTable ? (float) $factura->pagos->sum('monto_pagado') : 0.0;
                    $saldo = max(0, round($montoOriginal - $pagadoHistorico, 2));
                    $fechaBase = $factura->fecha_factura ?: $factura->created_at ?: now();
                    try {
                        $fechaEmision = Carbon::parse($fechaBase);
                    } catch (\Throwable) {
                        $fechaEmision = now();
                    }
                    $fechaVencimiento = $fechaEmision->copy()->addDays(30);
                    $diasMora = now()->greaterThan($fechaVencimiento) ? $fechaVencimiento->diffInDays(now()) : 0;
                    $estado = $saldo <= 0 ? 'pagada' : ($diasMora > 0 ? 'vencida' : 'al_dia');

                    ContaCxc::updateOrCreate(
                        ['factura_id' => $factura->id],
                        [
                            'cliente_id' => $factura->cliente_id,
                            'fecha_emision' => $fechaEmision->toDateString(),
                            'fecha_vencimiento' => $fechaVencimiento->toDateString(),
                            'dias_credito' => 30,
                            'monto_original' => $montoOriginal,
                            'saldo_actual' => $saldo,
                            'estado_cobro' => $estado,
                            'dias_mora' => $diasMora,
                        ]
                    );
                }
            });
    }
}
