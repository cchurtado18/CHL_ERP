<?php

namespace App\Http\Controllers;

use App\Models\ContaCobro;
use App\Models\ContaCuenta;
use App\Models\ContaCxc;
use App\Models\Facturacion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ContabilidadCxcController extends Controller
{
    public function index(Request $request)
    {
        $this->syncFacturasToCxc();

        $q = ContaCxc::query()->with(['factura.cliente', 'factura.encomienda.remitente', 'factura.encomienda.destinatario']);
        if ($request->filled('estado')) {
            $q->where('estado_cobro', $request->input('estado'));
        }
        if ($request->filled('cliente')) {
            $needle = '%'.addcslashes(trim((string) $request->input('cliente')), '%_\\').'%';
            $q->where(function ($sub) use ($needle) {
                $sub->whereHas('factura.cliente', fn ($c) => $c->where('nombre_completo', 'like', $needle))
                    ->orWhereHas('factura.encomienda.remitente', fn ($r) => $r->where('nombre_completo', 'like', $needle))
                    ->orWhereHas('factura.encomienda.destinatario', fn ($d) => $d->where('nombre_completo', 'like', $needle));
            });
        }
        $items = $q->orderByDesc('fecha_emision')->orderByDesc('factura_id')->paginate(20)->appends($request->query());

        return view('contabilidad.cxc.index', compact('items'));
    }

    private function syncFacturasToCxc(): void
    {
        if (! Schema::hasTable('facturacion') || ! Schema::hasTable('conta_cxc')) {
            return;
        }

        $hasPagosTable = Schema::hasTable('pagos');
        $hasContaCobrosTable = Schema::hasTable('conta_cobros');
        $hasContaCuentasTable = Schema::hasTable('conta_cuentas');
        $cuentaBanco = null;
        if ($hasContaCuentasTable) {
            $cuentaBanco = ContaCuenta::query()->where('subtipo', 'banco')->where('activa', true)->first()
                ?? ContaCuenta::query()->where('subtipo', 'caja')->where('activa', true)->first();
        }

        $query = Facturacion::query()
            ->where('monto_total', '>', 0)
            ->whereNotExists(function ($q) {
                $q->selectRaw(1)
                    ->from('conta_cxc')
                    ->whereColumn('conta_cxc.factura_id', 'facturacion.id');
            });

        if ($hasPagosTable) {
            $query->with('pagos');
        }

        $query->chunkById(200, function ($facturas) use ($hasPagosTable, $hasContaCobrosTable, $cuentaBanco) {
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

                    // Si existen pagos históricos, también los reflejamos como conta_cobros
                    // para que el detalle CxC tenga lista de cobros aplicada.
                    if ($hasPagosTable && $hasContaCobrosTable && $cuentaBanco) {
                        foreach ($factura->pagos as $pago) {
                            $montoPago = (float) $pago->monto_pagado;

                            $referenciaPago = $pago->referencia ?? null;
                            $referenciaPago = is_string($referenciaPago) ? trim($referenciaPago) : $referenciaPago;
                            if ($referenciaPago === '') {
                                $referenciaPago = null;
                            }

                            $existeCobro = ContaCobro::query()
                                ->where('factura_id', $factura->id)
                                ->whereDate('fecha_pago', $pago->fecha_pago)
                                ->where('monto', $montoPago)
                                ->where('metodo', $pago->metodo_pago)
                                ->when($referenciaPago === null, fn ($q) => $q->whereNull('referencia'))
                                ->when($referenciaPago !== null, fn ($q) => $q->where('referencia', $referenciaPago))
                                ->exists();

                            if ($existeCobro) {
                                continue;
                            }

                            ContaCobro::create([
                                'factura_id' => $factura->id,
                                'fecha_pago' => Carbon::parse($pago->fecha_pago)->toDateString(),
                                'monto' => $montoPago,
                                'moneda' => $factura->moneda ?? 'USD',
                                'tasa_cambio' => $factura->tasa_cambio,
                                'metodo' => (string) $pago->metodo_pago,
                                'cuenta_banco_caja_id' => (int) $cuentaBanco->id,
                                'referencia' => $referenciaPago,
                                'comision' => null,
                                'created_by' => null,
                            ]);
                        }
                    }
                }
            });
    }

    public function show(int $facturaId)
    {
        $this->syncFacturasToCxc();

        $cxc = ContaCxc::query()
            ->with(['factura.cliente', 'factura.encomienda.remitente'])
            ->where('factura_id', $facturaId)
            ->firstOrFail();

        $cobros = ContaCobro::query()
            ->with(['cuentaBancoCaja', 'creador'])
            ->where('factura_id', $facturaId)
            ->orderByDesc('fecha_pago')
            ->orderByDesc('id')
            ->get();

        $original = (float) $cxc->monto_original;
        $cobrado = (float) $cobros->sum('monto');
        $faltante = (float) $cxc->saldo_actual;

        return view('contabilidad.cxc.show', compact('cxc', 'cobros', 'original', 'cobrado', 'faltante'));
    }
}
