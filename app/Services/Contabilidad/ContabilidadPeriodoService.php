<?php

namespace App\Services\Contabilidad;

use App\Models\ContaPeriodo;
use Carbon\Carbon;
use RuntimeException;

class ContabilidadPeriodoService
{
    public function assertPeriodoAbierto(Carbon $fecha): void
    {
        $periodo = ContaPeriodo::query()->firstOrCreate(
            ['anio' => (int) $fecha->format('Y'), 'mes' => (int) $fecha->format('m')],
            ['estado' => 'abierto']
        );

        if ($periodo->estado === 'cerrado') {
            throw new RuntimeException("El periodo {$periodo->anio}-{$periodo->mes} está cerrado.");
        }
    }
}
