<?php

namespace App\Services\Contabilidad;

use App\Models\ContaCuenta;
use App\Models\ContaCxp;
use App\Models\ContaPagoProveedor;
use App\Models\Inventario;
use App\Models\Proveedor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class ContabilidadCxpService
{
    public function __construct(
        private readonly ContabilidadAsientoService $asientoService,
        private readonly ContabilidadVoucherService $voucherService,
    ) {}

    /**
     * @param  array<string,mixed>  $data
     */
    public function registrarFactura(array $data): ContaCxp
    {
        $cuentaCxp = $this->asientoService->buscarCuentaPorSubtipo('cxp');
        if (! $cuentaCxp) {
            throw new RuntimeException('No existe la cuenta contable de Cuentas por Pagar (subtipo cxp). Ejecuta las migraciones o créala en el catálogo.');
        }

        $monto = round((float) $data['monto_original'], 2);
        if ($monto <= 0) {
            throw new RuntimeException('El monto de la factura del proveedor debe ser mayor a cero.');
        }

        $fechaEmision = Carbon::parse($data['fecha_emision']);
        $diasCredito = (int) ($data['dias_credito'] ?? 0);
        $fechaVencimiento = ! empty($data['fecha_vencimiento'])
            ? Carbon::parse($data['fecha_vencimiento'])
            : $fechaEmision->copy()->addDays($diasCredito);

        return DB::transaction(function () use ($data, $cuentaCxp, $monto, $fechaEmision, $fechaVencimiento, $diasCredito) {
            $cxp = ContaCxp::create([
                'proveedor_id' => (int) $data['proveedor_id'],
                'numero_documento' => $data['numero_documento'] ?? null,
                'descripcion' => $data['descripcion'],
                'fecha_emision' => $fechaEmision->toDateString(),
                'fecha_vencimiento' => $fechaVencimiento->toDateString(),
                'dias_credito' => $diasCredito,
                'monto_original' => $monto,
                'saldo_actual' => $monto,
                'moneda' => $data['moneda'] ?? 'USD',
                'tasa_cambio' => $data['tasa_cambio'] ?? null,
                'cuenta_gasto_id' => (int) $data['cuenta_gasto_id'],
                'estado_pago' => $this->estadoDesdeSaldo($monto, $fechaVencimiento),
                'origen' => $data['origen'] ?? 'manual',
                'created_by' => Auth::id(),
            ]);

            $proveedor = Proveedor::query()->find($cxp->proveedor_id);
            $doc = $cxp->etiquetaDocumento();

            $asiento = $this->asientoService->crearAsiento(
                [
                    'fecha' => $cxp->fecha_emision,
                    'referencia_tipo' => 'cxp',
                    'referencia_id' => $cxp->id,
                    'descripcion' => 'Factura proveedor '.$doc.' — '.($proveedor->nombre ?? 'Proveedor'),
                    'moneda' => $cxp->moneda,
                    'tasa_cambio' => $cxp->tasa_cambio,
                ],
                [
                    [
                        'cuenta_id' => $cxp->cuenta_gasto_id,
                        'tercero_tipo' => 'proveedor',
                        'tercero_id' => $cxp->proveedor_id,
                        'debito' => $monto,
                        'credito' => 0,
                        'monto_origen' => $monto,
                        'monto_funcional' => $monto,
                        'glosa' => 'Gasto / costo factura '.$doc,
                    ],
                    [
                        'cuenta_id' => $cuentaCxp->id,
                        'tercero_tipo' => 'proveedor',
                        'tercero_id' => $cxp->proveedor_id,
                        'debito' => 0,
                        'credito' => $monto,
                        'monto_origen' => $monto,
                        'monto_funcional' => $monto,
                        'glosa' => 'CxP factura '.$doc,
                    ],
                ]
            );

            $cxp->update(['asiento_id' => $asiento->id]);

            return $cxp->fresh(['proveedor', 'cuentaGasto']);
        });
    }

    /**
     * @param  array<string,mixed>  $data
     */
    public function registrarPago(ContaCxp $cxp, array $data): ContaPagoProveedor
    {
        $cuentaCxp = $this->asientoService->buscarCuentaPorSubtipo('cxp');
        if (! $cuentaCxp) {
            throw new RuntimeException('No existe la cuenta contable de Cuentas por Pagar (subtipo cxp).');
        }

        $monto = round((float) $data['monto'], 2);
        if ($monto <= 0) {
            throw new RuntimeException('El pago debe ser mayor a cero.');
        }

        return DB::transaction(function () use ($cxp, $data, $cuentaCxp, $monto) {
            $cxp = ContaCxp::query()->lockForUpdate()->with('proveedor')->findOrFail($cxp->id);
            $saldo = (float) $cxp->saldo_actual;
            if ($monto - $saldo > 0.009) {
                throw new RuntimeException('El pago no puede superar el saldo pendiente ($'.number_format($saldo, 2).').');
            }
            $proveedor = $cxp->proveedor;
            $doc = $cxp->etiquetaDocumento();
            $fechaPago = Carbon::parse($data['fecha_pago'])->toDateString();

            $pago = ContaPagoProveedor::create([
                'cxp_id' => $cxp->id,
                'fecha_pago' => $fechaPago,
                'monto' => $monto,
                'moneda' => $data['moneda'] ?? $cxp->moneda,
                'tasa_cambio' => $data['tasa_cambio'] ?? null,
                'metodo' => $data['metodo'],
                'cuenta_banco_caja_id' => (int) $data['cuenta_banco_caja_id'],
                'referencia' => $data['referencia'] ?? null,
                'comprobante_path' => $data['comprobante_path'] ?? null,
                'created_by' => Auth::id(),
            ]);

            if (! $cxp->asiento_id) {
                $this->postearAsientoAccrual($cxp);
                $cxp->refresh();
            }

            $asiento = $this->asientoService->crearAsiento(
                [
                    'fecha' => $fechaPago,
                    'referencia_tipo' => 'pago_proveedor',
                    'referencia_id' => $pago->id,
                    'descripcion' => 'Pago proveedor '.$doc.' — '.($proveedor->nombre ?? 'Proveedor'),
                    'moneda' => $pago->moneda,
                    'tasa_cambio' => $pago->tasa_cambio,
                ],
                [
                    [
                        'cuenta_id' => $cuentaCxp->id,
                        'tercero_tipo' => 'proveedor',
                        'tercero_id' => $cxp->proveedor_id,
                        'debito' => $monto,
                        'credito' => 0,
                        'monto_origen' => $monto,
                        'monto_funcional' => $monto,
                        'glosa' => 'Cancelación CxP '.$doc,
                    ],
                    [
                        'cuenta_id' => $pago->cuenta_banco_caja_id,
                        'tercero_tipo' => 'proveedor',
                        'tercero_id' => $cxp->proveedor_id,
                        'debito' => 0,
                        'credito' => $monto,
                        'monto_origen' => $monto,
                        'monto_funcional' => $monto,
                        'glosa' => 'Egreso banco/caja pago '.$doc,
                    ],
                ]
            );

            $voucher = $this->voucherService->emitir([
                'tipo' => 'egreso',
                'fecha' => $fechaPago,
                'monto' => $monto,
                'moneda' => $pago->moneda,
                'tercero_tipo' => 'proveedor',
                'tercero_id' => $cxp->proveedor_id,
                'tercero_nombre' => $proveedor->nombre ?? 'Proveedor',
                'concepto' => 'Pago de CxP '.$doc.($cxp->descripcion ? ' — '.$cxp->descripcion : ''),
                'metodo' => $pago->metodo,
                'cuenta_banco_caja_id' => $pago->cuenta_banco_caja_id,
                'referencia_tipo' => 'pago_proveedor',
                'referencia_id' => $pago->id,
                'referencia' => $pago->referencia,
            ]);

            $nuevoSaldo = round($saldo - $monto, 2);
            $cxp->update([
                'saldo_actual' => $nuevoSaldo,
                'estado_pago' => $this->estadoDesdeSaldo($nuevoSaldo, $cxp->fecha_vencimiento),
            ]);

            $pago->update([
                'asiento_id' => $asiento->id,
                'voucher_id' => $voucher->id,
            ]);

            if ($nuevoSaldo <= 0.00001 && Schema::hasColumn('inventario', 'proveedor_pagado_at')) {
                Inventario::query()
                    ->where('cxp_id', $cxp->id)
                    ->whereNull('proveedor_pagado_at')
                    ->update(['proveedor_pagado_at' => now()]);
            }

            return $pago->fresh(['voucher', 'cuentaBancoCaja']);
        });
    }

    /**
     * Asigna el paquete a una factura CxP ya registrada. No cambia el monto de la factura.
     */
    public function sincronizarPaquete(Inventario $paquete): void
    {
        if (! Schema::hasTable('conta_cxp') || ! Schema::hasColumn('inventario', 'cxp_id')) {
            return;
        }

        $cxpId = $paquete->cxp_id ? (int) $paquete->cxp_id : null;
        if (! $cxpId) {
            $paquete->forceFill([
                'proveedor_id' => $paquete->proveedor_id,
                'factura_proveedor_codigo' => null,
            ])->saveQuietly();

            return;
        }

        $cxp = ContaCxp::query()->find($cxpId);
        if (! $cxp) {
            $paquete->forceFill(['cxp_id' => null, 'factura_proveedor_codigo' => null])->saveQuietly();

            return;
        }

        $paquete->forceFill([
            'cxp_id' => $cxp->id,
            'proveedor_id' => $cxp->proveedor_id,
            'factura_proveedor_codigo' => $cxp->numero_documento,
        ])->saveQuietly();
    }

    private function postearAsientoAccrual(ContaCxp $cxp): void
    {
        if ($cxp->asiento_id) {
            return;
        }

        $cuentaCxp = $this->asientoService->buscarCuentaPorSubtipo('cxp');
        if (! $cuentaCxp) {
            throw new RuntimeException('No existe la cuenta contable de Cuentas por Pagar (subtipo cxp).');
        }

        $monto = round((float) $cxp->monto_original, 2);
        if ($monto <= 0) {
            throw new RuntimeException('La factura del proveedor no tiene monto. Ingresa los paquetes o el monto antes de pagar.');
        }

        $proveedor = $cxp->proveedor;
        $doc = $cxp->etiquetaDocumento();
        $asiento = $this->asientoService->crearAsiento(
            [
                'fecha' => $cxp->fecha_emision,
                'referencia_tipo' => 'cxp',
                'referencia_id' => $cxp->id,
                'descripcion' => 'Factura proveedor '.$doc.' — '.($proveedor->nombre ?? 'Proveedor'),
                'moneda' => $cxp->moneda,
                'tasa_cambio' => $cxp->tasa_cambio,
            ],
            [
                [
                    'cuenta_id' => $cxp->cuenta_gasto_id,
                    'tercero_tipo' => 'proveedor',
                    'tercero_id' => $cxp->proveedor_id,
                    'debito' => $monto,
                    'credito' => 0,
                    'monto_origen' => $monto,
                    'monto_funcional' => $monto,
                    'glosa' => 'Gasto / costo factura '.$doc,
                ],
                [
                    'cuenta_id' => $cuentaCxp->id,
                    'tercero_tipo' => 'proveedor',
                    'tercero_id' => $cxp->proveedor_id,
                    'debito' => 0,
                    'credito' => $monto,
                    'monto_origen' => $monto,
                    'monto_funcional' => $monto,
                    'glosa' => 'CxP factura '.$doc,
                ],
            ]
        );

        $cxp->update(['asiento_id' => $asiento->id]);
    }

    public function cuentaCostoFlete(): ?ContaCuenta
    {
        $porSubtipo = ContaCuenta::query()
            ->where('subtipo', 'flete')
            ->where('activa', true)
            ->where('acepta_movimiento', true)
            ->orderBy('codigo')
            ->first();
        if ($porSubtipo) {
            return $porSubtipo;
        }

        $porCodigo = ContaCuenta::query()->where('codigo', '5.2.01')->where('activa', true)->first();
        if ($porCodigo) {
            return $porCodigo;
        }

        return ContaCuenta::query()
            ->whereIn('tipo', ['costo', 'gasto'])
            ->where('activa', true)
            ->where('acepta_movimiento', true)
            ->orderByRaw("CASE WHEN tipo = 'costo' THEN 0 ELSE 1 END")
            ->orderBy('codigo')
            ->first();
    }

    private function estadoDesdeSaldo(float $saldo, $fechaVencimiento): string
    {
        if ($saldo <= 0.00001) {
            return 'pagada';
        }
        if ($fechaVencimiento && Carbon::parse($fechaVencimiento)->lt(now()->startOfDay())) {
            return 'vencida';
        }

        return 'al_dia';
    }
}
