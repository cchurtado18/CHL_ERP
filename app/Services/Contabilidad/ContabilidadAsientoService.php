<?php

namespace App\Services\Contabilidad;

use App\Models\ContaAsiento;
use App\Models\ContaCuenta;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ContabilidadAsientoService
{
    public function __construct(private readonly ContabilidadPeriodoService $periodoService) {}

    /**
     * @param  array<int,array<string,mixed>>  $detalles
     */
    public function crearAsiento(array $payload, array $detalles): ContaAsiento
    {
        if (empty($detalles)) {
            throw new RuntimeException('No se puede crear un asiento sin detalles.');
        }

        $fecha = Carbon::parse($payload['fecha'] ?? now());
        $this->periodoService->assertPeriodoAbierto($fecha);

        $totalDebito = 0.0;
        $totalCredito = 0.0;
        foreach ($detalles as $d) {
            $deb = (float) ($d['debito'] ?? 0);
            $cre = (float) ($d['credito'] ?? 0);
            if ($deb < 0 || $cre < 0) {
                throw new RuntimeException('Débito o crédito inválido.');
            }
            if ($deb > 0 && $cre > 0) {
                throw new RuntimeException('Una línea no puede tener débito y crédito simultáneamente.');
            }
            $totalDebito += $deb;
            $totalCredito += $cre;
        }

        if (round($totalDebito, 2) !== round($totalCredito, 2)) {
            throw new RuntimeException('Asiento descuadrado: débitos no igualan créditos.');
        }

        return DB::transaction(function () use ($payload, $detalles, $fecha, $totalDebito, $totalCredito) {
            $asiento = ContaAsiento::create([
                'numero' => $this->nextNumero($fecha),
                'fecha' => $fecha->toDateString(),
                'periodo_anio' => (int) $fecha->format('Y'),
                'periodo_mes' => (int) $fecha->format('m'),
                'referencia_tipo' => $payload['referencia_tipo'] ?? null,
                'referencia_id' => $payload['referencia_id'] ?? null,
                'descripcion' => $payload['descripcion'] ?? null,
                'moneda' => $payload['moneda'] ?? 'USD',
                'tasa_cambio' => $payload['tasa_cambio'] ?? null,
                'total_debito' => $totalDebito,
                'total_credito' => $totalCredito,
                'estado' => $payload['estado'] ?? 'contabilizado',
                'created_by' => Auth::id(),
                'approved_by' => Auth::id(),
            ]);

            foreach ($detalles as $d) {
                $asiento->detalles()->create([
                    'cuenta_id' => $d['cuenta_id'],
                    'tercero_id' => $d['tercero_id'] ?? null,
                    'tercero_tipo' => $d['tercero_tipo'] ?? null,
                    'debito' => (float) ($d['debito'] ?? 0),
                    'credito' => (float) ($d['credito'] ?? 0),
                    'monto_origen' => $d['monto_origen'] ?? null,
                    'monto_funcional' => $d['monto_funcional'] ?? null,
                    'glosa' => $d['glosa'] ?? null,
                ]);
            }

            return $asiento;
        });
    }

    public function buscarCuentaPorSubtipo(string $subtipo): ?ContaCuenta
    {
        return ContaCuenta::query()
            ->where('subtipo', $subtipo)
            ->where('activa', true)
            ->first();
    }

    private function nextNumero(Carbon $fecha): string
    {
        $prefix = 'AST-'.$fecha->format('Ym').'-';
        $ultimo = ContaAsiento::query()
            ->where('numero', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('numero');

        $next = 1;
        if ($ultimo) {
            $parts = explode('-', $ultimo);
            $lastPart = end($parts);
            $next = ((int) $lastPart) + 1;
        }

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
