<?php

namespace App\Console\Commands;

use App\Models\Facturacion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncNotasCobroFacturacion extends Command
{
    protected $signature = 'facturacion:sync-notas-cobro {--dry-run : Solo mostrar diferencias sin guardar}';

    protected $description = 'Alinea numero_acta con el folio (Nota de cobro) en todas las facturas';

    public function handle(): int
    {
        if (! Schema::hasTable('facturacion')) {
            $this->error('Tabla facturacion no existe.');

            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry-run');
        $rows = Facturacion::query()->orderBy('id')->get(['id', 'folio', 'numero_acta']);
        $pending = [];

        foreach ($rows as $row) {
            $desired = $row->numeroNotaCobro();
            if ((string) $row->numero_acta !== $desired) {
                $pending[] = [$row->id, $row->folio, $row->numero_acta, $desired];
            }
        }

        if ($pending === []) {
            $this->info('Todas las notas de cobro ya coinciden con el folio.');

            return self::SUCCESS;
        }

        $this->table(['id', 'folio', 'acta actual', 'nota correcta'], $pending);
        $this->warn(count($pending).' factura(s) desfasadas.');

        if ($dry) {
            $this->comment('Dry-run: no se guardó nada.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($rows) {
            // Paso 1: valores temporales únicos para no chocar con el índice unique.
            foreach ($rows as $row) {
                $desired = $row->numeroNotaCobro();
                if ((string) $row->numero_acta === $desired) {
                    continue;
                }
                Facturacion::query()->whereKey($row->id)->update([
                    'numero_acta' => 'SYNC-TMP-'.$row->id,
                ]);
            }
            // Paso 2: valor final = Nota de cobro del folio.
            foreach ($rows as $row) {
                $desired = $row->numeroNotaCobro();
                Facturacion::query()->whereKey($row->id)->update([
                    'numero_acta' => $desired,
                ]);
            }
        });

        $this->info('Notas de cobro sincronizadas con el folio.');

        return self::SUCCESS;
    }
}
