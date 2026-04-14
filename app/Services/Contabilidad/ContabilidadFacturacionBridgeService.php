<?php

namespace App\Services\Contabilidad;

use App\Models\ContaAsiento;
use App\Models\ContaCxc;
use App\Models\Facturacion;
use Carbon\Carbon;
use RuntimeException;

class ContabilidadFacturacionBridgeService
{
    public function __construct(private readonly ContabilidadAsientoService $asientoService) {}

    public function registrarFactura(Facturacion $factura): ?ContaAsiento
    {
        $existing = ContaAsiento::query()
            ->where('referencia_tipo', 'factura')
            ->where('referencia_id', $factura->id)
            ->first();
        if ($existing) {
            return $existing;
        }

        $cuentaCxc = $this->asientoService->buscarCuentaPorSubtipo('cxc');
        $cuentaIngreso = $this->asientoService->buscarCuentaPorSubtipo('servicios');
        if (! $cuentaCxc || ! $cuentaIngreso) {
            throw new RuntimeException('Faltan cuentas puente (cxc / ingresos por servicios) para contabilizar factura.');
        }

        $monto = (float) $factura->monto_total;
        if ($monto <= 0) {
            return null;
        }

        $asiento = $this->asientoService->crearAsiento(
            [
                'fecha' => Carbon::parse($factura->fecha_factura),
                'referencia_tipo' => 'factura',
                'referencia_id' => $factura->id,
                'descripcion' => 'Registro de factura #'.$factura->id,
                'moneda' => $factura->moneda ?? 'USD',
                'tasa_cambio' => $factura->tasa_cambio,
            ],
            [
                [
                    'cuenta_id' => $cuentaCxc->id,
                    'tercero_id' => $factura->cliente_id,
                    'tercero_tipo' => 'cliente',
                    'debito' => $monto,
                    'credito' => 0,
                    'monto_origen' => $monto,
                    'monto_funcional' => (float) ($factura->monto_local ?? $monto),
                    'glosa' => 'CxC factura #'.$factura->id,
                ],
                [
                    'cuenta_id' => $cuentaIngreso->id,
                    'tercero_id' => $factura->cliente_id,
                    'tercero_tipo' => 'cliente',
                    'debito' => 0,
                    'credito' => $monto,
                    'monto_origen' => $monto,
                    'monto_funcional' => (float) ($factura->monto_local ?? $monto),
                    'glosa' => 'Ingreso factura #'.$factura->id,
                ],
            ]
        );

        ContaCxc::updateOrCreate(
            ['factura_id' => $factura->id],
            [
                'cliente_id' => $factura->cliente_id,
                'fecha_emision' => Carbon::parse($factura->fecha_factura)->toDateString(),
                'fecha_vencimiento' => Carbon::parse($factura->fecha_factura)->addDays(30)->toDateString(),
                'dias_credito' => 30,
                'monto_original' => $monto,
                'saldo_actual' => $monto,
                'estado_cobro' => 'al_dia',
                'dias_mora' => 0,
            ]
        );

        return $asiento;
    }
}
