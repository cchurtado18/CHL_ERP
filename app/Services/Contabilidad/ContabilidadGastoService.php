<?php

namespace App\Services\Contabilidad;

use App\Models\ContaGasto;
use App\Models\ContaGastoCategoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ContabilidadGastoService
{
    public function __construct(private readonly ContabilidadAsientoService $asientoService) {}

    /**
     * Registra un gasto y genera el asiento contable automáticamente:
     *   Débito  → cuenta contable de la categoría (gasto)
     *   Crédito → cuenta de caja/banco (cuenta_pago_id)
     *
     * @param  array<string,mixed>  $data
     */
    public function registrarGasto(array $data): ContaGasto
    {
        $categoria = ContaGastoCategoria::query()
            ->with('cuentaContable')
            ->findOrFail($data['categoria_id']);

        if (! $categoria->cuenta_contable_id) {
            throw new RuntimeException('La categoría "'.$categoria->nombre.'" no tiene una cuenta contable asociada. Asignala en Cuentas o desde la pantalla de categorías.');
        }

        return DB::transaction(function () use ($data, $categoria) {
            $gasto = ContaGasto::create([
                'fecha' => $data['fecha'],
                'categoria_id' => $categoria->id,
                'descripcion' => $data['descripcion'],
                'monto' => (float) $data['monto'],
                'moneda' => $data['moneda'] ?? 'USD',
                'tasa_cambio' => $data['tasa_cambio'] ?? null,
                'cuenta_pago_id' => (int) $data['cuenta_pago_id'],
                'referencia' => $data['referencia'] ?? null,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            $asiento = $this->asientoService->crearAsiento(
                [
                    'fecha' => $gasto->fecha->toDateString(),
                    'descripcion' => 'Gasto: '.$categoria->nombre.' — '.$gasto->descripcion,
                    'moneda' => $gasto->moneda,
                    'tasa_cambio' => $gasto->tasa_cambio,
                    'referencia_tipo' => 'gasto',
                    'referencia_id' => $gasto->id,
                ],
                [
                    [
                        'cuenta_id' => $categoria->cuenta_contable_id,
                        'debito' => (float) $gasto->monto,
                        'credito' => 0,
                        'glosa' => $categoria->nombre.' — '.$gasto->descripcion,
                    ],
                    [
                        'cuenta_id' => (int) $gasto->cuenta_pago_id,
                        'debito' => 0,
                        'credito' => (float) $gasto->monto,
                        'glosa' => 'Pago de gasto: '.$categoria->nombre,
                    ],
                ]
            );

            $gasto->update(['asiento_id' => $asiento->id]);

            return $gasto->fresh(['categoria', 'cuentaPago', 'asiento']);
        });
    }

    /**
     * Anula un gasto: lo elimina (soft delete) y crea un asiento de reversión.
     */
    public function anularGasto(ContaGasto $gasto, ?string $motivo = null): void
    {
        DB::transaction(function () use ($gasto, $motivo) {
            $gasto->load('categoria', 'cuentaPago');

            if ($gasto->asiento_id && $gasto->categoria?->cuenta_contable_id) {
                $this->asientoService->crearAsiento(
                    [
                        'fecha' => now()->toDateString(),
                        'descripcion' => 'Reversión gasto #'.$gasto->id.($motivo ? ' — '.$motivo : ''),
                        'moneda' => $gasto->moneda,
                        'tasa_cambio' => $gasto->tasa_cambio,
                        'referencia_tipo' => 'gasto_reversion',
                        'referencia_id' => $gasto->id,
                    ],
                    [
                        [
                            'cuenta_id' => (int) $gasto->cuenta_pago_id,
                            'debito' => (float) $gasto->monto,
                            'credito' => 0,
                            'glosa' => 'Reversión: '.$gasto->descripcion,
                        ],
                        [
                            'cuenta_id' => (int) $gasto->categoria->cuenta_contable_id,
                            'debito' => 0,
                            'credito' => (float) $gasto->monto,
                            'glosa' => 'Reversión: '.$gasto->categoria->nombre,
                        ],
                    ]
                );
            }

            $gasto->update(['updated_by' => Auth::id()]);
            $gasto->delete();
        });
    }
}
