<?php

namespace App\Services\Contabilidad;

use App\Models\ContaVoucher;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContabilidadVoucherService
{
    /**
     * @param  array<string,mixed>  $data
     */
    public function emitir(array $data): ContaVoucher
    {
        return DB::transaction(function () use ($data) {
            $fecha = Carbon::parse($data['fecha'] ?? now());

            return ContaVoucher::create([
                'folio' => $this->nextFolio($fecha),
                'tipo' => $data['tipo'],
                'fecha' => $fecha->toDateString(),
                'monto' => round((float) $data['monto'], 2),
                'moneda' => $data['moneda'] ?? 'USD',
                'tercero_tipo' => $data['tercero_tipo'] ?? null,
                'tercero_id' => $data['tercero_id'] ?? null,
                'tercero_nombre' => $data['tercero_nombre'],
                'concepto' => $data['concepto'],
                'metodo' => $data['metodo'] ?? null,
                'cuenta_banco_caja_id' => $data['cuenta_banco_caja_id'] ?? null,
                'referencia_tipo' => $data['referencia_tipo'] ?? null,
                'referencia_id' => $data['referencia_id'] ?? null,
                'referencia' => $data['referencia'] ?? null,
                'created_by' => Auth::id(),
            ]);
        });
    }

    private function nextFolio(Carbon $fecha): string
    {
        $prefix = 'VCH-'.$fecha->format('Ym').'-';
        $ultimo = ContaVoucher::query()
            ->where('folio', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('folio');

        $next = 1;
        if ($ultimo) {
            $parts = explode('-', $ultimo);
            $next = ((int) end($parts)) + 1;
        }

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
